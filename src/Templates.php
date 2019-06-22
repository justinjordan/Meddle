<?php

namespace Sxule\Meddle;

use Sxule\Meddle\Transpiler;

class Templates
{
    public static function renderComponent(string $className, $propsSerialized)
    {
        $props = unserialize(base64_decode($propsSerialized));

        $componentMarkup = (new $className())->render($props);

        return (new Transpiler())->transpile($componentMarkup);
    }
}
