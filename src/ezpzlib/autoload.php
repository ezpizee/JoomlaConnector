<?php

class EzpzLibAutoloader
{
    private static $delimiter = "\\";
    private static $packages = [];
    private static $objects = [];

    private function __construct()
    {
    }

    public static function appendPackage(array $packages, bool $invokeExecute = false)
    {
        if (!empty($packages)) {
            foreach ($packages as $nameSpacePfx => $dir) {
                if (!isset(self::$packages[$nameSpacePfx])) {
                    self::$packages[$nameSpacePfx] = $dir;
                }
            }
        }
        if ($invokeExecute === true) {
            self::exec();
        }
    }

    public static final function exec()
    {
        spl_autoload_register(function ($class) {
            $parts = explode(self::$delimiter, trim($class, self::$delimiter));
            $file = "";
            $part = "";
            if (isset($parts[2]) && isset(self::$packages[$parts[0].self::$delimiter.$parts[1].self::$delimiter.$parts[2]])) {
                $part = $parts[0].self::$delimiter.$parts[1].self::$delimiter.$parts[2];
                $file = self::$packages[$part] . DS . str_replace(self::$delimiter, DS, $class) . '.php';
            }
            else if (isset($parts[1]) && isset(self::$packages[$parts[0].self::$delimiter.$parts[1]])) {
                $part = $parts[0].self::$delimiter.$parts[1];
                $file = self::$packages[$part] . DS . str_replace(self::$delimiter, DS, $class) . '.php';
            }
            else if (isset(self::$packages[$parts[0]])) {
                $part = $parts[0];
                $file = self::$packages[$part] . DS . str_replace(self::$delimiter, DS, $class) . '.php';
            }
            $passed = isset(self::$objects[$part]);
            if ($passed === false && file_exists($file)) {
                self::$objects[] = $class;
                include $file;
                $passed = true;
            }
            return $passed;
        });
    }
}

EzpzLibAutoloader::appendPackage([
    'Ezpizee\\ConnectorUtils' => __DIR__.DIRECTORY_SEPARATOR.'connector-utils'.DIRECTORY_SEPARATOR.'src',
    'Ezpizee\\ContextProcessor' => __DIR__.DIRECTORY_SEPARATOR.'contextprocessor'.DIRECTORY_SEPARATOR.'src',
    'Handlebars' => __DIR__.DIRECTORY_SEPARATOR.'handlebars'.DIRECTORY_SEPARATOR.'src',
    'Ezpizee\\MicroservicesClient' => __DIR__.DIRECTORY_SEPARATOR.'microservices-client'.DIRECTORY_SEPARATOR.'src',
    'Ezpizee\\Utils' => __DIR__.DIRECTORY_SEPARATOR.'microservices-utils'.DIRECTORY_SEPARATOR.'src',
    'Ezpizee\\HandlebarsHelpers' => __DIR__.DIRECTORY_SEPARATOR.'php-hbs-helperss'.DIRECTORY_SEPARATOR.'src',
    'Unirest' => __DIR__.DIRECTORY_SEPARATOR.'unirest-php'.DIRECTORY_SEPARATOR.'src'
], true);