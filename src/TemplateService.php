<?php

namespace Sxule\Meddle;

class TemplateService
{
    public static function renderComponent(string $className, $propsSerialized)
    {
        $props = unserialize($propsSerialized);

        return (new $className())->render($props);
    }
}
