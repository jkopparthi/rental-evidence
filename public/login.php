<?php
require __DIR__ . '/../src/bootstrap.php';
if (current_user()) redirect('dashboard.php');

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email    = strtolower(post('email'));
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Same message for wrong email or wrong password, so accounts can't be guessed.
    if ($user && password_verify($password, $user['password_hash'])) {
        login_user((int)$user['id']);
        redirect('dashboard.php');
    }
    $errors[] = 'That email and password don\'t match an account.';
}

page_header('Log in', 'auth');
?>
<section class="auth-card">
    <h1>Log in</h1>
    <?php form_errors($errors); ?>
    <form method="post" class="form" data-validate>
        <?= csrf_field() ?>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
        </label>
        <label>Password
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button type="submit" class="button">Log in</button>
    </form>
    <p class="muted">New here? <a href="register.php">Create an account</a></p>
</section>
<?php page_footer();
