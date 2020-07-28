<?php

namespace Raketman\DatabasePartitionProcessor\Processor;

use Raketman\DatabasePartitionProcessor\Annotation\RaketmanDatePartition;
use Raketman\DatabasePartitionProcessor\Exception\PartitionNotFoundException;
use Raketman\DatabasePartitionProcessor\Exception\SchemaEqualException;
use Raketman\DatabasePartitionProcessor\Exception\SchemaNotEqualException;

class MysqlDbProcessor implements DbProcessorInterface
{
    use DateCalculatorTrait;


    /** @var \PDO  */
    protected $pdo;

    public function __construct($databaseUrlParams)
    {
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;%s',
            $databaseUrlParams['host'],
            $databaseUrlParams['port'],
            substr($databaseUrlParams['path'], 1),
            implode(';', explode('&', $databaseUrlParams['query']))
        );

        $this->pdo = new \PDO($dsn, $databaseUrlParams['user'], $databaseUrlParams['pass']);
    }

    public function prolongate(RaketmanDatePartition $partition)
    {
        $dateField = $partition->date_field;
        $table = $partition->table;

        $this->calculateInterval($partition, 'forward');

        list($notExists, $schemaEqual, $partitions) = $this->checkSchemaEqual($partition);

        if ($notExists || !$schemaEqual) {
            throw new SchemaNotEqualException("primary id not equal to schema", 500);
        }

        // Надо произвести реорганизацию таблиц
        if (0 === count($partitions)) {
            throw new PartitionNotFoundException("dont have create partition", 500);
        }


        //TODO: читаем схему, разбираем как созданы

        $partitions = [];
        foreach ($partitionSources as $partitionSource) {
            $partitions[$partitionSource['PARTITION_DESCRIPTION']] = $partitionSource;
        }

        // Построим партиции для проверки
        $currentPartition = date($format, strtotime(sprintf($interval, '1', $period)));
        // Пробуем создать следующую партицию
        $plus1Partition = date($format, strtotime(sprintf($interval, '2', $period)));
        $plus2Partition = date($format, strtotime(sprintf($interval, '3', $period)));

        $checkPartitions = [$currentPartition, $plus1Partition, $plus2Partition];
        // Если ее еще нет
        foreach ($checkPartitions as $partition) {
            if (!$partitions[$partition]) {
                // Выполним запрос
                $Con->ezQuery(sprintf(
                    "ALTER TABLE  {$table}  REORGANIZE PARTITION pmaxval INTO(PARTITION %s VALUES LESS THAN (%s), PARTITION pmaxval VALUES LESS THAN MAXVALUE )",
                    sprintf('p%s', $partition),
                    $partition
                ));

                unset($partitions[$partition]);
            }
        }

        // Удалим ненужные
        foreach ($partitions as $partition => $info) {
            if (in_array($partition ,['MAXVALUE', 'pmaxval'])) {
                continue;
            }

            if (in_array($partition, $checkPartitions)) {
                continue;
            }

            if (in_array($partition, $safePartitions)) {
                continue;
            }

            $Con->ezQuery(sprintf(
                "ALTER TABLE {$table} DROP PARTITION %s;",
                sprintf('p%s' , $partition)
            ));
        }
    }


    public function create(RaketmanDatePartition $partition)
    {
        list($notExists, $schemaEqual, $partitions) = $this->checkSchemaEqual($partition);

        $primaries = [$partition->id_field, $partition->date_field];

        // Надо произвести реорганизацию таблиц
        if (!$notExists && $schemaEqual && (count($partitions) > 1)) {
            throw new SchemaEqualException('schema already equal');
        }

        $newPrimary = implode('`, `', $primaries);
        $this->pdo->exec("alter table {$partition->table} drop PRIMARY KEY, add primary key (`{$newPrimary}`);");

        // Найдем партиции
        list($minDate, $maxDate) = $this->pdo->query("SELECT MIN({$partition->date_field}), MAX({$partition->date_field}) FROM {$partition->table}")->fetch(\PDO::FETCH_NUM);

        $minDate = new \DateTime($minDate);
        $maxDate = new \DateTime($maxDate);

        $maxDate = max($maxDate, new \DateTime());

        $createPartitions = $this->calculateIntervalByMinMax($partition, $minDate, $maxDate);

        $createPartitionSql = implode(",\n", $createPartitions);

        $range = $this->getRange($partition);

        // Реорганизуем партиции
        $addPartitionSql = <<<SQL
ALTER TABLE {$partition->table}
PARTITION BY RANGE ({$range})
(
{$createPartitionSql},
PARTITION pmaxval VALUES LESS THAN MAXVALUE);
SQL;

        $this->pdo->query($addPartitionSql);
    }


    protected function checkSchemaEqual(RaketmanDatePartition $partition)
    {
        // Проверим таблицу на совпадение id
        $primaries = $this->pdo->query("SHOW KEYS FROM {$partition->table} WHERE Key_name = 'PRIMARY'");//->fetch(\PDO::FETCH_OBJ);

        $checkColumns = [$partition->id_field, $partition->date_field];
        $notExists = false;
        // Проверим, что primary совпадают
        foreach ($primaries as $primary) {
            if (!in_array($primary['Column_name'], $checkColumns)) {
                $notExists = true;
                break;
            }

            $checkColumns = array_diff($checkColumns, [$primary['Column_name']]);
        }

        // Получаем текущие партиции
        $partitions = $this->pdo->query("SELECT PARTITION_NAME,PARTITION_DESCRIPTION, TABLE_ROWS
            FROM INFORMATION_SCHEMA.PARTITIONS
            WHERE TABLE_NAME = '{$partition->table}';
        ")->fetchAll(\PDO::FETCH_ASSOC);

        return [$notExists, count($checkColumns) === 0, $partitions];
    }
}