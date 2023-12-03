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

        $res = $model->get($table, [
            'fields' => ['id', 'name'],
            'where' => ['fio' => 'Koversun', 'name' => 'Daniil', 'patronymic' => 'Olexandrovich'],
            'operand' => ['<>', '='],
            'condition' => ['AND'],
            'order' => ['fio', 'name'],
            'order_direction' => ['ASC', 'DESC'],
            'limit' => '1'
        ]);

        dd($res);
    }
}