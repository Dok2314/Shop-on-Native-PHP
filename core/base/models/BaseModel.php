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
        $fields = $this->createFields($table, $params);
        $where = $this->createWhere($table, $params);
        $joinArr = $this->createJoin($table, $params);

        $fields .= $joinArr['fields'] ?? '';
        $join = $joinArr['join'] ?? '';
        $where .= $joinArr['where'] ?? '';

        $fields = rtrim($fields, ',');

        $order = $this->createOrder($table, $params);
        
        $limit = $params['limit'] ?: '';

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    protected function createFields(string|bool $table = false, array $params = []): string
    {
        $params['fields'] = $this->getValueByKeyFromParams($params, 'fields', ['*']);

        $table = $table . '.' ?: '';

        $fields = '';
        foreach ($params['fields'] as $fieldName) {
            $fields .= $table . $fieldName . ',';
        }

        return $fields;
    }

    protected function createWhere(string $table, array $params = [])
    {

    }

    protected function createJoin(string $table, array $params = [])
    {

    }

    protected function createOrder(string $table, array $params = []): string
    {
        $table = $table . '.' ?: '';

        $orderBy = '';
        if (isset($params['order']) && is_array($params['order'])) {
            $params['order_direction'] = $this->getValueByKeyFromParams($params, 'order_direction', ['ASC']);

            $orderBy = 'ORDER BY ';
            $directCount = 0;
            foreach ($params['order'] as $order) {
                if (isset($params['order_direction'][$directCount])) {
                    $orderDirection = strtoupper($params['order_direction'][$directCount]);
                    $directCount++;
                } else {
                    $orderDirection = $params['order_direction'][$directCount - 1];
                }

                $orderBy .= $table . $order . ' ' . $orderDirection . ',';
            }
            $orderBy = rtrim($orderBy, ',');
        }

        return $orderBy;
    }

    private function getValueByKeyFromParams(array $params, string $key, $defaultValue = false): array
    {
        return (isset($params[$key]) && is_array($params[$key])) ? $params[$key] : $defaultValue;
    }
}