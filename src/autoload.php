<?php

spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    array_shift($parts);
    $path = __DIR__.'/'.implode('/', $parts).'.php';
    if (file_exists($path)) {
        require_once($path);
    }
});
