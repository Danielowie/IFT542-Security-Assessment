# Task 2: Authentication Controls Implementation

**Student:** 2021-1-84189CF

## Control 1: Argon2id Password Hashing

**File:** `src/Auth.php` (lines 76–83)

```php
public static function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64 MB
        'time_cost'   => 4,      // 4 iterations
        'threads'     => 1,
    ]);
}
```

**Implementation:** Passwords hashed with Argon2id (GPU/ASIC resistant). No plaintext storage.

## Control 2: Rate Limiting (5 attempts / 15 minutes)

**File:** `src/Auth.php` (lines 93–103)

```php
private const MAX_ATTEMPTS_PER_WINDOW = 5;
private const WINDOW_MINUTES = 15;

private static function isRateLimited(string $identifier, string $ip): bool
{
    $stmt = Database::run(
        'SELECT COUNT(*) AS n FROM login_attempts
         WHERE (identifier = ? OR ip_address = ?)
           AND success = 0
           AND attempted_at > (NOW() - INTERVAL ? MINUTE)',
        [$identifier, $ip, self::WINDOW_MINUTES]
    );
    return (int) $stmt->fetch()['n'] >= self::MAX_ATTEMPTS_PER_WINDOW;
}
```

**Table:** `login_attempts` — append-only audit log, never stores passwords.

**Effect:** Brute force prevention. Applies to both identifier and IP (dual trigger).

## Control 3: Temporary Account Lockout (15 minutes)

**File:** `src/Auth.php` (lines 113–138)

```php
private const LOCKOUT_MINUTES = 15;

private static function registerFailureAndMaybeLock(string $identifier, string $ip): void
{
    // ... count failures over window ...
    if ($failures >= self::MAX_ATTEMPTS_PER_WINDOW) {
        Database::run(
            'INSERT INTO account_lockouts (user_id, locked_until, reason)
             VALUES (?, NOW() + INTERVAL ? MINUTE, "too_many_failed_attempts")
             ON DUPLICATE KEY UPDATE locked_until = NOW() + INTERVAL ? MINUTE',
            [$user['id'], self::LOCKOUT_MINUTES, self::LOCKOUT_MINUTES]
        );
    }
}
```

**Table:** `account_lockouts` — temporary lockout state, auto-expires.

**Effect:** Account locked 15 minutes after 5 failed attempts. Clears on successful login.

## Control 4: Session ID Regeneration

**File:** `src/Session.php`

```php
public static function regenerate(): void
{
    session_regenerate_id(true);
}
```

**Called in:** `src/Auth.php` line 68 (after successful authentication)

**Effect:** Prevents session fixation attacks. Old session ID invalidated.

## Control 5: Timing Attack Defence (Constant-Time Response)

**File:** `src/Auth.php` (lines 50–54)

```php
$hashToCheck = $user['password_hash'] ?? self::dummyHash();
$passwordOk  = password_verify($password, $hashToCheck);
```

**Effect:** `password_verify()` always runs (against dummy hash if user not found). Response time consistent, preventing user enumeration via timing side-channel.

## Control 6: Generic Login Error Messages

**File:** `src/Auth.php` line 24

```php
public const GENERIC_ERROR = 'Invalid credentials, or this account is temporarily locked. Please try again later.';
```

**Usage:** Same error message for rate limiting, lockout, non-existent user, wrong password, or inactive account.

**Effect:** Prevents user enumeration. Attacker cannot determine which part of auth failed.

## Control 7: Secure Session Cookies

**File:** `src/Session.php` (lines 21–30)

```php
ini_set('session.cookie_httponly', '1');    // No JavaScript access
ini_set('session.cookie_samesite', 'Lax');  // CSRF defence
// Production: ini_set('session.cookie_secure', '1');  // HTTPS only
```

**Effect:** HttpOnly prevents XSS theft. SameSite prevents CSRF. Secure (prod) enforces HTTPS.

## Control 8: Secure Password Reset Tokens

**File:** `public/password_reset_confirm.php`

- Single-use tokens (marked `used_at` after consumption)
- Time-limited (1-hour expiry)
- Hashed in database (raw token never stored, unrecoverable if DB leaks)
- Cryptographically random (`random_bytes(32)`)

**Table:** `password_resets` — single-use, time-limited, hashed tokens only.

## Summary

| Control | Type | Status |
|---------|------|--------|
| Argon2id hashing | Auth | ✅ Implemented |
| Rate limiting | Auth | ✅ Implemented |
| Account lockout | Auth | ✅ Implemented (2+ bonus) |
| Session regeneration | Session | ✅ Implemented (2+ bonus) |
| Timing attack defence | Auth | ✅ Implemented (bonus) |
| Generic errors | Auth | ✅ Implemented |
| Secure cookies | Session | ✅ Implemented |
| Password reset tokens | Auth | ✅ Implemented (bonus) |

**Result:** 8 authentication security controls implemented (requirement: 1 + at least 2 additional).
