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
    protected $data;

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

    protected function createTableData(): void
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

    protected function createData($arr = [], $add = true)
    {
        $fields = [];
        $order = [];
        $orderDirection = [];

        if ($add) {
            if(!isset($this->columns['id_row'])) {
                return $this->data = [];
            }

            $fields[] = $this->columns['id_row'] . ' as id';

            if (isset($this->columns['name'])) {
                $fields['name'] = 'name';
            }

            if (isset($this->columns['img'])) {
                $fields['img'] = 'img';
            }

            if (count($fields) < 3) {
                foreach ($this->columns as $columnKey => $columnVal) {
                    if (!isset($fields['name']) && str_contains($columnKey, 'name')) {
                        $fields['name'] = $columnKey . ' as name';
                    }

                    if (!isset($fields['img']) && str_starts_with($columnKey, 'img')) {
                        $fields['img'] = $columnKey . ' as img';
                    }
                }
            }

            if (isset($arr['fields'])) {
                $fields = Settings::getInstance()->arrayMergeRecursive($fields, $arr['fields']);
            }

            if (isset($this->columns['parent_id'])) {
                if (!in_array('parent_id', $fields)) {
                    $fields[] = 'parent_id';
                }

                $order[] = 'parent_id';
            }

            if (isset($this->columns['menu_position'])) {
                $order[] = 'menu_position';
            } elseif (isset($this->columns['date'])) {
                if ($order) {
                    $orderDirection = ['ASC', 'DESC'];
                } else {
                    $orderDirection = ['DESC'];
                }

                $order[] = 'date';
            }

            if (isset($arr['order'])) {
                $order = Settings::getInstance()->arrayMergeRecursive($order, $arr['order']);
            }

            if (isset($arr['order_direction'])) {
                $orderDirection = Settings::getInstance()->arrayMergeRecursive($orderDirection, $arr['order_direction']);
            }
        } else {
            if (!$arr) {
                return $this->data = [];
            }

            $fields = $arr['fields'];
            $order = $arr['order'];
            $orderDirection = $arr['order_direction'];
        }

        $this->data = $this->model->get($this->table, [
            'fields' => $fields,
            'order' => $order,
            'order_direction' => $orderDirection,
        ]);
    }
}