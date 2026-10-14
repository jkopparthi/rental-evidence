<?php
// Creates a new property, or edits one when ?id= is given.
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();

$id = int_param('id');
$property = $id ? find_owned_property($id, (int)$user['id']) : [
    'nickname' => '', 'address' => '', 'landlord_name' => '',
    'move_in_date' => '', 'move_out_date' => '', 'notes' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach (['nickname', 'address', 'landlord_name', 'move_in_date', 'move_out_date', 'notes'] as $field) {
        $property[$field] = post($field);
    }

    if ($property['nickname'] === '') $errors[] = 'Give the property a short name, like "Main St apartment".';
    if ($property['address'] === '')  $errors[] = 'Enter the address.';
    if (!valid_date($property['move_in_date']) || !valid_date($property['move_out_date'])) {
        $errors[] = 'Enter dates as YYYY-MM-DD.';
    }
    if ($property['move_in_date'] && $property['move_out_date']
        && $property['move_out_date'] < $property['move_in_date']) {
        $errors[] = 'Move-out date must be after the move-in date.';
    }

    if (!$errors) {
        $values = [
            $property['nickname'], $property['address'], nullable($property['landlord_name']),
            nullable($property['move_in_date']), nullable($property['move_out_date']), nullable($property['notes']),
        ];
        if ($id) {
            $stmt = db()->prepare(
                'UPDATE properties SET nickname = ?, address = ?, landlord_name = ?, move_in_date = ?,
                        move_out_date = ?, notes = ? WHERE id = ? AND user_id = ?'
            );
            $stmt->execute([...$values, $id, $user['id']]);
            flash('success', 'Property saved.');
        } else {
            $stmt = db()->prepare(
                'INSERT INTO properties (nickname, address, landlord_name, move_in_date, move_out_date, notes, user_id)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([...$values, $user['id']]);
            $id = (int)db()->lastInsertId();
            flash('success', 'Property added. Upload your move-in photos below.');
        }
        redirect('property.php?id=' . $id);
    }
}

page_header($id ? 'Edit property' : 'Add a property');
?>
<div class="page-head">
    <h1><?= $id ? 'Edit property' : 'Add a property' ?></h1>
</div>
<?php form_errors($errors); ?>
<form method="post" class="form form-wide" data-validate>
    <?= csrf_field() ?>
    <label>Name
        <input type="text" name="nickname" value="<?= e($property['nickname']) ?>" required maxlength="100"
               placeholder="Main St apartment">
    </label>
    <label>Address
        <input type="text" name="address" value="<?= e($property['address']) ?>" required maxlength="255">
    </label>
    <label>Landlord or property manager
        <input type="text" name="landlord_name" value="<?= e($property['landlord_name']) ?>" maxlength="100">
    </label>
    <div class="form-row">
        <label>Move-in date
            <input type="date" name="move_in_date" value="<?= e($property['move_in_date']) ?>">
        </label>
        <label>Move-out date
            <input type="date" name="move_out_date" value="<?= e($property['move_out_date']) ?>">
        </label>
    </div>
    <label>Notes
        <textarea name="notes" rows="4" placeholder="Lease number, deposit amount, anything agreed in writing"><?= e($property['notes']) ?></textarea>
    </label>
    <div class="form-actions">
        <button type="submit" class="button"><?= $id ? 'Save property' : 'Add property' ?></button>
        <a class="button button-ghost" href="<?= $id ? 'property.php?id=' . $id : 'dashboard.php' ?>">Cancel</a>
    </div>
</form>

<?php if ($id): ?>
    <form method="post" action="property_delete.php" class="danger-zone"
          data-confirm="Delete this property and all of its photos? This can't be undone.">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <button type="submit" class="button button-danger">Delete property</button>
    </form>
<?php endif; ?>
<?php page_footer();
