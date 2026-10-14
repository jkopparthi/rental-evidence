<?php
// Deletes a photo. Answers with JSON when called from JavaScript (fetch),
// or redirects back when submitted as a normal form.
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');
verify_csrf();

$photo = find_owned_photo(int_param('id'), (int)$user['id']);
delete_photo_file($photo);
db()->prepare('DELETE FROM photos WHERE id = ?')->execute([$photo['id']]);

$wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
if ($wantsJson) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'id' => (int)$photo['id']]);
    exit;
}
flash('success', 'Photo deleted.');
redirect('property.php?id=' . $photo['property_id']);
