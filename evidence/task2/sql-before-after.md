# Task 2: SQL Injection Remediation

**Student:** 2021-1-84189CF

## Vulnerable Pattern (Before)

**File:** `legacy/login_vulnerable.php`

```php
$identifier = $_POST['identifier']; // no validation
$password   = $_POST['password'];
$sql = "SELECT * FROM users WHERE email = '$identifier' AND password = '" . md5($password) . "'";
$result = $conn->query($sql);
if ($result === false) {
    die('Query failed: ' . $conn->error . ' SQL was: ' . $sql);
}
```

**Vulnerability:** SQL injection via string concatenation. Attack: `identifier: admin@example.test' --`

## Secure Implementation (After)

**File:** `src/Auth.php` (lines 43–48)

```php
$stmt = Database::run(
    'SELECT id, matric_no, email, password_hash, full_name, role, is_active
     FROM users WHERE email = ? OR matric_no = ? LIMIT 1',
    [$identifier, $identifier]
);
$user = $stmt->fetch();
```

**Security:** Parameterized query with PDO prepared statements. User input bound as data, never as SQL code.

## Database Layer

**File:** `src/Database.php`

```php
public static function run(string $sql, array $params = []): PDOStatement
{
    $stmt = self::connection()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
```

**Critical:** `PDO::ATTR_EMULATE_PREPARES = false` ensures server-side prepared statements.

## Coverage

✅ All 10 database access points use `Database::run()`:
- Login (auth query)
- Registration
- Profile updates
- Course registration
- Admin searches
- Document upload metadata
- Rate limiting
- Password reset
- Authorization checks

**Result:** 100% parameterized queries. SQL injection eliminated.
