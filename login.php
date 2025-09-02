<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$errors = [];
$email = '';

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $email = trim($_POST['email'] ?? '');
//     $password = $_POST['password'] ?? '';

//     if ($email === '' || $password === '') {
//         $errors[] = 'Email and password are required.';
//     } else {
//         try {
//             $pdo = db();
//             $stmt = $pdo->prepare('SELECT * FROM auth_users WHERE email = :email LIMIT 1');
//             $stmt->execute([':email' => strtolower($email)]);
//             $user = $stmt->fetch();
//             if ($user && password_verify($password, $user['password_hash'])) {
//                 login_user($user);
//                 header('Location: users.php');
//                 exit;
//             } else {
//                 $errors[] = 'Invalid email or password.';
//             }
//         } catch (PDOException $e) {
//             $errors[] = 'Login failed.';
//         }
//     }
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = $errors ?? [];

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    } else {
        try {
            $pdo = db(); // PDO connection to PostgreSQL
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // If you did NOT switch to CITEXT in DB, keep emails normalized:
            $normEmail = strtolower($email);

            $stmt = $pdo->prepare(
                'SELECT id, name, email, password_hash
                   FROM auth_users
                  WHERE email = :email
                  LIMIT 1'
            );
            $stmt->execute([':email' => $normEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Optional: upgrade hash if algorithm/cost changed
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $pdo->prepare('UPDATE auth_users SET password_hash = :h WHERE id = :id');
                    $upd->execute([':h' => $newHash, ':id' => $user['id']]);
                    $user['password_hash'] = $newHash;
                }

                // Security: new session id on login
                if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                session_regenerate_id(true);

                login_user($user); // your helper
                header('Location: users.php');
                exit;
            } else {
                $errors[] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            // Log $e->getMessage() server-side; don’t expose details to users
            $errors[] = 'Login failed.';
        }
    }
}

?>
<?php include __DIR__ . '/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-5 col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body p-4">
        <h1 class="h4 mb-4">Sign in</h1>
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
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100">Sign in</button>
          <p class="text-muted small mt-3 mb-0">No account? <a href="register.php">Register</a></p>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
