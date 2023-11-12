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

    public static function getSettingsByPropName($propName)
    {
        if(property_exists(self::getInstance(), $propName)) {
            return self::getInstance()->$propName;
        }
    }

    public function clueProperties($childClassName): array
    {
        $props = [];

        foreach ($this as $basePropName => $basePropVal) {
            $childPropVal = $childClassName::getSettingsByPropName($basePropName);

            if(is_array($basePropVal) && is_array($childPropVal)) {
                $props[$basePropName] = $this->arrayMergeRecursive($basePropVal, $childPropVal);
            }

            if(!$childPropVal) $props[$basePropName] = $basePropVal;
        }

        return $props;
    }

    protected function arrayMergeRecursive(...$props)
    {
        $baseProps = array_shift($props);

        foreach ($props as $childProp) {
            foreach ($childProp as $childPropKey => $childPropVal) {
                if(isset($baseProps[$childPropKey]) && is_array($childPropVal) && is_array($baseProps[$childPropKey])) {
                    $baseProps[$childPropKey] = $this->arrayMergeRecursive($baseProps[$childPropKey], $childPropVal);
                } else {
                    if(is_int($childPropKey)) {
                        if(!in_array($childPropVal, $baseProps)) $baseProps[] = $childPropVal;
                        continue;
                    }

                    $baseProps[$childPropKey] = $childPropVal;
                }
            }
        }

        return $baseProps;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}