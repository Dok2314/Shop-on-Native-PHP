<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController
{
    const ADMIN_ROUTE_TYPE = 'admin';
    const USER_ROUTE_TYPE = 'user';
    const PLUGINS_ROUTE_TYPE = 'plugins';

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

        $lastSlashPosition = mb_strrpos($addressStr, '/');
        if ($lastSlashPosition === mb_strlen($addressStr) - 1 && $lastSlashPosition !== 0) {
            // $this->redirect(rtrim($addressStr, '/'), 301);
        }

        $path = substr($phpSelf, 0, strpos($phpSelf, 'index.php'));

        if ($path === PATH) {
            $this->routes = Settings::getSettingsByPropName('routes');
            if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании!');

            if(strpos($addressStr, $this->routes['admin']['alias']) === strlen(PATH))  {
                // ADMIN PART
                $url = explode('/', substr($addressStr, strlen(PATH . $this->routes['admin']['alias']) + 1));

                if (!empty($url[0]) && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0])) {
                    // PLUGINS PART
                    $plugin = array_shift($url);

                    $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings');

                    if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
                        $pluginSettings = str_replace('/', '\\', $pluginSettings);
                        $this->routes = $pluginSettings::getSettingsByPropName('routes');
                    }

                    $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
                    $dir = str_replace('//', '/', $dir);

                    $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;
                    $hrlUrl = $this->routes['plugins']['hrUrl'];
                    $routeType = self::PLUGINS_ROUTE_TYPE;
                } else {
                    $this->controller = $this->routes['admin']['path'];
                    $hrlUrl = $this->routes['admin']['hrUrl'];
                    $routeType = self::ADMIN_ROUTE_TYPE;
                }
            } else {
                // USER PART
                $url = explode('/', ltrim($addressStr, PATH));

                $hrlUrl = $this->routes['user']['hrUrl'];

                $this->controller = $this->routes['user']['path'];

                $routeType = self::USER_ROUTE_TYPE;
            }

            $this->createRoute($routeType, $url);

            $this->setParameters($url, $hrlUrl);
        } else {
            try {
                throw new \Exception('Некорректная директория сайта!');
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function createRoute(string $routeType, array $url): void
    {
        $route = [];
        $controllerAlias = $url[0] ?? false;

        if($controllerAlias) {
            if(isset($this->routes[$routeType]['routes'][$controllerAlias])) {
                $route = explode('/', $this->routes[$routeType]['routes'][$controllerAlias]);

                $this->controller .= ucfirst($route[0] . 'Controller');
            } else {
                $this->controller .= ucfirst($controllerAlias . 'Controller');
            }
        } else {
            $this->controller .= $this->routes['default']['controller'];
        }

        $this->inputMethod = $route[1] ?? $this->routes['default']['inputMethod'];
        $this->outputMethod = $route[2] ?? $this->routes['default']['outputMethod'];
    }

    private function setParameters(array $url, bool $hrlUrl): void
    {
        if(isset($url[1])) {
            $count = count($url);
            $key = '';

            if(!$hrlUrl) {
                $i = 1;
            } else {
                $this->parameters['alias'] = $url[1];
                $i = 2;
            }

            for (; $i < $count; $i++) {
                if(!$key) {
                    $key = $url[$i];
                    $this->parameters[$key] = '';
                } else {
                    $this->parameters[$key] = $url[$i];
                    $key = '';
                }
            }
        }
    }
}