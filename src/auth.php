<?php
declare(strict_types=1);

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = db()->prepare('SELECT id, name, email FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('info', 'Log in to see your properties.');
        redirect('login.php');
    }
    return $user;
}

function login_user(int $userId): void
{
    session_regenerate_id(true); // stops session fixation
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

/**
 * Loads a property only if it belongs to the logged-in user.
 * Every page that takes a property id goes through here, so users
 * can never view someone else's evidence by changing the URL.
 */
function find_owned_property(int $propertyId, int $userId): array
{
    $stmt = db()->prepare('SELECT * FROM properties WHERE id = ? AND user_id = ?');
    $stmt->execute([$propertyId, $userId]);
    $property = $stmt->fetch();
    if (!$property) {
        http_response_code(404);
        exit('Property not found.');
    }
    return $property;
}

function find_owned_photo(int $photoId, int $userId): array
{
    $stmt = db()->prepare(
        'SELECT ph.* FROM photos ph
         JOIN properties p ON p.id = ph.property_id
         WHERE ph.id = ? AND p.user_id = ?'
    );
    $stmt->execute([$photoId, $userId]);
    $photo = $stmt->fetch();
    if (!$photo) {
        http_response_code(404);
        exit('Photo not found.');
    }
    return $photo;
}
