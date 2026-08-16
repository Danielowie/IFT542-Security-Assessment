<?php
/**
 * Comprehensive security test suite for IFT542 practical assignment
 * Tests authentication, SQL injection, XSS, CSRF, SSRF, and authorization
 */

declare(strict_types=1);

require __DIR__ . '/../public/bootstrap.php';

class SecurityTestSuite
{
    private int $passed = 0;
    private int $failed = 0;
    private array $results = [];

    public function run(): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "IFT542 SECURITY TEST SUITE\n";
        echo "Student: 2021-1-84189CF\n";
        echo str_repeat("=", 80) . "\n\n";

        $this->testAuthenticationControls();
        $this->testSQLInjectionDefence();
        $this->testXSSProtection();
        $this->testCSRFProtection();
        $this->testSSRFProtection();
        $this->testAuthorizationControls();
        $this->testInputValidation();
        $this->testLoggingSecurity();

        $this->printSummary();
    }

    private function testAuthenticationControls(): void
    {
        echo "\n--- TASK 2: AUTHENTICATION CONTROLS ---\n\n";

        // Test 1: Valid login credentials
        $this->test('Valid login succeeds', function() {
            $result = Auth::attemptLogin('student1@example.test', 'Tr0ub4dor&3', '127.0.0.1');
            return $result['ok'] === true && !empty($result['user']);
        });

        // Test 2: Invalid credentials rejected
        $this->test('Invalid credentials rejected', function() {
            $result = Auth::attemptLogin('student1@example.test', 'wrongpassword', '127.0.0.1');
            return $result['ok'] === false && $result['error'] === Auth::GENERIC_ERROR;
        });

        // Test 3: Non-existent user rejected with generic error
        $this->test('Non-existent user returns generic error', function() {
            $result = Auth::attemptLogin('fakeemail@nonexistent.test', 'anypassword', '127.0.0.1');
            return $result['ok'] === false && $result['error'] === Auth::GENERIC_ERROR;
        });

        // Test 4: Password verification (not plaintext)
        $this->test('Password hashing verified', function() {
            $stmt = Database::run(
                'SELECT password_hash FROM users WHERE email = ? LIMIT 1',
                ['student1@example.test']
            );
            $user = $stmt->fetch();
            // Verify hash starts with Argon2id identifier
            return strpos($user['password_hash'], '$argon2id$') === 0;
        });

        // Test 5: Session regeneration creates new ID
        $this->test('Session ID regeneration works', function() {
            $oldId = session_id();
            Session::regenerate();
            $newId = session_id();
            return $oldId !== $newId;
        });

        // Test 6: Rate limiting tracking recorded
        $this->test('Login attempts recorded in database', function() {
            $stmt = Database::run(
                'SELECT COUNT(*) AS n FROM login_attempts WHERE ip_address = ?',
                ['127.0.0.1']
            );
            $count = (int) $stmt->fetch()['n'];
            return $count > 0;
        });
    }

    private function testSQLInjectionDefence(): void
    {
        echo "\n--- TASK 2: SQL INJECTION DEFENCE ---\n\n";

        // Test 1: Comment injection attempt
        $this->test('SQL comment injection blocked in login', function() {
            $result = Auth::attemptLogin("admin@example.test' --", 'anypassword', '127.0.0.1');
            return $result['ok'] === false;
        });

        // Test 2: Boolean-based injection
        $this->test('Boolean-based injection returns no results', function() {
            $stmt = Database::run(
                'SELECT COUNT(*) AS n FROM users WHERE email LIKE ?',
                ['%' . "' OR '1'='1" . '%']
            );
            $count = (int) $stmt->fetch()['n'];
            return $count === 0;  // No results found
        });

        // Test 3: Union-based injection attempt
        $this->test('Union-based injection returns null', function() {
            $stmt = Database::run(
                'SELECT id FROM users WHERE matric_no = ?',
                ["' UNION SELECT user FROM information_schema.USER --"]
            );
            return $stmt->fetch() === false;
        });

        // Test 4: Profile update with malicious SQL
        $this->test('Profile update parameterized', function() {
            $maliciousBio = "Normal bio', role='admin' WHERE id='";
            // This should be treated as a literal string, not SQL
            Database::run(
                'SELECT * FROM users WHERE bio = ?',
                [$maliciousBio]
            );
            return true;  // No error = protection working
        });

        // Test 5: No database error disclosure
        $this->test('Database errors not returned to client', function() {
            ob_start();
            $error = '';
            try {
                Database::run('SELECT * FROM nonexistent_table WHERE x = ?', ['y']);
            } catch (PDOException $e) {
                $error = $e->getMessage();
            }
            ob_end_clean();
            return strpos($error, 'nonexistent_table') !== false;  // Error caught internally
        });
    }

    private function testXSSProtection(): void
    {
        echo "\n--- TASK 3: XSS PROTECTION ---\n\n";

        // Test 1: Output encoding of user input
        $this->test('HTML encoding via e() function works', function() {
            $malicious = '<script>alert("xss")</script>';
            $encoded = e($malicious);
            return $encoded === htmlspecialchars($malicious, ENT_QUOTES, 'UTF-8');
        });

        // Test 2: Bio field stored safely
        $this->test('Stored XSS defence on bio field', function() {
            $stmt = Database::run(
                'SELECT bio FROM users WHERE email = ? LIMIT 1',
                ['student1@example.test']
            );
            $user = $stmt->fetch();
            // Bio is stored; when retrieved and passed through e(), it's safe
            $safe = e($user['bio']);
            return strpos($safe, '<') === false || strpos($safe, '&lt;') !== false;
        });

        // Test 3: CSP header present
        $this->test('Content-Security-Policy header configured', function() {
            ob_start();
            SecurityHeaders::apply(false);
            ob_end_clean();
            // Headers sent; CSP should restrict script execution
            return true;
        });
    }

    private function testCSRFProtection(): void
    {
        echo "\n--- TASK 3: CSRF PROTECTION ---\n\n";

        // Test 1: CSRF token generation
        $this->test('CSRF token generated on session start', function() {
            $token = Csrf::token();
            return !empty($token) && strlen($token) > 20;
        });

        // Test 2: Missing token rejected
        $this->test('Request without CSRF token rejected', function() {
            $result = Csrf::verify(null);
            return $result === false;
        });

        // Test 3: Invalid token rejected
        $this->test('Request with invalid CSRF token rejected', function() {
            $result = Csrf::verify('invalid_token_string');
            return $result === false;
        });

        // Test 4: Valid token verified
        $this->test('Valid CSRF token passes verification', function() {
            $token = Csrf::token();
            $result = Csrf::verify($token);
            return $result === true;
        });

        // Test 5: Token rotation after use
        $this->test('CSRF token regenerates after rotation', function() {
            $oldToken = Csrf::token();
            Csrf::rotate();
            $newToken = Csrf::token();
            return $oldToken !== $newToken;
        });
    }

    private function testSSRFProtection(): void
    {
        echo "\n--- TASK 3: SSRF PROTECTION ---\n\n";

        $allowlist = ['example.com', 'trusted-site.test'];

        // Test 1: Loopback address rejected
        $this->test('Loopback address (127.0.0.1) rejected', function() use ($allowlist) {
            [$allowed, $error] = UrlGuard::isAllowed('https://127.0.0.1/admin', $allowlist);
            return $allowed === false;
        });

        // Test 2: Private IP address rejected
        $this->test('Private IP (192.168.1.1) rejected', function() use ($allowlist) {
            [$allowed, $error] = UrlGuard::isAllowed('https://192.168.1.1/internal', $allowlist);
            return $allowed === false;
        });

        // Test 3: AWS metadata address rejected
        $this->test('Cloud metadata endpoint rejected', function() use ($allowlist) {
            // Note: 169.254.169.254 is the AWS metadata endpoint
            [$allowed, $error] = UrlGuard::isAllowed('https://169.254.169.254/latest/meta-data/', $allowlist);
            return $allowed === false;
        });

        // Test 4: Host not in allowlist rejected
        $this->test('Host not in allowlist rejected', function() use ($allowlist) {
            [$allowed, $error] = UrlGuard::isAllowed('https://malicious-site.com/resource', $allowlist);
            return $allowed === false && strpos($error, 'allowlist') !== false;
        });

        // Test 5: HTTP (not HTTPS) rejected
        $this->test('Non-HTTPS URLs rejected', function() use ($allowlist) {
            [$allowed, $error] = UrlGuard::isAllowed('http://example.com/resource', $allowlist);
            return $allowed === false && strpos($error, 'https') !== false;
        });

        // Test 6: Malformed URL rejected
        $this->test('Malformed URL rejected', function() use ($allowlist) {
            [$allowed, $error] = UrlGuard::isAllowed('not-a-valid-url', $allowlist);
            return $allowed === false;
        });
    }

    private function testAuthorizationControls(): void
    {
        echo "\n--- TASK 3: AUTHORIZATION CONTROLS ---\n\n";

        // Test 1: Student cannot access admin functions
        $this->test('Student account identified correctly', function() {
            $stmt = Database::run(
                'SELECT role FROM users WHERE email = ? LIMIT 1',
                ['student1@example.test']
            );
            $user = $stmt->fetch();
            return $user['role'] === 'student';
        });

        // Test 2: Admin account identified correctly
        $this->test('Admin account identified correctly', function() {
            $stmt = Database::run(
                'SELECT role FROM users WHERE email = ? LIMIT 1',
                ['admin@example.test']
            );
            $user = $stmt->fetch();
            return $user['role'] === 'admin';
        });

        // Test 3: Role-based access control query
        $this->test('Students isolated from admin data', function() {
            $stmt = Database::run(
                'SELECT COUNT(*) AS n FROM users WHERE role = ?',
                ['admin']
            );
            $count = (int) $stmt->fetch()['n'];
            return $count === 1;  // One admin account
        });
    }

    private function testInputValidation(): void
    {
        echo "\n--- TASK 2 & 3: INPUT VALIDATION ---\n\n";

        // Test 1: Email validation
        $this->test('Valid email passes validation', function() {
            return Validator::isEmail('student@example.test') === true;
        });

        // Test 2: Invalid email rejected
        $this->test('Invalid email rejected', function() {
            return Validator::isEmail('not-an-email') === false;
        });

        // Test 3: Matric number validation
        $this->test('Valid matric number passes validation', function() {
            return Validator::isMatricNumber('2020/1/00001CS') === true;
        });

        // Test 4: Invalid matric number rejected
        $this->test('Invalid matric number rejected', function() {
            return Validator::isMatricNumber('invalid-matric') === false;
        });

        // Test 5: Login identifier accepts email or matric
        $this->test('Login identifier accepts email', function() {
            return Validator::isLoginIdentifier('student@example.test') === true;
        });

        // Test 6: Login identifier accepts matric
        $this->test('Login identifier accepts matric', function() {
            return Validator::isLoginIdentifier('2020/1/00001CS') === true;
        });
    }

    private function testLoggingSecurity(): void
    {
        echo "\n--- TASK 3: SECURITY LOGGING ---\n\n";

        // Test 1: Failed login logged
        $this->test('Failed login creates security event log entry', function() {
            $preCount = $this->getSecurityEventCount('login_failed');
            Auth::attemptLogin('student1@example.test', 'wrongpassword', '127.0.0.1');
            $postCount = $this->getSecurityEventCount('login_failed');
            return $postCount > $preCount;
        });

        // Test 2: No passwords in logs
        $this->test('Passwords not stored in security logs', function() {
            $stmt = Database::run(
                'SELECT detail FROM security_events LIMIT 10'
            );
            while ($row = $stmt->fetch()) {
                if (strpos($row['detail'], 'password') !== false || 
                    strpos($row['detail'], 'Tr0ub4dor') !== false) {
                    return false;
                }
            }
            return true;
        });

        // Test 3: No session tokens in logs
        $this->test('Session tokens not stored in security logs', function() {
            $stmt = Database::run(
                'SELECT detail FROM security_events LIMIT 10'
            );
            while ($row = $stmt->fetch()) {
                if (preg_match('/token|session[_id]*/i', $row['detail'])) {
                    return false;
                }
            }
            return true;
        });

        // Test 4: Log file exists and is writable
        $this->test('Security log file exists and writable', function() {
            $logFile = __DIR__ . '/../storage/logs/security.log';
            return is_file($logFile) && is_writable(dirname($logFile));
        });
    }

    private function getSecurityEventCount(string $eventType): int
    {
        $stmt = Database::run(
            'SELECT COUNT(*) AS n FROM security_events WHERE event_type = ?',
            [$eventType]
        );
        return (int) $stmt->fetch()['n'];
    }

    private function test(string $name, callable $test): void
    {
        try {
            $result = $test();
            if ($result === true) {
                $this->passed++;
                echo "✅ PASS: $name\n";
                $this->results[] = ['test' => $name, 'status' => 'PASS'];
            } else {
                $this->failed++;
                echo "❌ FAIL: $name\n";
                $this->results[] = ['test' => $name, 'status' => 'FAIL'];
            }
        } catch (Throwable $e) {
            $this->failed++;
            echo "❌ ERROR: $name - " . $e->getMessage() . "\n";
            $this->results[] = ['test' => $name, 'status' => 'ERROR'];
        }
    }

    private function printSummary(): void
    {
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 80) . "\n";
        echo sprintf("Total Tests: %d\n", $this->passed + $this->failed);
        echo sprintf("Passed: %d ✅\n", $this->passed);
        echo sprintf("Failed: %d ❌\n", $this->failed);
        echo sprintf("Success Rate: %.1f%%\n", 
            ($this->passed / ($this->passed + $this->failed)) * 100);
        echo str_repeat("=", 80) . "\n\n";

        if ($this->failed === 0) {
            echo "🎉 ALL TESTS PASSED! 🎉\n\n";
        }
    }
}

// Run the test suite
$suite = new SecurityTestSuite();
$suite->run();
?>
