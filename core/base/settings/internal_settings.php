<?php

use core\base\exceptions\RouteException;

defined('DOK_ACCESS') or die('ACCESS DENIED');

const TEMPLATE = 'templates/default/';
const ADMIN_TEMPLATES = 'core/admin/views/';

const COOKIE_VERSION = '1.0.0';
const CRYPT_KEY = '';
const COOKIE_TIME = 60;
const BLOCK_TIME = 3;

const QTY = 8;
const QTY_LINKS = 3;

const ADMIN_CSS_JS = [
    'styles' => [],
    'scripts' => [],
];

const USER_CSS_JS = [
    'styles' => [],
    'scripts' => [],
];

spl_autoload_register(function ($className)
{
    $className = str_replace('\\', '/', $className);

    if(!@include_once $className . '.php') {
        throw new RouteException('Не верное имя файла для подключения - ' . $className);
    }
});