<?php
declare(strict_types=1);

/**
 * Output-encoding helper (XSS defence, Task 3 activity 20) and
 * security response headers (Task 3 activity 23: security
 * misconfiguration hardening).
 */
final class SecurityHeaders
{
    public static function apply(bool $debug): void
    {
        // A restrictive CSP: no inline scripts, no third-party
        // script hosts, images/self only. Adjust src lists if you
        // add a CSS/JS CDN.
        header("Content-Security-Policy: default-src 'self'; "
            . "script-src 'self'; style-src 'self'; img-src 'self' data:; "
            . "object-src 'none'; base-uri 'self'; frame-ancestors 'none'");
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        if (!$debug) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

/**
 * Global helper for contextual output encoding in views.
 * Every place a user-controlled value is echoed into HTML MUST go
 * through e(). This is the single fix for the stored-XSS finding on
 * the profile "bio" field described in docs/stride_worksheet.md.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
