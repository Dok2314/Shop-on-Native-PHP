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
     * string $crud = r - SELECT / c - INSERT / u - UPDATE / d - DELETE
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

        $limit = isset($params['limit']) ? "LIMIT {$params['limit']}" : '';

        $fields = rtrim($fields, ', ');

        $query = "SELECT $fields FROM $table $join $where $order $limit";

        return $this->query($query);
    }

    /**
     * @param $table - таблица для вставки данных;
     * @param array $params - массив параметров;
     * fields => [поле => значение]; если не указан, то обрабатывается $_POST[поле => значение]
     * разрешена передача например NOW() в качестве MySQL функции обычной строкой
     * files = [поле => значение]; можно подать массив вида [поле => [массив значений]]
     * except => ['исключение 1', 'исключение 2'] - исключает данные элементы массива из добавления в запрос
     * return_id => true/false - возвращать или нет идентификатор вставленой записи
     * @return string|int|bool|array
     * @throws DBException
     */
    final public function add($table, array $params = []): string|int|bool|array
    {
        $params['fields'] = $this->containAndArray($params, 'fields') ? $params['fields'] : $_POST;
        $params['files'] = $this->containAndArray($params, 'files') ? $params['files'] : false;

        if (!$this->contain($params, 'fields') && !$this->contain($params, 'files')) {
            return false;
        }

        $params['except'] = $this->containAndArray($params, 'except') ? $params['except'] : false;

        $params['return_id'] = $this->contain($params, 'return_id');

        $insert_arr = $this->createInsert($params['fields'], $params['files'], $params['except']);

        if ($insert_arr) {
            $query = "INSERT INTO $table ({$insert_arr['fields']}) VALUES({$insert_arr['values']})";

            return $this->query($query, 'c', $params['return_id']);
        }

        return false;
    }

    final public function edit($table, array $params = []): array|bool|int|string
    {
        $params['fields'] = $this->containAndArray($params, 'fields') ? $params['fields'] : $_POST;
        $params['files'] = $this->containAndArray($params, 'files') ? $params['files'] : false;

        if (!$this->contain($params, 'fields') && !$this->contain($params, 'files')) {
            return false;
        }

        $params['except'] = $this->containAndArray($params, 'except') ? $params['except'] : false;

        $where = '';

        if (!$this->contain($params, 'all_rows')) {
            if ($this->contain($params, 'where')) {
                $where = $this->createWhere($params);
            } else {
                $columns = $this->showColumns($table);

                if (!$columns) {
                    return false;
                }

                if ($this->contain($columns, 'id_row') && $this->contain($params['fields'], $columns['id_row'])) {
                    $where = 'WHERE ' . $columns['id_row'] . '=' . $params['fields'][$columns['id_row']];
                    unset($params['fields'][$columns['id_row']]);
                }
            }
        }

        $update = $this->createUpdate($params['fields'], $params['files'], $params['except']);

        $query = "UPDATE $table SET $update $where";

        return $this->query($query, 'u');
    }

    /**
     * @param string $table - Таблица базы данных
     * @param array $params
     * 'fields'           => ['id', 'name'],
     * 'where'            => ['fio' => 'Smirnov', 'name' => 'Oleg', 'surname' => 'Sergeevich'],
     * 'operand'          => ['=', '<>'],
     * 'condition'        => ['AND'],
     * 'join' => [
     *      'table'             => 'teachers',
     *      'fields'            => ['id as j_id', 'name as j_name'],
     *      'type'              => 'left',
     *      'where'             => ['name' => 'Sasha'],
     *      'operand'           => ['='],
     *      'condition'         => ['OR'],
     *      'on'                => ['id', 'parent_id'],
     *      'group_condition'   => 'AND'
     *      ]
     *  ],
     *  'join_table1' => [
     *      'table'     => 'join_table2',
     *      'fields'    => ['id as j_id', 'name as j_name'],
     *      'type'      => 'left',
     *      'where'     => ['name' => 'Sasha'],
     *      'operand'   => ['='],
     *      'condition' => ['OR'],
     *      'on'        => [
     *      'table'  => 'teachers',
     *      'fields' => ['id', 'parent_id']
     *      ]
     *  ]
     * @return array|bool|int|string
     */
    final public function delete(string $table, array $params = [])
    {
        $table = trim($table);

        $where = $this->createWhere($params, $table);

        $columns = $this->showColumns($table);

        if (!$columns) {
            return false;
        }

        if ($this->containAndArray($params, 'fields')) {
            if ($this->contain($columns, 'id_row')) {
                $key = array_search($columns['id_row'], $params);

                if($key !== false) {
                    unset($params['fields'][$key]);
                }
            }

            $fields = [];
            foreach ($params['fields'] as $field) {
                $fields[$field] = $columns[$field]['Default'];
            }

            $update = $this->createUpdate($fields, false, false);

            $query = "UPDATE $table SET $update $where";
        } else {
            $joinArr = $this->createJoin($table, $params);
            $join = $joinArr['join'];
            $joinTables = $joinArr['tables'];

            $query = "DELETE $table" . $joinTables . " FROM " . $table . " " . $join . ' ' . $where;
        }

        return $this->query($query, 'u');
    }

    final public function showColumns($table): array
    {
        $query = "SHOW COLUMNS FROM $table";

        $res = $this->query($query);

        $columns = [];

        if ($res) {
            foreach ($res as $column) {
                $columns[$column['Field']] = $column;

                if ($column['Key'] === 'PRI') {
                    $columns['id_row'] = $column['Field'];
                }
            }
        }

        return $columns;
    }
}