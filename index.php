<?php

use core\base\controllers\RouteController;
use core\base\exceptions\RouteException;

require_once 'ini_configuration.php';

const DOK_ACCESS = true;

header('Content-Type:text/html;charset=utf-8');
session_start();

require_once "config.php";
require_once "core/base/settings/internal_settings.php";
require_once 'libraries/functions.php';
require_once 'core/bootstrap/autoload.php';

try {
    RouteController::getInstance()->route();
} catch (RouteException $e) {
    exit($e->getMessage());
}