<?php

namespace Meddle\Services;

use Meddle\Exceptions\MeddleException;
use Meddle\ErrorHandling\ErrorMessagePool;

class DataBinder
{
    public static function bind(string $phpDocPath, array $data = null)
    {
        if (!file_exists($phpDocPath)) {
            throw new MeddleException(ErrorMessagePool::get('dataBinderPhpFileNotFound'));
        }

        ob_start();
        extract($data);
        include($phpDocPath);
        return ob_get_clean();
    }
}