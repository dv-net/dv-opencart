<?php
/**
 * A simple autoloader for the DV.net Client library.
 * It registers an autoloader that maps the DvNet\DvNetClient namespace
 * to the src/ directory.
 */
spl_autoload_register(function ($class) {
    $prefix = 'DvNet\\DvNetClient\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
