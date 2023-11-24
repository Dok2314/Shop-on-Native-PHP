<?php

namespace core\base\settings;

use core\base\controllers\traits\Singleton;

class ShopSettings
{
    use Singleton;

    private $baseSettings;

    private array $routes = [
        'plugins' => [
            'dir' => false,
            'routes' => [
            ],
        ],
    ];

    private array $templateArr = [
        'text' => ['price', 'short'],
        'textarea' => ['goods_content'],
    ];

    private static function getInstance()
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
        $obj = self::getInstance();
        if(property_exists($obj, $propName)) {
            return $obj->$propName;
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
}