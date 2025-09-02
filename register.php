<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $errors = $errors ?? [];

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!$errors) {
        try {
            $pdo = db(); // your PDO Postgres connection
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Normalize email to lowercase so UNIQUE(email) works case-insensitively at app level
            $normEmail = strtolower($email);

            // Insert and return the new user in one statement; if email exists, do nothing
            $stmt = $pdo->prepare(
                'INSERT INTO auth_users (name, email, password_hash)
                 VALUES (:name, :email, :password_hash)
                 ON CONFLICT (email) DO NOTHING
                 RETURNING id, name, email'
            );

            $stmt->execute([
                ':name' => $name,
                ':email' => $normEmail,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Conflict hit: email already exists
                $errors[] = 'Email is already registered.';
            } else {
                // Auto-login
                login_user($user);
                header('Location: users.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

?>
<?php include __DIR__ . '/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-4">Create your account</h1>
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <form method="post">
          <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required placeholder="At least 6 chars">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Confirm Password</label>
              <input type="password" name="confirm_password" class="form-control" required>
            </div>
          </div>
          <button class="btn btn-primary w-100">Create account</button>
          <p class="text-muted small mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
