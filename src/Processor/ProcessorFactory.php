<?php

namespace Raketman\DatabasePartitionProcessor\Processor;

use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;
use Raketman\DatabasePartitionProcessor\Exception\PartitionNotFoundException;
use Raketman\DatabasePartitionProcessor\Exception\SchemaNotEqualException;
use Raketman\DatabasePartitionProcessor\Exception\SchemaNotFoundException;

class ProcessorFactory
{
    /**
     * @param $dsnParams
     * @return DbProcessorInterface
     * @throws SchemaNotFoundException
     */
    public function create($dsnParams)
    {
        /** @var \Raketman\DatabasePartitionProcessor\Processor\DbProcessorInterface  $processor */
        switch ($dsnParams['scheme']) {
            case 'pdo_mysql':
            case 'mysql':
                return new \Raketman\DatabasePartitionProcessor\Processor\MysqlDbProcessor($dsnParams);
                break;
            default:
                throw new SchemaNotFoundException("{$dsnParams['scheme']} не поддерживается", 500);
        }
    }

}