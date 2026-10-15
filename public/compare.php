<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();
$property = find_owned_property(int_param('id'), (int)$user['id']);
$rooms = photos_by_room((int)$property['id']);

$paired = array_filter($rooms, fn($s) => $s['before'] && $s['after']);
$incomplete = array_diff_key($rooms, $paired);

page_header('Compare rooms: ' . $property['nickname']);
?>
<div class="page-head">
    <div>
        <h1>Compare rooms</h1>
        <p class="muted"><?= e($property['nickname']) ?>. Drag the handle to see move-in against move-out.</p>
    </div>
    <div class="page-head-actions">
        <a class="button button-ghost" href="property.php?id=<?= (int)$property['id'] ?>">Back to property</a>
        <a class="button" href="report.php?id=<?= (int)$property['id'] ?>">Print report</a>
    </div>
</div>

<?php if (!$paired): ?>
    <section class="empty">
        <h2>Nothing to compare yet</h2>
        <p>A room appears here once it has at least one move-in and one move-out photo.</p>
    </section>
<?php endif; ?>

<?php foreach ($paired as $room => $stages):
    $b = $stages['before'][0];
    $a = $stages['after'][0]; ?>
    <section class="room compare-room" data-compare>
        <h2 class="room-title"><?= e($room) ?></h2>

        <div class="compare-slider" data-slider style="--pos: 50%">
            <img class="compare-after" src="photo.php?id=<?= (int)$a['id'] ?>" alt="<?= e($room) ?> at move-out" data-slot="after">
            <div class="compare-before-wrap">
                <img class="compare-before" src="photo.php?id=<?= (int)$b['id'] ?>" alt="<?= e($room) ?> at move-in" data-slot="before">
            </div>
            <span class="compare-label compare-label-before">Move-in</span>
            <span class="compare-label compare-label-after">Move-out</span>
            <input type="range" min="0" max="100" value="50" class="compare-range"
                   aria-label="Reveal move-in or move-out photo for <?= e($room) ?>">
            <span class="compare-handle" aria-hidden="true"></span>
        </div>

        <div class="compare-notes">
            <?php foreach (['before' => 'Move-in', 'after' => 'Move-out'] as $stage => $label): ?>
                <div class="stage-<?= $stage ?>">
                    <h3><span class="tag tag-<?= $stage ?>"><?= $label ?></span></h3>
                    <p data-caption="<?= $stage ?>"><?= nl2br(e($stages[$stage][0]['description'] ?: 'No description.')) ?></p>
                    <?php if (count($stages[$stage]) > 1): ?>
                        <div class="thumb-strip" role="group" aria-label="Choose <?= strtolower($label) ?> photo">
                            <?php foreach ($stages[$stage] as $i => $ph): ?>
                                <button type="button" class="thumb <?= $i === 0 ? 'is-active' : '' ?>"
                                        data-swap="<?= $stage ?>"
                                        data-src="photo.php?id=<?= (int)$ph['id'] ?>"
                                        data-text="<?= e($ph['description'] ?: 'No description.') ?>">
                                    <img src="photo.php?id=<?= (int)$ph['id'] ?>" alt="<?= $label ?> photo <?= $i + 1 ?>">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>

<?php if ($incomplete): ?>
    <section class="missing">
        <h2>Rooms missing a set</h2>
        <ul>
            <?php foreach ($incomplete as $room => $stages): ?>
                <li><?= e($room) ?>: needs <?= $stages['before'] ? 'move-out' : 'move-in' ?> photos</li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>
<?php page_footer();
