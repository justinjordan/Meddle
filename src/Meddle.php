<?php

namespace Sxule;

use Sxule\Meddle\Transpiler;
use Sxule\Meddle\DataBinder;
use Sxule\Meddle\Caching;
use Sxule\Meddle\Exceptions\MeddleException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;

class Meddle
{
    /**
     * Render Meddle template.
     *
     * @param string $templatePath
     * @param array $data
     * @param array $options
     *
     * @throws MeddleException
     *
     * @return string   Returns rendered HTML document
     */
    public function render(string $templatePath, array $data = [], array $options = []): string
    {
        // Set default options
        $options = array_merge([
            'cacheDir' => null,
            'devMode'  => false,
        ], $options);

        // Apply options
        if (!empty($options['cacheDir'])) {
            Caching::setCacheDirectory($options['cacheDir']);
        }
        
        // Load template
        $templateContents = $templatePath;
        if (file_exists($templatePath)) {
            $templateContents = file_get_contents($templatePath);
        }

        // Cache dynamic document
        $hash = md5($templateContents);
        $cachePath = Caching::getFilePath($hash, 'php');
        if ($options['devMode'] === true || empty($cachePath)) {
            $phpDocument = (new Transpiler())->transpile($templateContents);
            $cachePath = Caching::saveFile($hash, 'php', $phpDocument);
        }

        // Bind data to template
        $output = (new DataBinder())->bind($cachePath, $data);

        return $output;
    }
}
