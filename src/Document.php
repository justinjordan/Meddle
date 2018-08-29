<?php

namespace Meddle;

use Meddle\Exceptions\MeddleException;
use Meddle\ErrorHandling\ErrorMessagePool;
use Meddle\Services\Transpiler;
use Meddle\Services\DataBinder;
use Meddle\Services\Caching;

class Document
{
    /**
     * Render Meddle template.
     *
     * @param string $templatePath
     * @param array $data
     * @param array $options
     * @throws MeddleException
     */
    public static function render(string $templatePath, array $data = [], array $options = [])
    {
        /** Set default options */
        $options = array_merge([
            'cacheDir'  => null,
            'devMode'     => false,
        ], $options);

        /** Apply options */
        if (!empty($options['cacheDir'])) {
            Caching::setCacheDirectory($options['cacheDir']);
        }

        if (!file_exists($templatePath)) {
            throw new MeddleException("Template not found!");
        }

        $templateContents = file_get_contents($templatePath);
        $hash = md5($templateContents);

        $cachePath = Caching::getFilePath($hash, 'php');
        if ($options['devMode'] === true || empty($cachePath)) {
            $phpDocument = Transpiler::transpile($templateContents);
            $cachePath = Caching::saveFile($hash, 'php', $phpDocument);
        }

        $output = DataBinder::bind($cachePath, $data);

        return $output;
    }
}
