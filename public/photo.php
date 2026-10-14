<?php
// Streams a private photo after checking the logged-in user owns it.
// Files live outside /public, so this is the only way to view them.
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
$photo = find_owned_photo(int_param('id'), (int)$user['id']);

$path = upload_dir() . '/' . basename($photo['stored_name']);
if (!is_file($path)) {
    http_response_code(404);
    exit('Photo file is missing.');
}

session_write_close(); // don't block other requests while the image streams
header('Content-Type: ' . $photo['mime_type']);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
readfile($path);
