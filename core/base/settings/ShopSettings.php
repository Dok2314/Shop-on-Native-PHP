<?php

namespace core\base\settings;

class ShopSettings
{
    private static $instance;
    private $baseSettings;

    private array $routes = [
        'admin' => [
            'name' => 'sudo',
        ],
        'vasya' => [
            'name' => 'vasya',
        ],
    ];

    private array $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content'],
    ];

    public static function getInstance()
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        self::$instance = new self;
        self::$instance->baseSettings = Settings::getInstance();
        $baseProperties = self::$instance->baseSettings->clueProperties(self::class);
        self::$instance->setProperties($baseProperties);

        return self::$instance;
    }

    public static function get($property)
    {
        if(property_exists(self::getInstance(), $property)) {
            return self::getInstance()->$property;
        }
    }

    protected function setProperties($properties): void
    {
        if($properties) {
            foreach ($properties as $propName => $propVal) {
                $this->$propName = $propVal;
            }
        }
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }
}