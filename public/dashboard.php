<?php
require __DIR__ . '/../src/bootstrap.php';
$user = require_login();

$stmt = db()->prepare(
    "SELECT p.*,
            SUM(ph.stage = 'before') AS before_count,
            SUM(ph.stage = 'after')  AS after_count
     FROM properties p
     LEFT JOIN photos ph ON ph.property_id = p.id
     WHERE p.user_id = ?
     GROUP BY p.id
     ORDER BY p.created_at DESC"
);
$stmt->execute([$user['id']]);
$properties = $stmt->fetchAll();

page_header('My properties');
?>
<div class="page-head">
    <h1>My properties</h1>
    <a class="button" href="property_form.php">Add a property</a>
</div>

<?php if (!$properties): ?>
    <section class="empty">
        <h2>No properties yet</h2>
        <p>Add the place you're renting, then start photographing each room on move-in day.</p>
        <a class="button" href="property_form.php">Add your first property</a>
    </section>
<?php else: ?>
    <ul class="property-list">
        <?php foreach ($properties as $p): ?>
            <li class="property-row">
                <a href="property.php?id=<?= (int)$p['id'] ?>" class="property-link">
                    <strong><?= e($p['nickname']) ?></strong>
                    <span class="muted"><?= e($p['address']) ?></span>
                </a>
                <span class="counts">
                    <span class="tag tag-before"><?= (int)$p['before_count'] ?> move-in</span>
                    <span class="tag tag-after"><?= (int)$p['after_count'] ?> move-out</span>
                </span>
                <span class="muted dates">
                    In <?= e(format_date($p['move_in_date'])) ?><br>Out <?= e(format_date($p['move_out_date'])) ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
<?php page_footer();
