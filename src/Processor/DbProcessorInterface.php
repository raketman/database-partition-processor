<?php

namespace Raketman\DatabasePartitionProcessor\Processor;

use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;

interface DbProcessorInterface
{
    /**
     * Обработка партиций
     *
     * @param RaketmanDatePartition $partition
     * @return mixed
     */
    public function prolongate(RaketmanDatePartition $partition);

    /**
     * Создание партиций
     *
     * @param RaketmanDatePartition $partition
     * @return mixed
     */
    public function create(RaketmanDatePartition $partition);
}