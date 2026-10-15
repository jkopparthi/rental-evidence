<?php
// Print-friendly condition report. Use the browser's "Save as PDF" to keep a copy.
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
$property = find_owned_property(int_param('id'), (int)$user['id']);
$rooms = photos_by_room((int)$property['id']);

page_header('Condition report: ' . $property['nickname'], 'report');
?>
<div class="report-toolbar no-print">
    <a class="button button-ghost" href="property.php?id=<?= (int)$property['id'] ?>">Back to property</a>
    <button type="button" class="button" data-print>Print or save as PDF</button>
</div>

<article class="report-doc">
    <header class="report-head">
        <h1>Rental condition report</h1>
        <table class="report-meta">
            <tr><th>Property</th><td><?= e($property['nickname']) ?></td></tr>
            <tr><th>Address</th><td><?= e($property['address']) ?></td></tr>
            <tr><th>Tenant</th><td><?= e($user['name']) ?> (<?= e($user['email']) ?>)</td></tr>
            <tr><th>Landlord</th><td><?= e($property['landlord_name'] ?: '—') ?></td></tr>
            <tr><th>Move-in</th><td><?= e(format_date($property['move_in_date'])) ?></td></tr>
            <tr><th>Move-out</th><td><?= e(format_date($property['move_out_date'])) ?></td></tr>
            <tr><th>Generated</th><td><?= e(date('M j, Y g:i a T')) ?></td></tr>
        </table>
    </header>

    <?php if (!$rooms): ?>
        <p>No photos have been uploaded for this property.</p>
    <?php endif; ?>

    <?php foreach ($rooms as $room => $stages): ?>
        <section class="report-room">
            <h2><?= e($room) ?></h2>
            <div class="report-columns">
                <?php foreach (['before' => 'Move-in', 'after' => 'Move-out'] as $stage => $label): ?>
                    <div>
                        <h3><?= $label ?></h3>
                        <?php if (!$stages[$stage]): ?><p class="muted">No photos.</p><?php endif; ?>
                        <?php foreach ($stages[$stage] as $ph): ?>
                            <figure class="report-photo">
                                <img src="photo.php?id=<?= (int)$ph['id'] ?>" alt="<?= e($room . ' ' . strtolower($label)) ?>">
                                <figcaption>
                                    <?php if ($ph['description']): ?><p><?= nl2br(e($ph['description'])) ?></p><?php endif; ?>
                                    <small>
                                        <?= $ph['taken_at'] ? 'Taken ' . e(format_date($ph['taken_at'], 'M j, Y g:i a')) . '. ' : '' ?>
                                        Uploaded <?= e(format_date($ph['uploaded_at'], 'M j, Y g:i a')) ?>.<br>
                                        SHA-256 <span class="hash"><?= e($ph['sha256']) ?></span>
                                    </small>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</article>
<?php page_footer();
