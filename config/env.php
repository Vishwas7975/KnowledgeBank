<?php
// config/env.php — tiny dependency-free .env loader
//
// On Hostinger (or any host where you set real environment variables via
// the control panel), this file does nothing extra — getenv() already sees
// those values and the loop below simply finds no .env file to read.
//
// For local development, copy .env.example to .env in the project root and
// fill in your own values; this loader makes them visible to getenv().

$envPath = __DIR__ . '/../.env';

if (is_file($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Strip matching surrounding quotes, if present.
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && $value[-1] === '"') ||
            ($value[0] === "'" && $value[-1] === "'")
        )) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
        }
    }
}
