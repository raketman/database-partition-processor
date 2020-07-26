<?php

namespace Raketman\DatabasePartitionProcessor\Annotation;

/**
 * Class RaketmanDatePartition
 * @package Raketman\DatabasePartitionProcessor\Annotation
 *
 */
class RaketmanDatePartition
{
    public $table = null;

    /**
     * Поле, которое является id
     * @var null
     */
    public $id_field = null;


    /**
     * Тип партиции, доступ по дате day/month/year
     * @var
     */
    public $type = null;


    /**
     * Поле, по которому будет идти расчет партации
     *
     * @var null
     */
    public $date_field = null;

    /**
     * сколько
     *
     * @var null
     */
    public $safe_period = null;

    /**
     * На сколько вперед партиций создавать
     *
     * @var null
     */
    public $create_period = null;

    /**
     * Обработка в ручном режиме
     *
     * @var bool
     */
    public $manual = 'false';


    public function isManual()
    {
        return $this->manual === 'true';
    }
}