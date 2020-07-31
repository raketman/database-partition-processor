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

    public function calculateInterval(RaketmanDatePartition $partition, \DateTime $startDate, $direction)
    {
        $format = $this->getFormat($partition);

        $result = [];

        switch ($direction) {
            case 'forward':
                while(count($result) < $partition->create_period) {
                    $startDate->add(new \DateInterval('P1D'));

                    $result[$startDate->format($format)] = sprintf('PARTITION %s VALUES LESS THAN (%s)',
                        sprintf('p%s', $startDate->format($format)),
                        $startDate->format($format)
                    );
                }

                return $result;
                break;
            case 'back':

                while(count($result) < $partition->safe_period) {
                    $result[$startDate->format($format)] = sprintf('PARTITION %s VALUES LESS THAN (%s)',
                        sprintf('p%s', $startDate->format($format)),
                        $startDate->format($format)
                    );

                    $startDate->sub(new \DateInterval('P1D'));
                }

                break;
        }


        return $result;
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