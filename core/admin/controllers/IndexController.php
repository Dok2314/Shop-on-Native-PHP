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
            'where' => ['name' => "O'Raily"],
            'limit' => '1'
        ])[0];

        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }
}