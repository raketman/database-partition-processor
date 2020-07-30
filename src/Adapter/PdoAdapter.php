<?php

namespace Raketman\DatabasePartitionProcessor\Adapter;

use Raketman\DatabasePartitionProcessor\Exception\PdoAdapterException;

class PdoAdapter extends \PDO
{
    /** @var \PDO  */
    protected $pdo;

    public function __construct($dsn, $username = null, $passwd = null, $options = null)
    {
        $this->pdo = new \PDO($dsn, $username, $passwd, $options);
    }

    /**
     * @param string $statement
     * @return false|\PDOStatement
     * @throws PdoAdapterException
     */
    public function query($statement)
    {
        $result = $this->pdo->query($statement);

        if (false === $result) {
            throw new PdoAdapterException($this->pdo->errorInfo()[2], $this->pdo->errorInfo()[0]);
        }

        return $result;
    }

    /**
     * @param string $statement
     * @return false|\PDOStatement
     * @throws PdoAdapterException
     */
    public function exec($statement)
    {
        $result = $this->pdo->query($statement);

        if (false === $result) {
            throw new PdoAdapterException($this->errorCode(), 500);
        }

        return $result;
    }
}