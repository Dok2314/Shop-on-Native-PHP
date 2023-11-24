<?php

namespace core\base\traits;

use DateTime;

trait BaseMethods
{
    protected array $styles;
    protected array $scripts;

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

    protected function clearStr(array|string $str): array|string
    {
        if (is_array($str)) {
            foreach ($str as $key => $value) {
                $str[$key] = trim(strip_tags($value));
            }

            return $str;
        } else {
            return trim(strip_tags($str));
        }
    }

    protected function clearNum($num): float|int
    {
        return $num * 1;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect(bool|string $http = false, bool|int $code = false): void
    {
        if ($code) {
            $codes = ['301' => 'HTTP/1.1 301 Move Permanently'];

            if (isset($codes[$code])) {
                header($codes[$code]);
            }
        }

        if ($http) {
            $redirect = $http;
        } else {
            $redirect = $_SERVER['HTTP_REFERER'] ?? PATH;
        }

        header("Location: $redirect");
        exit;
    }

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault'): void
    {
        $dateTime = new DateTime();

        $str = $event . ' : ' . $dateTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";

        file_put_contents(LOG_PATH . $file, $str, FILE_APPEND);
    }
}