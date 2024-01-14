<?php

namespace core\admin\controllers;

use core\admin\models\Model;
use core\base\controllers\BaseController;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

abstract class BaseAdmin extends BaseController
{
    protected $model;

    protected $table;
    protected $columns;

    protected $menu;
    protected $title;

    protected function inputData()
    {
        $this->initJsScriptsAndCssStyles(true);

        $this->title = 'DOK engine';

        $this->model = Model::getInstance();

        if (!$this->model) {
            $this->model = Model::getInstance();
        }

        if (!$this->menu) {
            $this->menu = Settings::getSettingsByPropName('projectTables');
        }

        $this->sendNoCacheHeaders();
    }

    protected function outputData()
    {
    }

    protected function sendNoCacheHeaders(): void
    {
        header('Last-Modified: ' . gmdate('D, d m Y H:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Cache-Control: max-age=0');
        header('Cache-Control: post-check=0,pre-check=0');
    }

    protected function execBase(): void
    {
        self::inputData();
    }

    protected function createTableData()
    {
        if (!$this->table) {
            if ($this->parameters) {
                $this->table = array_keys($this->parameters)[0];
            } else {
                $this->table = Settings::getSettingsByPropName('defaultTable');
            }
        }

        $this->columns = $this->model->showColumns($this->table);

        if (!$this->columns) {
            throw new RouteException('Не найдены поля в таблице - ' . $this->table, 2);
        }
    }
}