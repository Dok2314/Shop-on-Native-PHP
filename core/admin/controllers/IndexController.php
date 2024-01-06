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

        $files['gallery_img'] = ['red.jpg', 'green.jpg', 'black.jpg'];
        $files['img'] = 'main.jpg';

        $res = $model->add($table, [
            'fields' => ['name' => 'Daniil', 'content' => 'Hello'],
            'files' => $files,
        ]);

//        $res = $model->get($table, [
//            'fields' => ['id', 'name'],
//            'where' => ['name' => "O'Raily"],
//            'limit' => '1'
//        ])[0];
//
//        exit('id = ' . $res['id'] . ' Name = ' . $res['name']);
    }
}