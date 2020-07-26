<?php

require_once __DIR__. '/../../../autoload.php';

list($dirs, $table, $databaseUrl, $envDatabaseUrl) = (new \Raketman\DatabasePartitionProcessor\Parser\ConsoleOption())->getList($argv, [
    'dirs'              => '--locate-dirs',
    'table'             => '--table',
    'databaseUrl'       => '--database-url',
    'envDatabaseUrl'    => '--env-database-url'
]);

$dirs = is_null($dirs) ? [__DIR__. '/../../../../src'] : explode(':', $dirs);

$locator = new Raketman\DatabasePartitionProcessor\Parser\Locator();
/** @var \Raketman\DatabasePartitionProcessor\Parser\Annotation $parser */
$parser = new Raketman\DatabasePartitionProcessor\Parser\Annotation();

$objects = [];

foreach ($dirs as $dir) {
    $files = $locator->locate($dir);

    foreach ($files as $file) {
        $object = $parser->parse($file);

        if (is_null($object)) {
            continue;
        }

        if ($table && $object->table !== $table) {
            continue;
        }

        if ($object->isManual() && $object->table !== $table) {
            continue;
        }

        $objects[] = $object;
    }
}

// Проверим, что
$databaseUrl = is_null($databaseUrl) ? getenv($envDatabaseUrl) : $databaseUrl;
$dsnParams = parse_url($databaseUrl);


/** @var \Raketman\DatabasePartitionProcessor\Processor\DbProcessorInterface  $processor */
switch ($dsnParams['scheme']) {
    case 'pdo_mysql':
    case 'mysql':
        $processor = new \Raketman\DatabasePartitionProcessor\Processor\MysqlDbProcessor($dsnParams);
        break;
    default:
        throw new \Exception("{$dsnParams['scheme']} не поддерживается", 500);
}


foreach ($objects as $object) {
    $processor->prolongate($object);
}




?>
