<?php

namespace core\base\models;

trait BaseModelMethods
{
    protected function createFields(array $params, string|bool $table = false): string
    {
        $params['fields'] = $this->getValueByKeyFromParams($params, 'fields', ['*']);
        $table = $table ? $table . '.' : '';

        $fields = '';
        foreach ($params['fields'] as $fieldName) {
            $fields .= $table . $fieldName . ', ';
        }

        return rtrim($fields, ', ');
    }

    protected function createWhere(array $params, string|bool $table = false, $instruction = 'WHERE')
    {
        $table = $table ? $table . '.' : '';
        $where = '';

        if ($this->containAndArray($params, 'where')) {
            $params['operand'] = $this->getValueByKeyFromParams($params, 'operand', ["="]);
            $params['condition'] = $this->getValueByKeyFromParams($params, 'condition', ["AND"]);

            $where = $instruction;

            $operandCount = 0;
            $conditionCount = 0;
            $condition = '';

            foreach ($params['where'] as $key => $value) {
                $where .= ' ';

                $operand = $this->getOperand($params, $operandCount);
                $condition = $this->getCondition($params, $conditionCount);

                if ($operand === 'IN' || $operand === 'NOT IN') {
                    $inStr = $this->getInStr($value);

                    $where .= $table . $key . ' ' . $operand . ' (' . $inStr . ') ' . $condition;
                } elseif (str_contains($operand, 'LIKE')) {
                    $value = $this->resolveLikeOperand($operand, $value);

                    $where .= $table . $key . ' LIKE ' . "'" . $value . "' $condition";
                } else {
                    if (str_starts_with($value, 'SELECT')) {
                        $where .= $table . $key . $operand . '(' . $value . ") $condition";
                    } else {
                        $where .= $table . $key . $operand . "'" . $value . "' $condition";
                    }
                }
            }

            $where = trim(substr($where, 0, strrpos($where, $condition)));
        }

        return $where;
    }


    /**
     * @param string $table
     * @param array $params
     * @param bool $newWhere
     * @return array
     *
     * 'join' => [
     * [
     * 'table' => 'join_table1',
     * 'fields' => ['id as j_id', 'name as j_name'],
     * 'type' => 'left',
     * 'where' => ['name' => 'Sasha'],
     * 'operand' => ['='],
     * 'condition' => ['OR'],
     * 'on' => ['id', 'parent_id'],
     * 'group_condition' => 'AND',
     * ],
     * 'join_table2' => [
     * 'table' => 'join_table2',
     * 'fields' => ['id as j2_id', 'name as j2_name'],
     * 'type' => 'left',
     * 'where' => ['name' => 'Sasha'],
     * 'operand' => ['<>'],
     * 'condition' => ['AND'],
     * 'on' => [
     *      'table' => 'teachers',
     *      'fields' => ['id', 'parent_id'],
     * ],
     * ],
     * ],
     *
     */
    protected function createJoin(string $table, array $params = [], bool $newWhere = false): array
    {
        $fields = '';
        $join = '';
        $where = '';

        if ($this->contain($params, 'join')) {
            $joinTable = $table;

            foreach ($params['join'] as $key => $value) {
                if (is_int($key)) {
                    if (!$this->contain($value, 'table')) {
                        continue;
                    } else {
                        $key = $value['table'];
                    }
                }

                if ($join) {
                    $join .= ' ';
                }

                if ($this->contain($value, 'on')) {
                    $joinFields = [];

                    switch (2) {
                        case count($value['on']['fields']):
                            $joinFields = $value['on']['fields'];
                            break;
                        case count($value['on']):
                            $joinFields = $value['on'];
                            break;
                        default:
                            // Go to the next iteration of the foreach loop
                            continue 2;
                    }

                    // Default using LEFT JOIN
                    if(!$this->contain($value, 'type')) {
                        $join .= 'LEFT JOIN';
                    } else {
                        $join .= trim(strtoupper($value['type'])) . ' JOIN ';
                    }

                    $join .= $key . ' ON ';

                    if($this->contain($value['on'], 'table')) {
                        $join .= $value['on']['table'];
                    } else {
                        $join .= $joinTable;
                    }

                    $join .= '.' . $joinFields[0] . '=' . $key . '.' . $joinFields[1];

                    $joinTable = $key;

                    if($newWhere) {
                        if($this->contain($value, 'where')) {
                            $newWhere = false;
                        }

                        $groupCondition = 'WHERE';
                    } else {
                        $groupCondition = $value['group_condition'] ? strtoupper($value['group_condition']) : 'AND';
                    }

                    $fields .= $this->createFields($key, $value);

                    $where .= $this->createWhere($key, $value, $groupCondition);
                }
            }
        }

        return compact('fields', 'join', 'where');
    }

    protected function createOrder(array $params, string|bool $table = false): string
    {
        $table = $table ? $table . '.' : '';

        $orderBy = '';
        if ($this->containAndArray($params, 'order')) {
            $params['order_direction'] = $this->getValueByKeyFromParams($params, 'order_direction', ['ASC']);

            $orderBy = 'ORDER BY ';
            $directCount = 0;
            foreach ($params['order'] as $orderColumn) {
                $orderDirection = $this->getOrderDirection($params, $directCount);

                if (is_int($orderColumn)) {
                    $orderBy .= $orderColumn . ' ' . $orderDirection . ', ';
                } else {
                    $orderBy .= $table . $orderColumn . ' ' . $orderDirection . ', ';
                }
            }
            $orderBy = rtrim($orderBy, ', ');
        }

        return $orderBy;
    }

    private function getValueByKeyFromParams(array $params, string $key, $defaultValue = false): array
    {
        return ($this->containAndArray($params, $key)) ? $params[$key] : $defaultValue;
    }

    private function containAndArray($params, $key): bool
    {
        return isset($params[$key]) && is_array($params[$key]);
    }

    private function contain($stack, $needle): bool
    {
        return isset($stack[$needle]);
    }

    private function getOrderDirection($params, &$directCount)
    {
        if (isset($params['order_direction'][$directCount])) {
            $orderDirection = strtoupper($params['order_direction'][$directCount]);
            $directCount++;
        } else {
            $orderDirection = $params['order_direction'][$directCount - 1];
        }

        return $orderDirection;
    }

    private function getOperand($params, &$operandCount)
    {
        if (isset($params['operand'][$operandCount])) {
            $operand = $params['operand'][$operandCount];
            $operandCount++;
        } else {
            $operand = $params['operand'][$operandCount - 1];
        }

        return $operand;
    }

    private function getCondition($params, &$conditionCount)
    {
        if (isset($params['condition'][$conditionCount])) {
            $condition = $params['condition'][$conditionCount];
            $conditionCount++;
        } else {
            $condition = $params['condition'][$conditionCount - 1];
        }

        return $condition;
    }

    private function getInStr($value): string
    {
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

        return trim($inStr, ',');
    }

    private function resolveLikeOperand($operand, $value): string
    {
        $likeTemplate = explode('%', $operand);

        foreach ($likeTemplate as $likeTmpKey => $likeTmpVal) {
            // Нет $likeTmpVal - в нём пустая строка и был '%',
            // проверяю ключ, если его нет - он = 0, нужно приклеить '%' в начало строки
            // если ключ есть - нужно приклеить в конец строки '%'
            if (!$likeTmpVal) {
                if (!$likeTmpKey) {
                    $value = '%' . $value;
                } else {
                    $value .= '%';
                }
            }
        }

        return $value;
    }
}