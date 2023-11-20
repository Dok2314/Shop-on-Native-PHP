<?php

namespace core\base\controllers;

use core\base\exceptions\RouteException;
use ReflectionMethod;
use ReflectionException;

abstract class BaseController
{
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
            throw new RouteException($e);
        }
    }

    public function request($args)
    {
        dd($args);
    }
}