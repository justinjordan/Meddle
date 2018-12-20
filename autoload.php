<?php

// Load Composer autoloader
$composerPath = __DIR__ . "/vendor/autoload.php";
if (file_exists($composerPath)) {
    require_once($composerPath);
}

// Init autoloader
spl_autoload_register(function ($class) {
    $parts = explode('\\', $class);
    array_shift($parts);
    $path = __DIR__ . '/src/' . implode('/', $parts) . '.php';

    if (file_exists($path)) {
        require_once($path);
    }
});
