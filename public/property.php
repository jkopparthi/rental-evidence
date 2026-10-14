<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
$property = find_owned_property(int_param('id'), (int)$user['id']);
$rooms = photos_by_room((int)$property['id']);
$pid = (int)$property['id'];

// Suggest move-out once the move-out date has passed.
$defaultStage = ($property['move_out_date'] && $property['move_out_date'] <= date('Y-m-d')) ? 'after' : 'before';

page_header($property['nickname']);
?>
<div class="page-head">
    <div>
        <h1><?= e($property['nickname']) ?></h1>
        <p class="muted"><?= e($property['address']) ?></p>
    </div>
    <div class="page-head-actions">
        <a class="button button-ghost" href="property_form.php?id=<?= $pid ?>">Edit details</a>
        <?php if ($rooms): ?>
            <a class="button button-ghost" href="compare.php?id=<?= $pid ?>">Compare rooms</a>
            <a class="button" href="report.php?id=<?= $pid ?>">Print report</a>
        <?php endif; ?>
    </div>
</div>

<dl class="facts">
    <div><dt>Landlord</dt><dd><?= e($property['landlord_name'] ?: '—') ?></dd></div>
    <div><dt>Move-in</dt><dd><?= e(format_date($property['move_in_date'])) ?></dd></div>
    <div><dt>Move-out</dt><dd><?= e(format_date($property['move_out_date'])) ?></dd></div>
</dl>
<?php if ($property['notes']): ?>
    <p class="notes"><?= nl2br(e($property['notes'])) ?></p>
<?php endif; ?>

<section class="upload-panel" aria-labelledby="upload-title">
    <h2 id="upload-title">Add photos</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data" class="form upload-form" data-upload>
        <?= csrf_field() ?>
        <input type="hidden" name="property_id" value="<?= $pid ?>">
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= (int)$config['app']['max_upload_mb'] * 1048576 ?>">

        <fieldset class="stage-toggle">
            <legend>Which inspection?</legend>
            <label class="stage-option stage-before">
                <input type="radio" name="stage" value="before" <?= $defaultStage === 'before' ? 'checked' : '' ?>>
                Move-in
            </label>
            <label class="stage-option stage-after">
                <input type="radio" name="stage" value="after" <?= $defaultStage === 'after' ? 'checked' : '' ?>>
                Move-out
            </label>
        </fieldset>

        <label>Room
            <select name="room" required>
                <?php foreach (ROOMS as $room): ?>
                    <option><?= e($room) ?></option>
                <?php endforeach; ?>
            </select>
        </label>

        <label class="dropzone" data-dropzone>
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required
                   data-max-mb="<?= (int)$config['app']['max_upload_mb'] ?>">
            <span class="dropzone-text">Drop photos here or <u>choose files</u><br>
                <small>JPG, PNG or WebP, up to <?= (int)$config['app']['max_upload_mb'] ?> MB each</small></span>
        </label>
        <div class="previews" data-previews aria-live="polite"></div>

        <label>What do these photos show?
            <textarea name="description" rows="3" maxlength="2000"
                      placeholder="Small burn mark on counter left of the stove, about 3 cm. Was here when I moved in."></textarea>
        </label>
        <button type="submit" class="button">Upload photos</button>
    </form>
</section>

<?php if (!$rooms): ?>
    <section class="empty">
        <h2>No photos yet</h2>
        <p>Start at the front door and work through each room. Photograph walls, floors,
           appliances and anything already damaged.</p>
    </section>
<?php endif; ?>

<?php foreach ($rooms as $room => $stages): ?>
    <section class="room" id="room-<?= e(md5($room)) ?>">
        <h2 class="room-title"><?= e($room) ?></h2>
        <div class="room-columns">
            <?php foreach (['before' => 'Move-in', 'after' => 'Move-out'] as $stage => $label): ?>
                <div class="stage-column stage-<?= $stage ?>">
                    <h3><span class="tag tag-<?= $stage ?>"><?= $label ?></span></h3>
                    <?php if (!$stages[$stage]): ?>
                        <p class="muted">No <?= strtolower($label) ?> photos for this room.</p>
                    <?php endif; ?>
                    <div class="photo-grid">
                        <?php foreach ($stages[$stage] as $photo): ?>
                            <figure class="photo-card" data-photo-card>
                                <a href="photo.php?id=<?= (int)$photo['id'] ?>" data-lightbox
                                   data-caption="<?= e($photo['description'] ?? '') ?>">
                                    <img src="photo.php?id=<?= (int)$photo['id'] ?>" alt="<?= e($room . ', ' . strtolower($label) . ': ' . ($photo['description'] ?: $photo['original_name'])) ?>" loading="lazy">
                                </a>
                                <figcaption>
                                    <?php if ($photo['description']): ?>
                                        <p><?= nl2br(e($photo['description'])) ?></p>
                                    <?php endif; ?>
                                    <time class="stamp" datetime="<?= e($photo['taken_at'] ?? $photo['uploaded_at']) ?>">
                                        <?= $photo['taken_at'] ? 'Taken ' . e(format_date($photo['taken_at'], 'M j, Y g:i a'))
                                                                : 'Uploaded ' . e(format_date($photo['uploaded_at'], 'M j, Y g:i a')) ?>
                                    </time>
                                    <span class="card-actions">
                                        <a href="photo_edit.php?id=<?= (int)$photo['id'] ?>">Edit</a>
                                        <form method="post" action="photo_delete.php" data-ajax-delete
                                              data-confirm="Delete this photo? This can't be undone.">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="id" value="<?= (int)$photo['id'] ?>">
                                            <button type="submit" class="link-button danger">Delete</button>
                                        </form>
                                    </span>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
<?php page_footer();
