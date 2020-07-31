<?php

namespace Raketman\DatabasePartitionProcessor\Command;


use Raketman\DatabasePartitionProcessor\Parser\Annotation;
use Raketman\DatabasePartitionProcessor\Parser\ConsoleOption;

/**
 *
 * create partition by annotation shcema
 *
 * Class CreateCommand
 * @package Raketman\DatabasePartitionProcessor\Command
 */
class CreateCommand
{
    /**
     * @throws \Raketman\DatabasePartitionProcessor\Exception\AnnotationNotFoundException
     * @throws \Raketman\DatabasePartitionProcessor\Exception\SchemaNotFoundException
     * @throws \ReflectionException
     */
    public function run($argv)
    {
        list($entityPath, $databaseUrl, $envDatabaseUrl) = (new ConsoleOption())->getList($argv, [
            'entity_path'       => '--entity-path',
            'databaseUrl'       => '--database-url',
            'envDatabaseUrl'    => '--env-database-url'
        ]);


        /** @var Annotation $parser */
        $parser = new Annotation();

        $object = $parser->parse($entityPath);

        if (is_null($object)) {
            throw new \Raketman\DatabasePartitionProcessor\Exception\AnnotationNotFoundException();
        }

        $databaseUrl = is_null($databaseUrl) ? getenv($envDatabaseUrl) : $databaseUrl;
        $dsnParams = parse_url($databaseUrl);

        $processor = (new \Raketman\DatabasePartitionProcessor\Processor\ProcessorFactory())->create($dsnParams);

        $processor->create($object);

    }
}
?>
