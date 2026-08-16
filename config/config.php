<?php
/**
 * Central configuration loader.
 *
 * Security-misconfiguration control:
 *  - No secrets are hardcoded here; everything comes from .env.
 *  - APP_DEBUG defaults to false. If .env is missing entirely we
 *    fail closed (debug off, generic error) rather than fail open.
 */

declare(strict_types=1);

if (!function_exists('env_load')) {
    function env_load(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }
        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $values[trim($key)] = trim($value);
        }
        return $values;
    }
}

if (!function_exists('cfg')) {
    function cfg(array $env, string $key, $default = null)
    {
        return $env[$key] ?? $default;
    }
}

$envFile = __DIR__ . '/../.env';
$env = array_merge($_ENV, env_load($envFile));

return [
    'app_env'   => cfg($env, 'APP_ENV', 'production'),
    // Fail closed: only "true" (exact match) turns debug on.
    'app_debug' => cfg($env, 'APP_DEBUG', 'false') === 'true',

    'db' => [
        'host' => cfg($env, 'DB_HOST', '127.0.0.1'),
        'port' => cfg($env, 'DB_PORT', '3306'),
        'name' => cfg($env, 'DB_NAME', 'student_registration'),
        'user' => cfg($env, 'DB_USER', ''),
        'pass' => cfg($env, 'DB_PASS', ''),
    ],

    'session' => [
        'name'     => cfg($env, 'SESSION_NAME', 'srapp_session'),
        'lifetime' => (int) cfg($env, 'SESSION_LIFETIME', '1800'),
    ],

    'url_preview_allowlist' => array_filter(array_map(
        'trim',
        explode(',', cfg($env, 'URL_PREVIEW_ALLOWLIST', ''))
    )),
];
