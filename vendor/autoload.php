<?php

spl_autoload_register(function (string $class): void {
    // Antre\Tests\Foo  → tests/Foo.php
    if (str_starts_with($class, 'Antre\\Tests\\')) {
        $rel  = str_replace('Antre\\Tests\\', '', $class);
        $file = __DIR__ . '/../tests/' . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) { require_once $file; }
        return;
    }
    // Antre\Foo  → src/Foo.php
    if (str_starts_with($class, 'Antre\\')) {
        $rel  = str_replace('Antre\\', '', $class);
        $file = __DIR__ . '/../src/' . str_replace('\\', '/', $rel) . '.php';
        if (file_exists($file)) { require_once $file; }
    }
});
