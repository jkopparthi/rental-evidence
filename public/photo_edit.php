<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
$photo = find_owned_photo(int_param('id'), (int)$user['id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $photo['room'] = post('room');
    $photo['stage'] = post('stage');
    $photo['description'] = mb_substr(post('description'), 0, 2000);

    if (!in_array($photo['room'], ROOMS, true)) $errors[] = 'Choose a room.';
    if (!in_array($photo['stage'], ['before', 'after'], true)) $errors[] = 'Choose move-in or move-out.';

    if (!$errors) {
        db()->prepare('UPDATE photos SET room = ?, stage = ?, description = ? WHERE id = ?')
            ->execute([$photo['room'], $photo['stage'], nullable($photo['description']), $photo['id']]);
        flash('success', 'Photo saved.');
        redirect('property.php?id=' . $photo['property_id'] . '#room-' . md5($photo['room']));
    }
}

page_header('Edit photo');
?>
<div class="page-head"><h1>Edit photo</h1></div>
<?php form_errors($errors); ?>
<div class="edit-layout">
    <img class="edit-preview" src="photo.php?id=<?= (int)$photo['id'] ?>" alt="Photo being edited">
    <form method="post" class="form">
        <?= csrf_field() ?>
        <label>Room
            <select name="room">
                <?php foreach (ROOMS as $room): ?>
                    <option <?= $room === $photo['room'] ? 'selected' : '' ?>><?= e($room) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Inspection
            <select name="stage">
                <option value="before" <?= $photo['stage'] === 'before' ? 'selected' : '' ?>>Move-in</option>
                <option value="after" <?= $photo['stage'] === 'after' ? 'selected' : '' ?>>Move-out</option>
            </select>
        </label>
        <label>Description
            <textarea name="description" rows="5" maxlength="2000"><?= e($photo['description']) ?></textarea>
        </label>
        <dl class="file-meta">
            <div><dt>Original file</dt><dd><?= e($photo['original_name']) ?></dd></div>
            <div><dt>Size</dt><dd><?= e(format_bytes((int)$photo['size_bytes'])) ?></dd></div>
            <div><dt>Uploaded</dt><dd><?= e(format_date($photo['uploaded_at'], 'M j, Y g:i a')) ?></dd></div>
            <div><dt>SHA-256</dt><dd class="hash"><?= e($photo['sha256']) ?></dd></div>
        </dl>
        <div class="form-actions">
            <button type="submit" class="button">Save photo</button>
            <a class="button button-ghost" href="property.php?id=<?= (int)$photo['property_id'] ?>">Cancel</a>
        </div>
    </form>
</div>
<?php page_footer();
