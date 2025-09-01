<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= defined('APP_NAME') ? APP_NAME : 'App' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
      <div class="container">
        <a class="navbar-brand" href="users.php"><?= defined('APP_NAME') ? APP_NAME : 'App' ?></a>
        <div class="ms-auto">
          <?php if (is_logged_in()): ?>
            <span class="me-2 text-muted">Hi, <?= htmlspecialchars(current_user()['name']) ?></span>
            <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-primary btn-sm me-2">Login</a>
            <a href="register.php" class="btn btn-primary btn-sm">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
    <main class="container py-4">
