<?php
declare(strict_types=1);

/**
 * Thin PDO wrapper. Every query MUST go through prepare()+execute()
 * with bound parameters -- this class has no method that accepts a
 * raw, interpolated SQL string with user data in it.
 *
 * Remediates: SQL Injection (Task 2, activities 10-11).
 */
final class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $db['host'],
                $db['port'],
                $db['name']
            );

            self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Use real prepared statements at the driver level,
                // not client-side emulation, so bound values are
                // never woven into the SQL text at all.
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }

    /**
     * Convenience helper: prepare + execute with bound params, return
     * the PDOStatement. This is the ONLY sanctioned way to run a query
     * with user-supplied values anywhere in this codebase.
     *
     * @param array<int|string,mixed> $params
     */
    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
