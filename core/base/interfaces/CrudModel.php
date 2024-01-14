<?php
namespace core\base\interfaces;

interface CrudModel
{
    public function get(string $table, array $params = []);
    public function add($table, array $params = []);
    public function edit($table, array $params = []);
    public function delete(string $table, array $params = []);
}