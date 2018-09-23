<?php

namespace Sxule\Meddle;

class TemplateScope
{
    public $time;

    public function __construct()
    {
        $this->time = time();
    }

    public function toUpper(string $input)
    {
        return strtoupper($input);
    }

    public function toLower(string $input)
    {
        return strtolower($input);
    }
}
