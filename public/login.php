<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        Logger::log('csrf_rejected', null, Logger::clientIp(), ['form' => 'login']);
        $error = 'Your session expired. Please try again.';
    } else {
        $identifier = trim((string) ($_POST['identifier'] ?? ''));
        $password   = (string) ($_POST['password'] ?? '');

        if ($identifier === '' || $password === '' || !Validator::isLoginIdentifier($identifier)) {
            // Same generic message as a real auth failure -- do not
            // reveal that the format itself was the problem.
            Logger::log('validation_rejected', $identifier, Logger::clientIp(), ['field' => 'identifier']);
            $error = Auth::GENERIC_ERROR;
        } else {
            $result = Auth::attemptLogin($identifier, $password, Logger::clientIp());
            if ($result['ok']) {
                Csrf::rotate();
                header('Location: /dashboard.php');
                exit;
            }
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Student Registration</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <h1>Student Login</h1>

    <?php if ($error): ?>
        <p class="error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="/login.php" autocomplete="off">
        <?= Csrf::field() ?>
        <label for="identifier">Email or Matric Number</label>
        <input type="text" id="identifier" name="identifier" required maxlength="190">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required maxlength="128">

        <button type="submit">Log in</button>
    </form>

    <p><a href="/register.php">Create an account</a> &middot; <a href="/password_reset_request.php">Forgot password?</a></p>
</body>
</html>
