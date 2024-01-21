<?php

namespace core\admin\controllers;

class ShowController extends BaseAdmin
{
    protected function inputData(): void
    {
        $this->execBase();
        $this->createTableData();
        $this->createData();

        dd($this->columns, $this->table);
    }

    protected function outputData()
    {
    }
}