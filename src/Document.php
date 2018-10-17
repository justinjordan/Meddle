<?php

namespace Sxule\Meddle;

use Sxule\Meddle\Transpiler;
use Sxule\Meddle\DataBinder;
use Sxule\Meddle\Caching;
use Sxule\Meddle\Exceptions\MeddleException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;

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
    public function render(string $templatePath, array $data = [], array $options = [])
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
        
        $templateContents = $templatePath;
        if (file_exists($templatePath)) {
            $templateContents = file_get_contents($templatePath);
        }

        $hash = md5($templateContents);

        $cachePath = Caching::getFilePath($hash, 'php');
        if ($options['devMode'] === true || empty($cachePath)) {
            $transpiler = new Transpiler();
            $phpDocument = $transpiler->transpile($templateContents);
            $cachePath = Caching::saveFile($hash, 'php', $phpDocument);
        }

        $output = (new DataBinder())->bind($cachePath, $data);

        return $output;
    }
}
