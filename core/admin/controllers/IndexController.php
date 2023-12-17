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
            'order' => ['fio', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '1'
        ]);

        dd($res);
    }
}