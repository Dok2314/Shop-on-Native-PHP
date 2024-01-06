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

        $files = [];

        $_POST['id'] = 8;
        $_POST['name'] = '';
        $_POST['content'] = "<p>Content it's From Oleg</p>";

        $columns = $model->edit($table);


//        $res = $model->add($table, [
////            'fields' => ['name' => 'Nika', 'content' => 'Hello2'],
//        ]);


//        $res = $model->get($table, [
//            'fields' => ['id', 'name'],
//            'where' => ['name' => "O'Raily"],
//            'limit' => '1'
//        ])[0];
//
//        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }
}