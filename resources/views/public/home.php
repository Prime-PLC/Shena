<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Hero Slideshow Section -->
<section id="heroSlideshow" style="position: relative; min-height: 620px; overflow: hidden;">

    <!-- Slides -->
    <?php
    $heroSlides = [
        [
            'image' => '/public/images/background-image1.jpeg',
            'heading' => 'Dignified Send-off,<br>Lasting Support.',
            'sub'     => 'A dedicated welfare association serving communities across Kenya with professional funeral cover and compassionate support.',
        ],
        [
            'image' => '/public/images/sensitization1.jpeg',
            'heading' => 'Together We Stand,<br>Together We Serve.',
            'sub'     => 'Mobilizing communities across Kenya to secure a peaceful and dignified farewell for every family member.',
        ],
        [
            'image' => '/public/images/services.jpeg',
            'heading' => 'Royal Care in<br>Every Farewell.',
            'sub'     => 'From mortuary to burial site, SHENA handles every detail with professionalism, grace, and the highest respect.',
        ],
        [
            'image' => '/public/images/hearse-service2.jpeg',
            'heading' => 'Professional Services,<br>Heartfelt Compassion.',
            'sub'     => 'Our specialized hearse fleet and expert team ensure a dignified final journey for your loved one.',
        ],
    ];
    foreach ($heroSlides as $i => $slide):
    ?>
    <div class="hero-slide <?php echo $i === 0 ? 'active' : ''; ?>"
         style="position: absolute; inset: 0; transition: opacity 1.2s ease, transform 1.2s ease; opacity: <?php echo $i === 0 ? '1' : '0'; ?>; transform: <?php echo $i === 0 ? 'scale(1)' : 'scale(1.04)'; ?>;">
        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(45,26,74,0.72) 0%, rgba(0,0,0,0.38) 100%), url('<?php echo $slide['image']; ?>') center/cover no-repeat;"></div>
    </div>
    <?php endforeach; ?>

    <!-- Slide Content -->
    <div style="position: relative; z-index: 10; min-height: 620px; display: flex; align-items: center;">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-10">
                    <?php foreach ($heroSlides as $i => $slide): ?>
                    <div class="hero-text <?php echo $i === 0 ? 'active' : ''; ?>"
                         style="position: absolute; left: 0; right: 0; transition: opacity 0.8s ease, transform 0.8s ease; opacity: <?php echo $i === 0 ? '1' : '0'; ?>; transform: <?php echo $i === 0 ? 'translateY(0)' : 'translateY(20px)'; ?>; pointer-events: <?php echo $i === 0 ? 'auto' : 'none'; ?>;">
                        <h1 class="mb-4" style="color: white; font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 700; text-shadow: 2px 2px 8px rgba(0,0,0,0.5); font-family: 'Playfair Display', serif; line-height: 1.2;">
                            <?php echo $slide['heading']; ?>
                        </h1>
                        <p class="lead mb-5" style="color: rgba(255,255,255,0.93); font-size: 1.1rem; max-width: 680px; margin: 0 auto 2rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.4); line-height: 1.7;">
                            <?php echo $slide['sub']; ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                    <!-- Buttons always visible -->
                    <div style="margin-top: 220px;" class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="/register" class="btn btn-lg" style="background-color: #7F3D9E; color: white; padding: 14px 40px; border-radius: 30px; font-weight: 600; border: none; box-shadow: 0 4px 16px rgba(127,61,158,0.5); transition: background 0.2s;">
                            Join Association
                        </a>
                        <a href="/about" class="btn btn-lg" style="background-color: rgba(255,255,255,0.15); color: white; padding: 14px 40px; border-radius: 30px; font-weight: 600; border: 2px solid rgba(255,255,255,0.7); backdrop-filter: blur(4px); transition: background 0.2s, color 0.2s; text-decoration: none;">
                            Our Benefits
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Slide Indicators -->
    <div style="position: absolute; bottom: 28px; left: 0; right: 0; z-index: 20; display: flex; justify-content: center; gap: 10px;">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <button class="hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $i; ?>)"
                style="width: <?php echo $i === 0 ? '32px' : '10px'; ?>; height: 10px; border-radius: 5px; background: <?php echo $i === 0 ? '#C9A659' : 'rgba(255,255,255,0.5)'; ?>; border: none; cursor: pointer; transition: all 0.4s ease; padding: 0;"></button>
        <?php endforeach; ?>
    </div>

    <!-- Prev / Next Arrows -->
    <button onclick="prevSlide()" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); z-index:20; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.3); color:white; width:46px; height:46px; border-radius:50%; font-size:1.1rem; cursor:pointer; backdrop-filter:blur(4px); transition:background 0.2s;" aria-label="Previous slide">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button onclick="nextSlide()" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); z-index:20; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.3); color:white; width:46px; height:46px; border-radius:50%; font-size:1.1rem; cursor:pointer; backdrop-filter:blur(4px); transition:background 0.2s;" aria-label="Next slide">
        <i class="fas fa-chevron-right"></i>
    </button>

</section>

<section style="padding:56px 0;background:#F7F7F9;border-top:1px solid #E5E7EB;border-bottom:1px solid #E5E7EB;"><div class="container"><div class="row align-items-center g-0"><div class="col-lg-5 pe-lg-5 pb-4 pb-lg-0"><span style="color:#7F3D9E;font-weight:700;letter-spacing:1.4px;">SHENA BASIC</span><p style="color:#2D1A4A;font-family:'Playfair Display',serif;font-size:1.45rem;line-height:1.3;margin:10px 0;">The cover that helps your family arrange a dignified funeral.</p><p style="color:#6B7280;line-height:1.7;margin:0;">Basic is where every SHENA membership begins, with practical funeral support when a covered loved one dies.</p></div><div class="col-lg-5 px-lg-5 py-4 py-lg-0" style="border-left:1px solid #D9D3DF;"><span style="color:#7F3D9E;font-weight:700;letter-spacing:1.4px;">SHENA PLATINUM</span><p style="color:#2D1A4A;font-family:'Playfair Display',serif;font-size:1.45rem;line-height:1.3;margin:10px 0;">Extra inpatient support for the people you choose.</p><p style="color:#6B7280;line-height:1.7;margin:0;">Add Platinum to Basic for up to 20 inpatient bed-cover days per selected person each year. It is optional and does not replace Basic.</p></div><div class="col-lg-2 text-lg-end pt-4 pt-lg-0 ps-lg-4"><a href="/membership#platinum" class="btn" style="background:#7F3D9E;color:#fff;margin-bottom:10px;">Compare covers</a><a href="/services#platinum" style="color:#7F3D9E;font-weight:600;display:block;">See services</a></div></div></div></section>

<script>
(function() {
    const total = <?php echo count($heroSlides); ?>;
    let current = 0;
    let timer;

    function activate(n) {
        const slides = document.querySelectorAll('.hero-slide');
        const texts  = document.querySelectorAll('.hero-text');
        const dots   = document.querySelectorAll('.hero-dot');

        slides[current].style.opacity = '0';
        slides[current].style.transform = 'scale(1.04)';
        texts[current].style.opacity  = '0';
        texts[current].style.transform = 'translateY(20px)';
        texts[current].style.pointerEvents = 'none';
        dots[current].style.width = '10px';
        dots[current].style.background = 'rgba(255,255,255,0.5)';
        dots[current].classList.remove('active');

        current = (n + total) % total;

        slides[current].style.opacity = '1';
        slides[current].style.transform = 'scale(1)';
        texts[current].style.opacity  = '1';
        texts[current].style.transform = 'translateY(0)';
        texts[current].style.pointerEvents = 'auto';
        dots[current].style.width = '32px';
        dots[current].style.background = '#C9A659';
        dots[current].classList.add('active');
    }

    function startTimer() {
        clearInterval(timer);
        timer = setInterval(function() { activate(current + 1); }, 5500);
    }

    window.nextSlide = function() { activate(current + 1); startTimer(); };
    window.prevSlide = function() { activate(current - 1); startTimer(); };
    window.goToSlide = function(n)  { if (n !== current) { activate(n); startTimer(); } };

    startTimer();
})();
</script>

<!-- Professional Care & Services Section -->
<section class="section" style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3" style="color: #2D1A4A; font-family: 'Playfair Display', serif; font-weight: 700; font-size: 2.5rem; position: relative; display: inline-block;">
                Professional Care & Services
                <span style="display: block; width: 80px; height: 3px; background: linear-gradient(90deg, #C9A659, #E8C879); margin: 15px auto 0;"></span>
            </h2>
            <p style="color: #718096; max-width: 700px; margin: 0 auto; font-size: 1rem; line-height: 1.6;">
                When a family needs support, SHENA helps coordinate the practical arrangements with care. Start with Basic funeral support, then explore Platinum if you would also like inpatient bed-cover support for selected family members.
            </p>
        </div>

        <div class="text-center mb-4">
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #1A1A1A; margin-bottom: 8px;">Last Respect Services</h3>
            <p style="color: #6B7280; font-size: 0.95rem; margin: 0;">These are the funeral arrangements SHENA coordinates directly with approved service providers, so your family can focus on one another during bereavement.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Mortuary Service -->
            <div class="col-lg col-md-4 col-sm-6" style="max-width: 220px;">
                <div class="text-center" style="padding: 20px;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-briefcase-medical" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h4 style="color: #2D3748; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px;">Mortuary</h4>
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.6;">
                        Professional preservation and care at Kenya's leading facilities.
                    </p>
                </div>
            </div>

            <!-- Dressing Service -->
            <div class="col-lg col-md-4 col-sm-6" style="max-width: 220px;">
                <div class="text-center" style="padding: 20px;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-user-tie" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h4 style="color: #2D3748; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px;">Dressing</h4>
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.6;">
                        Gentle grooming and presentation handled with total respect.
                    </p>
                </div>
            </div>

            <!-- Transport Service -->
            <div class="col-lg col-md-4 col-sm-6" style="max-width: 220px;">
                <div class="text-center" style="padding: 20px;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-truck" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h4 style="color: #2D3748; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px;">Transport</h4>
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.6;">
                        Modern, reliable hearses for a dignified final journey.
                    </p>
                </div>
            </div>

            <!-- Coffins Service -->
            <div class="col-lg col-md-4 col-sm-6" style="max-width: 220px;">
                <div class="text-center" style="padding: 20px;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-box" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h4 style="color: #2D3748; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px;">Coffins</h4>
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.6;">
                        Executive high-quality designs crafted to royal standards.
                    </p>
                </div>
            </div>

            <!-- Logistics Service -->
            <div class="col-lg col-md-4 col-sm-6" style="max-width: 220px;">
                <div class="text-center" style="padding: 20px;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        <i class="fas fa-boxes" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h4 style="color: #2D3748; font-weight: 600; font-size: 1.1rem; margin-bottom: 12px;">Logistics</h4>
                    <p style="color: #718096; font-size: 0.9rem; line-height: 1.6;">
                        Full event support including premium tents and chairs.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>

<section id="platinum" style="padding:72px 0;background:#F7F7F9;border-top:1px solid #E5E7EB;"><div class="container"><div class="text-center mb-4"><h2 style="font-family:'Playfair Display',serif;font-size:2.2rem;font-weight:700;color:#2D1A4A;margin-bottom:8px;">SHENA Platinum</h2><p style="color:#6B7280;font-size:.95rem;max-width:650px;margin:0 auto;line-height:1.6;">A separate add-on to SHENA Basic providing inpatient bed-cover support for selected covered people.</p></div><div class="row g-4 justify-content-center"><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-hospital" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Inpatient Bed-Cover</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Up to 20 inpatient bed-cover days per selected person each calendar year.</p></div></div><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-user-check" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Selected People</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Choose yourself, registered dependants, or eligible corporate members individually.</p></div></div><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-calendar-check" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Annual Allowance</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Active, mature cover provides an annual inpatient support allowance.</p></div></div></div><div class="text-center mt-3"><a href="/membership#platinum" style="color:#7F3D9E;font-weight:600;text-decoration:none;">See Platinum and Basic pricing <i class="fas fa-arrow-right"></i></a></div></div></section>

<!-- Why We Are The Royal Choice Section -->
<section style="padding: 80px 0; background: #F7FAFC;">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Column: Content -->
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="mb-3">
                    <span style="color: #7F3D9E; font-size: 0.85rem; font-weight: 600; letter-spacing: 1.5px;">OUR EXCELLENCE</span>
                </div>
                <h2 class="mb-4" style="font-family: 'Playfair Display', serif; line-height: 1.2;">
                    <span style="color: #2D1A4A; font-size: 2.5rem; font-weight: 400; font-style: italic;">Why We Are</span><br>
                    <span style="color: #7F3D9E; font-size: 2.5rem; font-weight: 700;">The Royal Choice</span>
                </h2>
                <p class="mb-4" style="color: #4A5568; line-height: 1.8; font-size: 1rem;">
                    SHENA Companion stands as a pillar of strength for communities across Kenya. We combine traditional values with modern professionalism to ensure peace of mind for our members.
                </p>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-3">
                            <div>
                                <i class="fas fa-clock" style="color: #7F3D9E; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="font-size: 1.05rem; font-weight: 600; color: #2D3748; margin-bottom: 8px;">Immediate Response</h5>
                                <p style="color: #718096; font-size: 0.9rem; line-height: 1.5; margin: 0;">
                                    Our team is available 24/7 to handle claims immediately.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-3">
                            <div>
                                <i class="fas fa-users" style="color: #7F3D9E; font-size: 24px;"></i>
                            </div>
                            <div>
                                <h5 style="font-size: 1.05rem; font-weight: 600; color: #2D3748; margin-bottom: 8px;">Community Spirit</h5>
                                <p style="color: #718096; font-size: 0.9rem; line-height: 1.5; margin: 0;">
                                    Built by and for communities across Kenya.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="/about" class="btn" style="background-color: transparent; color: #7F3D9E; padding: 12px 32px; border: 2px solid #7F3D9E; border-radius: 30px; font-weight: 600; text-decoration: none; display: inline-block;">
                    Learn More About Us
                </a>
            </div>

            <!-- Right Column: Image with Badge -->
            <div class="col-lg-6">
                <div class="position-relative">
                    <img src="/public/images/community.jpeg" 
                        alt="SHENA community" 
                         class="img-fluid" 
                         style="width: 100%; height: auto; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                    <div class="position-absolute" style="bottom: 30px; left: 50%; transform: translateX(-50%);">
                        <div class="bg-white rounded-3 p-4 shadow-lg text-center" style="min-width: 220px;">
                            <div style="font-size: 3rem; font-weight: 700; color: #7F3D9E; line-height: 1; font-style: italic; font-family: 'Playfair Display', serif;">
                                100%
                            </div>
                            <div style="font-size: 0.8rem; font-weight: 600; color: #2D3748; letter-spacing: 1.5px; margin-top: 8px;">
                                CLAIMS HONORED
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/partials/impact-gallery.php'; ?>

<!-- Call to Action Section -->
<section style="padding: 60px 20px; background: white;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div style="background: linear-gradient(135deg, #7F3D9E 0%, #6B2D8A 100%); border-radius: 30px; padding: 60px 50px; box-shadow: 0 20px 60px rgba(127, 61, 158, 0.25); position: relative; overflow: hidden;">
                    <!-- Decorative Background Circles -->
                    <div style="position: absolute; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; top: -100px; left: -100px;"></div>
                    <div style="position: absolute; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; bottom: -80px; right: -80px;"></div>
                    
                    <div class="text-center" style="position: relative; z-index: 2;">
                        <h2 class="mb-4" style="color: white; font-size: 2.8rem; font-weight: 600; font-family: 'Playfair Display', serif; font-style: italic; line-height: 1.2;">
                            Step into Royal Protection
                        </h2>
                        <p class="mb-5" style="color: rgba(255, 255, 255, 0.95); font-size: 1.05rem; max-width: 700px; margin: 0 auto 40px; line-height: 1.6;">
                            Secure your future and that of your loved ones today. Joining SHENA Companion is a commitment to dignity and community welfare.
                        </p>
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="/register" class="btn btn-lg" style="background: linear-gradient(135deg, #C9A659 0%, #E8C879 100%); color: #2D1A4A; padding: 14px 40px; border-radius: 30px; font-weight: 600; border: none; box-shadow: 0 4px 15px rgba(201, 166, 89, 0.4); text-decoration: none;">
                                Get Started Now
                            </a>
                            <a href="/contact" class="btn btn-lg" style="background-color: transparent; color: white; padding: 14px 40px; border: 2px solid white; border-radius: 30px; font-weight: 600; text-decoration: none;">
                                Talk to Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
