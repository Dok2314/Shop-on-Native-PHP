<?php

namespace core\base\models;

use core\base\controllers\traits\Singleton;
use core\base\exceptions\DBException;
use mysqli;

class BaseModel
{
    use Singleton, BaseModelMethods;

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

    /**
     * @throws DBException
     */
    final public function query(string $query, $crud = 'r', $returnId = false): int|bool|array|string
    {
        $result = $this->db->query($query);

        if ($this->db->affected_rows === -1) {
            throw new DBException('Ошибка в SQL запросе: ' . $query . ' - ' . $this->db->errno . ' ' . $this->db->error);
        }

        switch ($crud) {
            case 'r':
                if ($result->num_rows) {
                    $res = [];

                    for ($i = 0; $i < $result->num_rows; $i++) {
                        $res[] = $result->fetch_assoc();
                    }

                    return $res;
                }

                return false;
            case 'c':
                if ($returnId) return $this->db->insert_id;
                return true;
            default:
                return true;
        }
    }

    /**
     * @param string $table - Таблица базы данных
     * @param array $params
     * 'fields' => ['id', 'name'],
     * 'where' => ['fio' => 'Ivanov', 'name' => 'Ivan', 'patronymic' => 'Ivanovich'],
     * 'operand' => ['<>', '='],
     * 'condition' => ['AND'],
     * 'order' => ['fio', 'name'],
     * 'order_direction' => ['ASC', 'DESC'],
     * 'limit' => '1'
     * @return array|bool|int|string
     * @throws DBException
     */
    final public function get(string $table, array $params = []): array|bool|int|string
    {
        $fields = $this->createFields($params, $table);
        $where = $this->createWhere($params, $table);

        $newWhere = !$where;

        $joinArr = $this->createJoin($table, $params, $newWhere);

        $fields .= $joinArr['fields'] ?? '';
        $join = $joinArr['join'] ?? '';
        $where .= $joinArr['where'] ?? '';

        $order = $this->createOrder($params, $table);

        $limit = $params['limit'] ? "LIMIT {$params['limit']}" : '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";
        dd($query);

        return $this->query($query);
    }
}