<?php

require_once __DIR__. '/../../../autoload.php';

list($entityPath, $databaseUrl, $envDatabaseUrl) = (new \Raketman\DatabasePartitionProcessor\Parser\ConsoleOption())->getList($argv, [
    'entity_path'       => '--entity-path',
    'databaseUrl'       => '--database-url',
    'envDatabaseUrl'    => '--env-database-url'
]);


/** @var \Raketman\DatabasePartitionProcessor\Parser\Annotation $parser */
$parser = new Raketman\DatabasePartitionProcessor\Parser\Annotation();

$object = $parser->parse($entityPath);

if (is_null($object)) {
    throw new \Raketman\DatabasePartitionProcessor\Exception\AnnotationNotFoundException();
}

// Проверим, что
$databaseUrl = is_null($databaseUrl) ? getenv($envDatabaseUrl) : $databaseUrl;
$dsnParams = parse_url($databaseUrl);

$processor = (new \Raketman\DatabasePartitionProcessor\Processor\ProcessorFactory())->create($dsnParams);

$processor->create($object);

$processor->prolongate($object);

?>
