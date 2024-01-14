<?php

namespace core\admin\controllers;

class ShowController extends BaseAdmin
{
    protected function inputData(): void
    {
        $this->execBase();
        $this->createTableData();

        dd($this->columns, $this->table);
    }

    protected function outputData()
    {
    }
}