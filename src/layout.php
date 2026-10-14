<?php
declare(strict_types=1);

function page_header(string $title, string $bodyClass = ''): void
{
    $user = current_user();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@500;700;800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js" defer></script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <a class="brand" href="<?= $user ? 'dashboard.php' : 'index.php' ?>">
        <span class="brand-mark" aria-hidden="true"></span><?= e(APP_NAME) ?>
    </a>
    <nav aria-label="Main">
        <?php if ($user): ?>
            <a href="dashboard.php">My properties</a>
            <form action="logout.php" method="post" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="link-button">Log out</button>
            </form>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a class="button button-small" href="register.php">Create account</a>
        <?php endif; ?>
    </nav>
</header>
<main id="main" class="page">
    <?php foreach (take_flashes() as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
<?php
}

function page_footer(): void
{
    ?>
</main>
<footer class="site-footer">
    <p>Only you can see your properties and photos. <a href="https://github.com/YOUR-USERNAME/rental-evidence-app/blob/main/PRIVACY.md">How your data is handled</a>.</p>
</footer>
</body>
</html>
<?php
}

/** Prints a list of validation errors above a form. */
function form_errors(array $errors): void
{
    if (!$errors) return;
    echo '<div class="flash flash-error" role="alert"><ul>';
    foreach ($errors as $err) echo '<li>' . e($err) . '</li>';
    echo '</ul></div>';
}
