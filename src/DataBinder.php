<?php

namespace Sxule\Meddle;

use Sxule\Meddle\Exceptions\MeddleException;
use Sxule\Meddle\Exceptions\SyntaxException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;

class DataBinder
{
    public function bind(string $phpDocPath, array $data = [])
    {
        if (!file_exists($phpDocPath)) {
            throw new MeddleException(ErrorMessagePool::get('dataBinderPhpFileNotFound'));
        }

        self::addFunctions($data);

        try {
            ob_start();
            extract($data);
            @include($phpDocPath);
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            throw new SyntaxException("Syntax error in template.");
        }

        return $output;
    }

    /**
     * Add standard functions to be used in templates.
     *
     * @param array $data   Reference to template data
     * @return void
     */
    private static function addFunctions(array &$data)
    {
        $data['toUpper'] = function ($input) {
            return strtoupper($input);
        };

        $data['toLower'] = function ($input) {
            return strtolower($input);
        };
    }
}