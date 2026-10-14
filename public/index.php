<?php
require __DIR__ . '/../src/bootstrap.php';
if (current_user()) redirect('dashboard.php');

page_header('Document your rental', 'home');
?>
<section class="hero">
    <h1>Get your deposit back with photos a landlord can't argue with.</h1>
    <p class="lede">Photograph every room when you move in, again when you move out,
        and keep both sets side by side with dated notes.</p>
    <div class="hero-actions">
        <a class="button" href="register.php">Start documenting</a>
        <a class="button button-ghost" href="login.php">I already have an account</a>
    </div>
</section>

<section class="steps" aria-label="How it works">
    <ol>
        <li><h2>Add your rental</h2><p>Address, landlord and your move-in date.</p></li>
        <li><h2>Photograph move-in</h2><p>Walk room by room. Describe scuffs, stains and anything broken.</p></li>
        <li><h2>Photograph move-out</h2><p>Compare each room with a slider and print a dated report.</p></li>
    </ol>
</section>
<?php page_footer();
