<?php
/**
 * @RaketmanDatePartition(
 *     table="partition_test_table",
 *     type="month",
 *     id_field="id",
 *     date_field="created",
 *     safe_period="31",
 *     create_period="42",
 *     manual=true
 * )
 *
 */

require_once __DIR__ . '/../../../autoload.php';


// create fixtures
list($databaseUrl, $envDatabaseUrl) = (new \Raketman\DatabasePartitionProcessor\Parser\ConsoleOption())->getList($argv, [
    'databaseUrl'       => '--database-url',
    'envDatabaseUrl'    => '--env-database-url'
]);
$databaseUrlParams = parse_url($databaseUrl);
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;%s',
    $databaseUrlParams['host'],
    $databaseUrlParams['port'],
    substr($databaseUrlParams['path'], 1),
    implode(';', explode('&', $databaseUrlParams['query']))
);

$pdo = new \PDO($dsn, $databaseUrlParams['user'], $databaseUrlParams['pass']);

$pdo->query("DROP TABLE IF EXISTS partition_test_table");

$pdo->query("
create table partition_test_table
(
    id      int auto_increment
        primary key,
    created datetime not null
)
    collate = utf8mb4_unicode_ci;
");

$endDate = (new \DateTimeImmutable());
$startDate = (new \DateTime())->sub(new \DateInterval('P3Y'));
$processDate = clone $startDate;


while($processDate < $endDate) {
    $query = "INSERT INTO partition_test_table (created) VALUES (\"{$processDate->format('Y-m-d H:i:s')}\")";

    $max = rand(10, 20);
    for($i = 0; $i < $max; $i++) { 
        $pdo->query($query);
    }


    $processDate->add(new \DateInterval('P1D'));
}


$partition = (new \Raketman\DatabasePartitionProcessor\Parser\Annotation())->parse(__DIR__ .'/Example.php');


$databaseUrl = is_null($databaseUrl) ? getenv($envDatabaseUrl) : $databaseUrl;
$dsnParams = parse_url($databaseUrl);

$processor = (new \Raketman\DatabasePartitionProcessor\Processor\ProcessorFactory())->create($dsnParams);

$processor->create($partition);

$processor->process($partition);

// check, that we dont have record, that  greater $checkDate
$checkDate = $endDate->sub(new \DateInterval("P{$partition->safe_period}Y"))->format('Y-01-01');

$minDate = $pdo->query("SELECT MIN(created) FROM partition_test_table")->fetch(\PDO::FETCH_NUM);


$pdo->exec("DROP TABLE partition_test_table");

if ($minDate[0] < $checkDate) {
    throw new \Exception("have record, that  greater $checkDate", 500);
}

