<?php

namespace Sxule;

use Sxule\Meddle\Transpiler;
use Sxule\Meddle\DataBinder;
use Sxule\Meddle\Caching;
use Sxule\Meddle\Exceptions\MeddleException;
use Sxule\Meddle\ErrorHandling\ErrorMessagePool;

class Meddle
{
    protected $options;

    public function __construct(array $options = []) {
        // Set default options
        $this->options = array_merge([
            'cacheDir'      => null,
            'devMode'       => false,
            'data'          => [],
            'components'    => [],
        ], $options);

        $this->validateOptions($this->options);
    }

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
        // Override default options with argument
        $options = array_merge($this->options, $options);

        // Apply options
        $this->validateOptions($options);
        if (!empty($options['cacheDir'])) {
            Caching::setCacheDirectory($options['cacheDir']);
        }
        
        // Load template or use $templatePath as content
        $templateContents = $templatePath;
        $hashInput = $templateContents;
        if (file_exists($templatePath)) {
            $templateContents = file_get_contents($templatePath);
            $hashInput = $templatePath.filemtime($templatePath);
        }

        // Cache dynamic document
        $hash = md5($hashInput);
        $cachePath = Caching::getFilePath($hash, 'php');
        if ($options['devMode'] === true || empty($cachePath)) {
            $phpDocument = (new Transpiler())->
                transpile($templateContents, $this->options['components']);
            $cachePath = Caching::saveFile($hash, 'php', $phpDocument);
        }

        // Add options data to scope
        foreach ($options['data'] as $name => $variable) {
            $data[$name] = $variable;
        }

        // Bind data to template
        $output = (new DataBinder())->bind($cachePath, $data);

        return $output;
    }

    protected function validateOptions($options)
    {
        // data must be array or object
        if (!in_array(gettype($options['data']), ['array', 'object'])) {
            throw new MeddleException(ErrorMessagePool::get('optionsBadData'));
        }
    }
}
