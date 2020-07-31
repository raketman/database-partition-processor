<?php

namespace Raketman\DatabasePartitionProcessor\Command;

use Raketman\DatabasePartitionProcessor\Parser\Annotation;
use Raketman\DatabasePartitionProcessor\Parser\ConsoleOption;
use Raketman\DatabasePartitionProcessor\Parser\Locator;
use Raketman\DatabasePartitionProcessor\Processor\DbProcessorInterface;
use Raketman\DatabasePartitionProcessor\Processor\MysqlDbProcessor;

/**
 * process partition
 * prolongate|delete partition by annotation shcema
 *
 * Class ProcessCommand
 * @package Raketman\DatabasePartitionProcessor\Command
 *
 */
class ProcessCommand
{
    /**
     * @throws \Raketman\DatabasePartitionProcessor\Exception\PartitionNotFoundException
     * @throws \Raketman\DatabasePartitionProcessor\Exception\SchemaNotEqualException
     * @throws \ReflectionException
     */
    public function run($argv)
    {
        list($dirs, $table, $databaseUrl, $envDatabaseUrl) = (new ConsoleOption())->getList($argv, [
            'dirs'              => '--locate-dirs',
            'table'             => '--table',
            'databaseUrl'       => '--database-url',
            'envDatabaseUrl'    => '--env-database-url'
        ]);

        $dirs = is_null($dirs) ? [__DIR__. '/../../../../../src'] : explode(':', $dirs);

        $locator = new Locator();
        /** @var Annotation $parser */
        $parser = new Annotation();

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

        $databaseUrl = is_null($databaseUrl) ? getenv($envDatabaseUrl) : $databaseUrl;
        $dsnParams = parse_url($databaseUrl);

        $processor = (new \Raketman\DatabasePartitionProcessor\Processor\ProcessorFactory())->create($dsnParams);

        foreach ($objects as $object) {
            $processor->process($object);
        }
    }
}

?>
