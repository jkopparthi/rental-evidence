<?php
require __DIR__ . '/../src/bootstrap.php';
if (current_user()) redirect('dashboard.php');

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name     = post('name');
    $email    = strtolower(post('email'));
    $password = $_POST['password'] ?? '';

    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 8) $errors[] = 'Use a password with at least 8 characters.';

    if (!$errors) {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) $errors[] = 'An account with that email already exists. Log in instead.';
    }

    if (!$errors) {
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        login_user((int)db()->lastInsertId());
        flash('success', 'Account created. Add your first property.');
        redirect('dashboard.php');
    }
}

page_header('Create account', 'auth');
?>
<section class="auth-card">
    <h1>Create your account</h1>
    <?php form_errors($errors); ?>
    <form method="post" class="form" data-validate>
        <?= csrf_field() ?>
        <label>Name
            <input type="text" name="name" value="<?= e($name) ?>" required maxlength="100" autocomplete="name">
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" required autocomplete="email">
        </label>
        <label>Password
            <input type="password" name="password" required minlength="8" autocomplete="new-password">
            <small>At least 8 characters.</small>
        </label>
        <button type="submit" class="button">Create account</button>
    </form>
    <p class="muted">Already registered? <a href="login.php">Log in</a></p>
</section>
<?php page_footer();
