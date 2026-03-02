<?php
/**
 * Impact Gallery Partial
 * Displays images from storage/uploads/impact/
 * Usage: include VIEWS_PATH . '/partials/impact-gallery.php';
 */
$impact_dir = (defined('ROOT_PATH') ? ROOT_PATH : (defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__, 3))) . '/storage/uploads/impact/';
$impact_images = [];
if (is_dir($impact_dir)) {
    foreach (glob($impact_dir . '*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE) as $file) {
        $impact_images[] = '/storage/uploads/impact/' . basename($file);
    }
}
// Show section only if there are images
if (empty($impact_images)) return;
?>

<!-- Impact Gallery Section -->
<section style="padding: 80px 0; background: #F7FAFC;">
    <div class="container">
        <div class="text-center mb-5">
            <span style="color: #7F3D9E; font-size: 0.85rem; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase;">Our Impact</span>
            <h2 class="mt-2 mb-3" style="color: #2D1A4A; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 2.4rem; position: relative; display: inline-block;">
                Moments That Matter
                <span style="display: block; width: 80px; height: 3px; background: linear-gradient(90deg, #C9A659, #E8C879); margin: 15px auto 0;"></span>
            </h2>
            <p style="color: #718096; max-width: 600px; margin: 0 auto; font-size: 1rem; line-height: 1.6;">
                A glimpse into the lives we touch and the communities we serve across Kenya.
            </p>
        </div>

        <div class="row g-3">
            <?php foreach ($impact_images as $i => $img_url): ?>
                <?php
                // Alternate column sizes for a masonry-like feel
                $col_class = match ($i % 6) {
                    0, 3 => 'col-md-6 col-lg-4',
                    1, 4 => 'col-md-6 col-lg-4',
                    default => 'col-md-12 col-lg-4',
                };
                $height = ($i % 3 === 2) ? '300px' : '220px';
                ?>
                <div class="<?= $col_class ?>">
                    <div style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.10); height: <?= $height ?>;">
                        <img src="<?= htmlspecialchars($img_url) ?>"
                             alt="Impact photo <?= $i + 1 ?>"
                             loading="lazy"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;"
                             onmouseover="this.style.transform='scale(1.05)'"
                             onmouseout="this.style.transform='scale(1)'">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
