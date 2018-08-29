<?php

namespace Meddle\Services;

use Meddle\Exceptions\MeddleException;
use Meddle\ErrorHandling\ErrorMessagePool;

class Caching
{
    /**
     * Saves file to cache directory.
     *
     * @param string $hash      Hash used to identify file
     * @param string $type      PHP or HTML
     * @param string $content   Content to be saved to file
     * @throws MeddleException
     * @return boolean Returns true on success, or false
     */
    public static function saveFile(string $hash, string $type, string $content)
    {
        $cacheDir = dirname(__DIR__, 2).'/cache';

        $bytes = false;
        $type = strtolower($type);
        $path = '';
        switch ($type) {
            case 'php':
            case 'html':
                $dir = "$cacheDir/$type";
                $path = "$dir/$hash.$type";
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $bytes = file_put_contents($path, $content);
                break;
        }

        if (empty($path) || $bytes === false) {
            throw new MeddleException(ErrorMessagePool::get('cachingSaveFileError'));
        }

        return $path;
    }

    /**
     * Gets cached file path
     *
     * @param string $hash  Hash used to identify file
     * @param string $type  PHP or HTML
     * @return void
     */
    public static function getFilePath(string $hash, string $type)
    {
        $type = strtolower($type);
        $cacheDir = dirname(__DIR__, 2).'/cache';
        $path = "$cacheDir/$type/$hash.$type";

        if (!file_exists($path)) {
            return null;
        }

        return $path;
    }
}