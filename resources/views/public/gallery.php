<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<section style="padding: 80px 0; background: #F7F7F9;">
    <div class="container">
        <div class="text-center mb-5">
            <h1 style="font-family: 'Playfair Display', serif; color: #2D1A4A;">SHENA Community Gallery</h1>
            <p style="color: #6B7280; max-width: 760px; margin: 0 auto;">Moments from community mobilization, sensitization, recruitment journeys, and dignified service support across Kenya.</p>
        </div>

        <div class="row g-4">
            <?php
            $items = [
                ['file' => 'community.jpeg', 'title' => 'Community Outreach'],
                ['file' => 'community-mobilization.jpeg', 'title' => 'Community Mobilization'],
                ['file' => 'community-mobilization1.jpeg', 'title' => 'Mobilization Session'],
                ['file' => 'community-mobilization2.jpeg', 'title' => 'Community Engagement'],
                ['file' => 'sensitization1.jpeg', 'title' => 'Sensitization Program'],
                ['file' => 'sensitization2.jpeg', 'title' => 'Awareness Session'],
                ['file' => 'sensitization3.jpeg', 'title' => 'Public Sensitization'],
                ['file' => 'sensitization4.jpeg', 'title' => 'Community Education'],
                ['file' => 'recruitment-journey.jpeg', 'title' => 'Recruitment Journey'],
                ['file' => 'recruitment-journey1.jpeg', 'title' => 'Member Recruitment'],
                ['file' => 'recruitment-journey3.jpeg', 'title' => 'Registration Drive'],
                ['file' => 'funeral-service1.jpeg', 'title' => 'Funeral Service Support'],
                ['file' => 'castket1.jpeg', 'title' => 'Casket Support'],
                ['file' => 'casket2.jpeg', 'title' => 'Casket Preparation'],
                ['file' => 'casket3.jpeg', 'title' => 'Service Readiness'],
                ['file' => 'casket4.jpeg', 'title' => 'Dignified Send-off'],
                ['file' => 'background-image1.jpeg', 'title' => 'Community Event Backdrop']
            ];
            ?>

            <?php foreach ($items as $item): ?>
                <div class="col-lg-4 col-md-6">
                    <div style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.08);">
                        <img src="/public/images/<?php echo e($item['file']); ?>" alt="<?php echo e($item['title']); ?>" style="width: 100%; height: 250px; object-fit: cover;">
                        <div style="padding: 14px 16px;">
                            <h5 style="margin: 0; color: #2D1A4A; font-size: 1rem;"><?php echo e($item['title']); ?></h5>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="col-12">
                <div style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.08);">
                    <video controls style="width: 100%; max-height: 520px; background: #000;">
                        <source src="/public/images/farewell-service-video.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
