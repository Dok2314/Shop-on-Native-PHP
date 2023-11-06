<?php

namespace core\base\settings;

class Settings
{
    private static $instance;

    private array $routes = [
        'admin' => [
            'name' => 'admin',
            'path' => 'core/admin/controllers/',
            'hrUrl' => false,
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
        ],
        'user' => [
            'path' => 'core/user/controllers/',
            'hrUrl' => true,
            'routes' => [

            ],
        ],
        'default' => [
            'controller' => 'IndexController',
            'inputMethod' => 'inputData',
            'outputMethod' => 'outputData',
        ],
    ];

    private $testProp = [
        'test' => [
            'key' => 'val'
        ]
    ];

    private array $templateArr = [
        'text' => ['name', 'phone', 'address'],
        'textarea' => ['content', 'keywords'],
    ];

    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        return self::$instance = new self;
    }

    public static function get($property)
    {
        return self::getInstance()->$property;
    }

    public function clueProperties($childSettingsClassName)
    {
        $baseProperties = [];

        foreach ($this as $basePropName => $basePropVal) {
            $childProps = $childSettingsClassName::get($basePropName);

            if(is_array($childProps) && is_array($basePropVal)) {
                $baseProperties[$basePropName] = $this->arrayMergeRecursive($basePropVal, $childProps);
            }

            if(!$childProps) $baseProperties[$basePropName] = $basePropVal;
        }

        return $baseProperties;
    }

    protected function arrayMergeRecursive(...$arrays)
    {
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if(isset($base[$key]) && is_array($value) && is_array($base[$key])) {
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                } else {
                    if(is_int($key)) {
                        if(!in_array($value, $base)) $base[] = $value;
                        continue;
                    }

                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}