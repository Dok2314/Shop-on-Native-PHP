<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController
{
    private static $instance;

    protected array $routes;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

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
        $addressStr = $_SERVER['REQUEST_URI'];
        $phpSelf = $_SERVER['PHP_SELF'];

        $lastSlashPosition = strrpos($addressStr, '/');
        if ($lastSlashPosition === strlen($addressStr) - 1 && $lastSlashPosition !== 0) {
            // $this->redirect(rtrim($addressStr, '/'), 301);
        }

        $path = substr($phpSelf, 0, strpos($phpSelf, 'index.php'));

        if ($path === PATH) {
            $this->routes = Settings::getSettingsByPropName('routes');
            if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании!');

            if(strpos($addressStr, $this->routes['admin']['alias']) === strlen(PATH))  {
                // Admin panel
            } else {
                $url = explode('/', ltrim($addressStr, PATH));

                $hrlUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $route = 'user';
            }

            $this->createRoute($route, $url);
        } else {
            try {
                throw new \Exception('Некорректная директория сайта!');
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }

        exit();
    }

    private function createRoute(string $routeType, array $url): void
    {
        $route = [];

        if(!empty($url[0])) {
            if(isset($this->routes[$routeType]['routes'][$url[0]])) {
                $route = explode('/', $this->routes[$routeType]['routes'][$url[0]]);

                $this->controller .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controller .= ucfirst($url[0] . 'Controller');
            }
        } else {
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod = $route[1] ?? $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ?? $this->routes['default']['outputMethod'];
    }
}