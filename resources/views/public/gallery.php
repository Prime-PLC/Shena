<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Gallery Hero Section -->
<section style="position: relative; min-height: 480px; display: flex; align-items: center; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('/public/images/community-mobilization2.jpeg') center/cover no-repeat;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center text-white">
                <p style="color: #C9A659; font-size: 0.82rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px;">OUR STORY IN PICTURES</p>
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 700; font-family: 'Playfair Display', serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                    SHENA Community Gallery
                </h1>
                <p class="lead" style="font-size: 1.1rem; max-width: 720px; margin: 0 auto; line-height: 1.7; opacity: 0.92; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                    Moments captured across Kenya — from community mobilization and sensitization campaigns to dignified farewell services.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Albums Section -->
<section style="padding: 90px 0; background: #F7F7F9;">
    <div class="container">

        <!-- ── Album 1: Community Outreach & Mobilization ── -->
        <div class="mb-5 reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #F3E8FF; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-users" style="color: #7F3D9E; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Community Outreach &amp; Mobilization</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">Bringing people together — grassroots meetings, barazas, and county-wide engagement drives building a stronger SHENA community.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #7F3D9E, #C9A659); border-radius: 2px; margin-bottom: 24px;"></div>
            <div class="row g-3">
                <?php
                $album1 = [
                    ['file' => 'community.jpeg',             'title' => 'Community Gathering'],
                    ['file' => 'community-mobilization.jpeg',  'title' => 'Mobilization Drive'],
                    ['file' => 'community-mobilization1.jpeg', 'title' => 'County Engagement'],
                    ['file' => 'community-mobilization2.jpeg', 'title' => 'Open Baraza Session'],
                ];
                foreach ($album1 as $img): ?>
                <div class="col-lg-3 col-md-6 reveal-up">
                    <div class="gallery-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.09); cursor: zoom-in;" onclick="openLightbox('/public/images/<?php echo e($img['file']); ?>', '<?php echo e($img['title']); ?>')">
                        <img src="/public/images/<?php echo e($img['file']); ?>" alt="<?php echo e($img['title']); ?>" style="width: 100%; height: 240px; object-fit: cover; display: block; transition: transform 0.4s ease;">
                        <div style="background: white; padding: 12px 16px;">
                            <p style="margin: 0; color: #2D1A4A; font-weight: 600; font-size: 0.9rem;"><?php echo e($img['title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Album 2: Sensitization & Awareness Programs ── -->
        <div class="mb-5 reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #FFF7E6; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-bullhorn" style="color: #C9A659; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Sensitization &amp; Awareness Programs</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">Educating communities about SHENA's welfare cover, claim processes, and the importance of dignified farewell planning.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #C9A659, #7F3D9E); border-radius: 2px; margin-bottom: 24px;"></div>
            <div class="row g-3">
                <?php
                $album2 = [
                    ['file' => 'sensitization1.jpeg', 'title' => 'Public Awareness Forum'],
                    ['file' => 'sensitization2.jpeg', 'title' => 'Welfare Education Session'],
                    ['file' => 'sensitization3.jpeg', 'title' => 'Community Information Day'],
                    ['file' => 'sensitization4.jpeg', 'title' => 'Policy Sensitization Drive'],
                ];
                foreach ($album2 as $img): ?>
                <div class="col-lg-3 col-md-6 reveal-up">
                    <div class="gallery-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.09); cursor: zoom-in;" onclick="openLightbox('/public/images/<?php echo e($img['file']); ?>', '<?php echo e($img['title']); ?>')">
                        <img src="/public/images/<?php echo e($img['file']); ?>" alt="<?php echo e($img['title']); ?>" style="width: 100%; height: 240px; object-fit: cover; display: block; transition: transform 0.4s ease;">
                        <div style="background: white; padding: 12px 16px;">
                            <p style="margin: 0; color: #2D1A4A; font-weight: 600; font-size: 0.9rem;"><?php echo e($img['title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Album 3: Recruitment Journeys ── -->
        <div class="mb-5 reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #E8F5E9; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-route" style="color: #2E7D32; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Recruitment Journeys</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">Our agents and teams on the road — reaching new members across towns and villages, one registration at a time.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #2E7D32, #7F3D9E); border-radius: 2px; margin-bottom: 24px;"></div>
            <div class="row g-3">
                <?php
                $album3 = [
                    ['file' => 'recruitment-journey.jpeg',  'title' => 'Field Recruitment Drive'],
                    ['file' => 'recruitment-journey1.jpeg', 'title' => 'Agent Registration Tour'],
                    ['file' => 'recruitment-journey3.jpeg', 'title' => 'On-the-Road Sign-ups'],
                ];
                foreach ($album3 as $img): ?>
                <div class="col-lg-4 col-md-6 reveal-up">
                    <div class="gallery-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.09); cursor: zoom-in;" onclick="openLightbox('/public/images/<?php echo e($img['file']); ?>', '<?php echo e($img['title']); ?>')">
                        <img src="/public/images/<?php echo e($img['file']); ?>" alt="<?php echo e($img['title']); ?>" style="width: 100%; height: 260px; object-fit: cover; display: block; transition: transform 0.4s ease;">
                        <div style="background: white; padding: 12px 16px;">
                            <p style="margin: 0; color: #2D1A4A; font-weight: 600; font-size: 0.9rem;"><?php echo e($img['title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Album 4: Dignified Funeral Services & Casket Support ── -->
        <div class="mb-5 reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #F3E8FF; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-dove" style="color: #7F3D9E; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Dignified Funeral Services</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">A glimpse into the professional and compassionate funeral support SHENA delivers — caskets, hearses, and send-off logistics handled with royal care.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #7F3D9E, #2D1A4A); border-radius: 2px; margin-bottom: 24px;"></div>
            <div class="row g-3">
                <?php
                $album4 = [
                    ['file' => 'funeral-service1.jpeg',  'title' => 'Farewell Ceremony Support'],
                    ['file' => 'funeral-service2.jpeg',  'title' => 'Funeral Service Coordination'],
                    ['file' => 'castket1.jpeg',           'title' => 'Executive Casket — Series 1'],
                    ['file' => 'casket2.jpeg',            'title' => 'Executive Casket — Series 2'],
                    ['file' => 'casket3.jpeg',            'title' => 'Premium Casket Display'],
                    ['file' => 'casket4.jpeg',            'title' => 'Dignified Send-off Casket'],
                    ['file' => 'hearse-service.jpeg',    'title' => 'Hearse Transportation'],
                    ['file' => 'hearse-service2.jpeg',   'title' => 'Body Transit Convoy'],
                ];
                foreach ($album4 as $img): ?>
                <div class="col-lg-3 col-md-6 reveal-up">
                    <div class="gallery-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.09); cursor: zoom-in;" onclick="openLightbox('/public/images/<?php echo e($img['file']); ?>', '<?php echo e($img['title']); ?>')">
                        <img src="/public/images/<?php echo e($img['file']); ?>" alt="<?php echo e($img['title']); ?>" style="width: 100%; height: 230px; object-fit: cover; display: block; transition: transform 0.4s ease;">
                        <div style="background: white; padding: 12px 16px;">
                            <p style="margin: 0; color: #2D1A4A; font-weight: 600; font-size: 0.9rem;"><?php echo e($img['title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Album 5: Event Backdrops & Promotional ── -->
        <div class="mb-5 reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #E0F2FE; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-images" style="color: #0284C7; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Event Backdrops &amp; Promotional</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">SHENA branded event settings and services overview — how we present ourselves at official gatherings and promotional drives.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #0284C7, #7F3D9E); border-radius: 2px; margin-bottom: 24px;"></div>
            <div class="row g-3">
                <?php
                $album5 = [
                    ['file' => 'background-image1.jpeg', 'title' => 'SHENA Event Setting'],
                    ['file' => 'services.jpeg',           'title' => 'Our Services Overview'],
                    ['file' => 'services2.jpeg',          'title' => 'Service Delivery Showcase'],
                ];
                foreach ($album5 as $img): ?>
                <div class="col-lg-4 col-md-6 reveal-up">
                    <div class="gallery-card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.09); cursor: zoom-in;" onclick="openLightbox('/public/images/<?php echo e($img['file']); ?>', '<?php echo e($img['title']); ?>')">
                        <img src="/public/images/<?php echo e($img['file']); ?>" alt="<?php echo e($img['title']); ?>" style="width: 100%; height: 260px; object-fit: cover; display: block; transition: transform 0.4s ease;">
                        <div style="background: white; padding: 12px 16px;">
                            <p style="margin: 0; color: #2D1A4A; font-weight: 600; font-size: 0.9rem;"><?php echo e($img['title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Video ── -->
        <div class="reveal-up">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div style="width: 48px; height: 48px; background: #FEE2E2; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i class="fas fa-video" style="color: #DC2626; font-size: 1.3rem;"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.75rem; margin: 0;">Service in Motion</h2>
                    <p style="color: #6B7280; margin: 0; font-size: 0.92rem;">A video glimpse of SHENA Companion delivering a dignified farewell ceremony — compassion in every detail.</p>
                </div>
            </div>
            <div style="height: 3px; background: linear-gradient(90deg, #DC2626, #7F3D9E); border-radius: 2px; margin-bottom: 24px;"></div>
            <div style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.1);">
                <video controls style="width: 100%; max-height: 560px; background: #000; display: block;">
                    <source src="/public/images/farewell-service-video.mp4" type="video/mp4">
                </video>
            </div>
        </div>

    </div>
</section>

<!-- Lightbox Modal -->
<div id="galleryLightbox" onclick="closeLightbox()" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.92); z-index:9999; align-items:center; justify-content:center; padding: 20px;">
    <button onclick="closeLightbox()" style="position:absolute; top:20px; right:24px; background:none; border:none; color:white; font-size:2.5rem; cursor:pointer; line-height:1;">&times;</button>
    <div onclick="event.stopPropagation()" style="max-width:900px; width:100%;">
        <img id="lightboxImg" src="" alt="" style="width:100%; border-radius:12px; max-height:80vh; object-fit:contain;">
        <p id="lightboxCaption" style="color:rgba(255,255,255,0.75); text-align:center; margin-top:16px; font-size:1rem;"></p>
    </div>
</div>

<style>
    .gallery-card:hover img { transform: scale(1.04); }
    #galleryLightbox { display: none; }
    #galleryLightbox.open { display: flex; }
</style>
<script>
    function openLightbox(src, caption) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightboxCaption').textContent = caption;
        document.getElementById('galleryLightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
        document.getElementById('galleryLightbox').classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLightbox();
    });
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
