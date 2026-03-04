<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<section style="padding: 80px 0; background: linear-gradient(135deg, #F3E8FF 0%, #EDE9FE 100%);">
    <div class="container text-center">
        <h1 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 3rem;">Membership Packages</h1>
        <p style="color: #4B5563; max-width: 760px; margin: 0 auto;">Choose a plan that fits your stage of life. All packages support dignified send-off services and dependable welfare support.</p>
    </div>
</section>

<section id="packages" style="padding: 70px 0; background: #F8FAFC;">
    <div class="container">
        <div class="row g-4 mb-5">
            <?php foreach (($package_tiers ?? []) as $plan): ?>
                <?php include VIEWS_PATH . '/partials/pricing-card.php'; ?>
            <?php endforeach; ?>
        </div>

        <?php include VIEWS_PATH . '/partials/pricing-compare.php'; ?>
    </div>
</section>

<section style="padding: 70px 0; background: #fff;">
    <div class="container">
        <div style="background: linear-gradient(135deg, #7F3D9E 0%, #5E2B7A 100%); border-radius: 20px; padding: 44px; text-align: center;">
            <h2 style="color: #fff; font-family: 'Playfair Display', serif; margin-bottom: 14px;">Ready to Join SHENA Companion?</h2>
            <p style="color: rgba(255,255,255,0.92); margin-bottom: 24px;">Register in minutes and start your welfare protection journey today.</p>
            <a href="/register" class="btn" style="background: #C9A659; color: #1A1A1A; font-weight: 700; padding: 12px 28px; border-radius: 10px; text-decoration: none;">Register Now</a>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
