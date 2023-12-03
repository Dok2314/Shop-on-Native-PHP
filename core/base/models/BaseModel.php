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
        $fields = $this->createFields($params, $table);
        $where = $this->createWhere($params, $table);
        $joinArr = $this->createJoin($table, $params);

        $fields .= $joinArr['fields'] ?? '';
        $join = $joinArr['join'] ?? '';
        $where .= $joinArr['where'] ?? '';

        $fields = rtrim($fields, ',');

        $order = $this->createOrder($params, $table);

        $limit = $params['limit'] ?: '';

        dd($where);
        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    protected function createFields(array $params, string|bool $table = false): string
    {
        $params['fields'] = $this->getValueByKeyFromParams($params, 'fields', ['*']);

        $table = $table ? $table . '.' : '';

        $fields = '';
        foreach ($params['fields'] as $fieldName) {
            $fields .= $table . $fieldName . ',';
        }

        return $fields;
    }

    protected function createWhere(array $params, string|bool $table = false, $instruction = 'WHERE')
    {
        $table = $table ? $table . '.' : '';
        $where = '';

        if ($this->contain($params, 'where')) {
            $params['operand'] = $this->getValueByKeyFromParams($params, 'operand', ["="]);
            $params['condition'] = $this->getValueByKeyFromParams($params, 'condition', ["AND"]);

            $where = $instruction;

            $operandCount = 0;
            $conditionCount = 0;

            foreach ($params['where'] as $key => $value) {
                $where .= ' ';

                if (isset($params['operand'][$operandCount])) {
                    $operand = $params['operand'][$operandCount];
                    $operandCount++;
                } else {
                    $operand = $params['operand'][$operandCount - 1];
                }

                if (isset($params['condition'][$conditionCount])) {
                    $condition = $params['condition'][$conditionCount];
                    $conditionCount++;
                } else {
                    $condition = $params['condition'][$conditionCount - 1];
                }

                if ($operand === 'IN' || $operand === 'NOT IN') {
                    if (is_string($value) && str_starts_with($value, 'SELECT')) {
                        $inStr = $value;
                    } else {
                        if (is_array($value)) {
                            $tempValue = $value;
                        } else {
                            $tempValue = explode(',', $value);
                        }

                        $inStr = '';

                        foreach ($tempValue as $tmpVal) {
                            $inStr .= "'" . addslashes(trim($tmpVal)) . "',";
                        }
                    }

                    $where .= $table . $key . ' ' . $operand . ' (' . trim($inStr, ',') . ') ' . $condition;
                }
            }
        }

        return $where;
    }

    protected function createJoin(string $table, array $params = [])
    {

    }

    protected function createOrder(array $params, string|bool $table = false): string
    {
        $table = $table ? $table . '.' : '';

        $orderBy = '';
        if ($this->contain($params, 'order')) {
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
        return ($this->contain($params, $key)) ? $params[$key] : $defaultValue;
    }

    private function contain($params, $key): bool
    {
        return isset($params[$key]) && is_array($params[$key]);
    }
}