<?php

namespace core\base\controllers;

use core\base\controllers\traits\Singleton;
use core\base\exceptions\RouteException;
use core\base\settings\Settings;

class RouteController extends BaseController
{
    use Singleton;

    protected array $routes;

    protected string $addressStr;
    protected string $phpSelf;

    protected bool $hrUrl;
    protected string $routeType;

    private function __construct()
    {
        $this->prepareVars();

        $this->redirectWithoutLastSlash($this->addressStr);

        $path = $this->getBasePath();

        if ($path === PATH) {
            $this->routes = Settings::getSettingsByPropName('routes');
            if(!$this->routes) throw new RouteException('Сайт находится на техническом обслуживании!');

            $url = explode('/', ltrim($this->addressStr, PATH));

            $this->resolveRoute($url);
        } else {
            try {
                throw new \Exception('Некорректная директория сайта!');
            } catch (\Exception $e) {
                exit($e->getMessage());
            }
        }
    }

    private function prepareVars(): void
    {
        $this->addressStr = $_SERVER['REQUEST_URI'];
        $this->phpSelf = $_SERVER['PHP_SELF'];
    }

    private function redirectWithoutLastSlash(string $address): void
    {
        $lastSlashPosition = mb_strrpos($address, '/');

        if ($lastSlashPosition === mb_strlen($address) - 1 && $lastSlashPosition !== 0) {
            $this->redirect(rtrim($address, '/'), 301);
        }
    }

    private function getBasePath(): string
    {
        return substr($this->phpSelf, 0, strpos($this->phpSelf, 'index.php'));
    }

    private function resolveRoute($url): void
    {
        if($this->isAdminPart($url))  {
            array_shift($url);

            if ($this->isPluginPart($url)) {
                $this->pluginPart($url);
            } else {
                $this->adminPart();
            }
        } else {
            $this->userPart();
        }

        $this->createRoute($this->routeType, $url);

        $this->setParameters($url, $this->hrUrl);
    }

    private function userPart(): void
    {
        $this->hrUrl = $this->routes['user']['hrUrl'];
        $this->controller = $this->routes['user']['path'];
        $this->routeType = self::USER_ROUTE_TYPE;
    }

    private function adminPart(): void
    {
        $this->controller = $this->routes['admin']['path'];
        $this->hrUrl = $this->routes['admin']['hrUrl'];
        $this->routeType = self::ADMIN_ROUTE_TYPE;
    }

    private function pluginPart($url): void
    {
        $plugin = array_shift($url);

        $pluginSettings = $this->routes['settings']['path'] . ucfirst($plugin . 'Settings');

        if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . $pluginSettings . '.php')) {
            $pluginSettings = str_replace('/', '\\', $pluginSettings);
            $this->routes = $pluginSettings::getSettingsByPropName('routes');
        }

        $dir = $this->routes['plugins']['dir'] ? '/' . $this->routes['plugins']['dir'] . '/' : '/';
        $dir = str_replace('//', '/', $dir);

        $this->controller = $this->routes['plugins']['path'] . $plugin . $dir;
        $this->hrUrl = $this->routes['plugins']['hrUrl'];
        $this->routeType = self::PLUGINS_ROUTE_TYPE;
    }

    private function isAdminPart($url): bool
    {
        return !empty($url[0]) && $url[0] === $this->routes['admin']['alias'];
    }

    private function isPluginPart($url): bool
    {
        return !empty($url[0]) && is_dir($_SERVER['DOCUMENT_ROOT'] . PATH . $this->routes['plugins']['path'] . $url[0]);
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