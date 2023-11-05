<?php

namespace core\base\controllers;

use core\base\settings\Settings;

class RouteController
{
    private static $instance;


    public function route()
    {
    }

    public static function getInstance(): RouteController
    {
        if(self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    private function __clone(): void
    {
    }

    private function __construct()
    {
        $s = Settings::get('routes');

        exit();
    }
}