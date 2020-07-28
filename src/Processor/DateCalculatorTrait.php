<?php

namespace Raketman\DatabasePartitionProcessor\Processor;

use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;
use Raketman\DatabasePartitionProcessor\Enum\DateType;

trait DateCalculatorTrait
{
    public function calculateIntervalByMinMax(RaketmanDatePartition $partition, \DateTime $minDate, \DateTime $maxDate)
    {

        $format = $this->getFormat($partition);

        $result = [];
        while($minDate < $maxDate) {
            $minDate->add(new \DateInterval('P1D'));

            $result[$minDate->format($format)] = sprintf('PARTITION %s VALUES LESS THAN (%s)',
                sprintf('p%s', $minDate->format($format)),
                $minDate->format($format)
            );
        }

        return $result;
    }

    public function calculateInterval(RaketmanDatePartition $partition, $direction)
    {
        switch ($partition->type) {
            case DateType::MONTH:
                $interval = '+%s %s';
                $format = 'Ym';
                $period = 'month';
                $range = sprintf('(YEAR(%s) * 100 + MONTH(%s))', $partition->date_field, $partition->date_field);
                $safePartitions = [];


                $safeInterval = '-%s day';

                for ($i= 0; $i < $partition->safe_period * 31; $i++) {
                    $key = date($format, strtotime(sprintf($safeInterval, $i, $period)));

                    $safePartitions[$key] = $key;
                }

                break;

            default:
                throw new \Exception('Unknown type: ' . $partition->type , 500);
        }
    }

    protected function getRange(RaketmanDatePartition $partition)
    {
        switch ($partition->type) {
            case DateType::MONTH:
                return sprintf('(YEAR(%s) * 100 + MONTH(%s))', $partition->date_field, $partition->date_field);;
                break;
            case DateType::DAY:
                return sprintf('(YEAR(%s) * 10000 + MONTH(%s) * 100 + DAY(%s))', $partition->date_field, $partition->date_field, $partition->date_field);;
                break;
            case DateType::YEAR:
                return sprintf('(YEAR(%s))', $partition->date_field);
                break;
        }
    }

    protected function getFormat(RaketmanDatePartition $partition)
    {
        switch ($partition->type) {
            case DateType::MONTH:
                return 'Ym';
                break;
            case DateType::DAY:
                return 'Ymd';
                break;
            case DateType::YEAR:
                return 'Y';
                break;
        }
    }
}