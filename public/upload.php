<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');

// If the request is bigger than post_max_size, PHP empties $_POST entirely.
if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    flash('error', 'Those files are too large to upload at once. Try fewer photos.');
    redirect($_SERVER['HTTP_REFERER'] ?? 'dashboard.php');
}
verify_csrf();

$property = find_owned_property(int_param('property_id'), (int)$user['id']);
$back = 'property.php?id=' . $property['id'];

$stage = post('stage');
$room = post('room');
$description = mb_substr(post('description'), 0, 2000);

if (!in_array($stage, ['before', 'after'], true) || !in_array($room, ROOMS, true)) {
    flash('error', 'Choose an inspection and a room.');
    redirect($back);
}
if (empty($_FILES['photos'])) {
    flash('error', 'Choose at least one photo.');
    redirect($back);
}

$saved = 0;
foreach (normalize_files($_FILES['photos']) as $file) {
    $error = store_photo($file, (int)$property['id'], $room, $stage, $description);
    if ($error) {
        flash('error', $error);
    } elseif ($file['error'] === UPLOAD_ERR_OK) {
        $saved++;
    }
}

if ($saved) {
    flash('success', "Uploaded $saved photo" . ($saved === 1 ? '' : 's') . " to $room.");
}
redirect($back . '#room-' . md5($room));
