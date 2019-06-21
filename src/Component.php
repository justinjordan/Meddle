<?php

namespace Sxule\Meddle;

use DOMNode;

abstract class Component
{
    protected $document;
    protected $props;

    abstract public function render();

    public function __construct($props)
    {
        $this->props = json_decode($props, true);
    }
}
