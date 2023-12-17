<?php

namespace core\admin\controllers;

use core\admin\models\Model;
use core\base\controllers\BaseController;

class IndexController extends BaseController
{
    protected function inputData()
    {
        $model = Model::getInstance();

        $table = 'teachers';

        $colors = ['red', 'green', 'blue'];

        $query = "(SELECT t1.name, t2.fio FROM t1 LEFT JOIN t2 ON t1.parent_id = t2.id WHERE t1.parent_id = 1)
                 UNION 
                 (SELECT t1.name, t2.fio FROM t1 LEFT JOIN t2 ON t1.parent_id = t2.id WHERE t2.id = 1)
                 ORDER BY t1.name ASC
        ";

        $res = $model->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['name' => 'daniil, oleg, olya', 'patronymic' => 'Ivanovich', 'fio' => 'Andreev', 'car' => 'Porsche', 'color' => $colors],
            'operand' => ['IN', '%LIKE%', '<>', '=', 'NOT IN'],
            'condition' => ['AND', 'OR'],
            'order' => [1, 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '1',
            'join' => [
                [
//                    'table' => 'join_table1',
                    'fields' => ['id as j_id', 'name as j_name'],
                    'type' => 'left',
                    'where' => ['name' => 'Sasha'],
                    'operand' => ['='],
                    'condition' => ['OR'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id'],
                    ],
                ],
                'join_table2' => [
                    'table' => 'join_table2',
                    'fields' => ['id as j2_id', 'name as j2_name'],
                    'type' => 'left',
                    'where' => ['name' => 'Sasha'],
                    'operand' => ['<>'],
                    'condition' => ['AND'],
                    'on' => [
                        'table' => 'teachers',
                        'fields' => ['id', 'parent_id'],
                    ],
                ],
            ],
        ]);

        dd($res);
    }
}