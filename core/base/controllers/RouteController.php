<?php

namespace core\base\controllers;

class RouteController
{
    private static $instance;

    private function __construct()
    {
    }

    private function __clone(): void
    {
    }

    public static function getInstance(): RouteController
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public function route()
    {

    }
}