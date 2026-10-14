<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('dashboard.php');
verify_csrf();

$property = find_owned_property(int_param('id'), (int)$user['id']);

// Remove files from disk first; the database rows cascade-delete with the property.
$stmt = db()->prepare('SELECT stored_name FROM photos WHERE property_id = ?');
$stmt->execute([$property['id']]);
foreach ($stmt->fetchAll() as $photo) delete_photo_file($photo);

db()->prepare('DELETE FROM properties WHERE id = ?')->execute([$property['id']]);
flash('success', 'Deleted ' . $property['nickname'] . '.');
redirect('dashboard.php');
