<?php

namespace Sxule\Meddle\ErrorHandling;

class ErrorMessagePool
{
    public static $language = 'en';

    public static function get(string $key, $arg = null)
    {
        $errorFilePath = __DIR__ . '/errors.json';
        $errorsContents = file_get_contents($errorFilePath);
        $errors = json_decode($errorsContents, true);
        $lang = self::$language;

        if (!isset($errors[$key][$lang])) {
            return "An error occurred!";
        }

        $error = sprintf($errors[$key], $arg);

        return $error;
    }
    
    public static function setLanguage(string $language)
    {
        self::$language = $language;
    }
}