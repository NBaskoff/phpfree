<?php

namespace Core;

class Autoloader {
    public static function register() {
        spl_autoload_register(function ($class) {
            $file = Path::root(str_replace('\\', '/', $class) . '.php');
            if (file_exists($file)) {
                require_once $file;
            }
        });
    }
}