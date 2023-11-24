<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use ReflectionMethod;
use ReflectionException;
use ReflectionClass;

abstract class BaseController
{
    const ADMIN_ROUTE_TYPE = 'admin';
    const USER_ROUTE_TYPE = 'user';
    const PLUGINS_ROUTE_TYPE = 'plugins';

    protected $page;
    protected $errors;

    protected $controller;
    protected $inputMethod;
    protected $outputMethod;
    protected $parameters;

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

        $this->$inputMethod();

        $this->page = $this->$outputMethod();

        if ($this->errors) {
            $this->writeLog();
        }

        $this->getPage();
    }

    protected function render($path = '', $parameters = [])
    {
        extract($parameters);

        if (!$path) {
            $path = TEMPLATE . explode('controller', strtolower((new ReflectionClass($this))->getShortName()))[0];
        }

        ob_start();

        if (!@include_once $path . '.php') throw new RouteException('Отсутствует шаблон - ' . $path);

        return ob_get_clean();
    }

    protected function getPage()
    {
        exit($this->page);
    }
}