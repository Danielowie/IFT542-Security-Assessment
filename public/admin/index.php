<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap.php';

$admin = Auth::requireAdmin();

$stats = [
    'students' => Database::run('SELECT COUNT(*) AS n FROM users WHERE role = "student"')->fetch()['n'],
    'courses'  => Database::run('SELECT COUNT(*) AS n FROM courses')->fetch()['n'],
    'enrolments' => Database::run('SELECT COUNT(*) AS n FROM enrolments WHERE status = "active"')->fetch()['n'],
];

$recentEvents = Database::run(
    'SELECT event_type, subject, ip_address, created_at FROM security_events ORDER BY created_at DESC LIMIT 20'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Student Registration</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <nav>
        <a href="/dashboard.php">Profile</a>
        <a href="/admin/index.php">Admin</a>
        <a href="/admin/students.php">Students</a>
        <a href="/admin/courses.php">Manage Courses</a>
        <a href="/logout.php">Log out</a>
    </nav>

    <h1>Admin Dashboard</h1>
    <p>Students: <?= (int) $stats['students'] ?> &middot; Courses: <?= (int) $stats['courses'] ?> &middot; Active enrolments: <?= (int) $stats['enrolments'] ?></p>

    <h2>Recent Security Events</h2>
    <!--
        Broken-access-control remediation note: every page under
        /admin/ calls Auth::requireAdmin() before rendering anything,
        which re-checks the session's role server-side on each
        request. The vulnerable baseline only hid the /admin link in
        the nav bar for non-admins but had no server-side check, so
        any authenticated student could reach /admin/index.php
        directly by typing the URL (a classic IDOR/broken access
        control finding).
    -->
    <table>
        <tr><th>Event</th><th>Subject</th><th>IP</th><th>Time (UTC)</th></tr>
        <?php foreach ($recentEvents as $ev): ?>
        <tr>
            <td><?= e($ev['event_type']) ?></td>
            <td><?= e($ev['subject'] ?? '-') ?></td>
            <td><?= e($ev['ip_address']) ?></td>
            <td><?= e($ev['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
