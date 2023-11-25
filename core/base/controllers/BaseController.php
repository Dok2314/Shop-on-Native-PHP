<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use core\base\settings\Settings;
use core\base\traits\BaseMethods;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

abstract class BaseController
{
    use BaseMethods;

    const ADMIN_ROUTE_TYPE = 'admin';
    const USER_ROUTE_TYPE = 'user';
    const PLUGINS_ROUTE_TYPE = 'plugins';

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

    protected array $styles;
    protected array $scripts;

    /**
     * @throws RouteException
     */
    public function route(): void
    {
        $controller = str_replace('/', '\\', $this->controller);

        try {
            $object = new ReflectionMethod($controller, 'request');

            $args = [
                'parameters' => $this->parameters,
                'inputMethod' => $this->inputMethod,
                'outputMethod' => $this->outputMethod,
            ];

            // call the request method in our controller
            $object->invoke(new $controller, $args);
        } catch (ReflectionException $e) {
            throw new RouteException($e->getMessage());
        }
    }

    public function request(array $args): void
    {
        $this->parameters = $args['parameters'];

        $inputMethod = $args['inputMethod'];
        $outputMethod = $args['outputMethod'];

        $data = $this->$inputMethod();

        if (method_exists($this, $outputMethod)) {
            $page = $this->$outputMethod($data);
            if ($page) {
                $this->page = $this->$outputMethod($data);
            }
        } elseif ($data) {
            $this->page = $data;
        }

        if ($this->errors) {
            $this->writeLog($this->errors);
        }

        $this->getPage();
    }

    protected function render($path = '', array $parameters = []): bool|string
    {
        extract($parameters);

        if (!$path) {
            $path = $this->getPathFromControllerNameSpace();
        }

        ob_start();

        if (!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - ' . $path);

        return ob_get_clean();
    }

    protected function getPage(): void
    {
        if (is_array($this->page)) {
            foreach ($this->page as $block) {
                echo $block;
            }
        } else {
            echo $this->page;
        }

        exit();
    }

    protected function getPathFromControllerNameSpace(): string
    {
        $class = new ReflectionClass($this);
        $nameSpace = str_replace('\\', '/', $class->getNamespaceName() . '\\');

        $routes = Settings::getSettingsByPropName('routes');

        $template = ($nameSpace === $routes['user']['path']) ? TEMPLATE : ADMIN_TEMPLATE;

        return $template . explode('controller', strtolower($class->getShortName()))[0];
    }

    protected function initJsScriptsAndCssStyles(bool $admin = false): void
    {
        if (!$admin) {
            if (isset(USER_CSS_JS['styles'])) {
                foreach (USER_CSS_JS['styles'] as $style) {
                    $this->styles[] = PATH . TEMPLATE . trim($style, '/');
                }
            }

            if (isset(USER_CSS_JS['scripts'])) {
                foreach (USER_CSS_JS['scripts'] as $script) {
                    $this->scripts[] = PATH . TEMPLATE . trim($script, '/');
                }
            }
        } else {
            if (isset(ADMIN_CSS_JS['styles'])) {
                foreach (USER_CSS_JS['styles'] as $style) {
                    $this->styles[] = PATH . TEMPLATE . trim($style, '/');
                }
            }

            if (isset(ADMIN_CSS_JS['scripts'])) {
                foreach (USER_CSS_JS['scripts'] as $script) {
                    $this->scripts[] = PATH . TEMPLATE . trim($script, '/');
                }
            }
        }
    }
}