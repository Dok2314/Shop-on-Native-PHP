<?php

namespace core\base\models;

use core\base\controllers\traits\Singleton;
use core\base\exceptions\DBException;
use mysqli;

class BaseModel
{
    use Singleton;

    protected mysqli $db;

    /**
     * @throws DBException
     */
    private function __construct()
    {
        $this->db = @new mysqli(HOST, USERNAME, PASSWORD, DB_NAME);

        if ($this->db->connect_errno) {
            throw new DBException('mysqli connection error: ' . $this->db->connect_error);
        }

        $this->db->query("SET NAMES UTF8");
    }

    final public function query(string $query, $crud = 'r', $returnId = false)
    {
        $result = $this->db->query($query);
    }
}