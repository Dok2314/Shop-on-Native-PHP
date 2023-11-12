<?php

namespace core\base\settings;

class ShopSettings
{
    private static $instance;
    private $baseSettings;

    private array $routes = [
        'plugins' => [
            'path' => 'core/plugins/',
            'hrUrl' => false,
            'dir' => 'controller',
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
        $properties = self::$instance->baseSettings->clueProperties(self::class);
        self::$instance->setProperties($properties);

        return self::$instance;
    }

    public static function getSettingsByPropName($propName)
    {
        if(property_exists(self::getInstance(), $propName)) {
            return self::getInstance()->$propName;
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