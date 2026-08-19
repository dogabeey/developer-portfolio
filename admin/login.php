<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

if (logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $statement = database()->prepare('SELECT id, username, password_hash FROM users WHERE username = ? LIMIT 1');
    $statement->execute([$username]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: index.php');
        exit;
    }
    $error = 'Incorrect username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin login | Game Dev Portfolio</title>
  <link rel="stylesheet" href="admin.css">
</head>
<body class="login-page">
  <main class="login-card">
    <a class="back-link" href="../index.php">← Portfolio</a>
    <p class="eyebrow">Admin area</p>
    <h1>Welcome back.</h1>
    <p class="muted">Sign in to add and manage projects.</p>
    <?php if ($error): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>Username<input name="username" autocomplete="username" required autofocus></label>
      <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
      <button type="submit">Sign in</button>
    </form>
  </main>
</body>
</html>
