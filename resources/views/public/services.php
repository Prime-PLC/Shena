<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<style>
    #platinum .platinum-service-card {
        background: white;
        border-radius: 20px;
        padding: 40px 30px;
        height: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: left;
    }
    #platinum .platinum-service-icon {
        width: 70px;
        height: 70px;
        background: #F3E8FF;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
    }
    #platinum > .container > .row > div > div {
        background: white !important;
        border-radius: 20px !important;
        padding: 40px 30px !important;
        height: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: left !important;
    }
    #platinum > .container > .row > div {
        max-width: none !important;
    }
    #platinum > .container > .row > div > div > div:first-child {
        width: 70px !important;
        height: 70px !important;
        background: #F3E8FF !important;
        border-radius: 16px !important;
        margin: 0 0 24px !important;
    }
    #platinum > .container > .row > div > div h4 { color: #1A1A1A !important; margin-bottom: 12px !important; }
    #platinum > .container > .row > div > div p { color: #6B7280 !important; margin-bottom: 0 !important; }
    #platinum > .container > .text-center {
        text-align: left !important;
        margin-bottom: 3rem !important;
    }
    #platinum > .container > .text-center p:first-child {
        margin-bottom: 12px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        letter-spacing: 1.5px !important;
    }
    #platinum > .container > .text-center h2 {
        font-family: 'Playfair Display', serif !important;
        font-size: 3rem !important;
        font-weight: 700 !important;
        color: #1A1A1A !important;
        margin-bottom: 8px !important;
    }
    #platinum > .container > .text-center p:last-child {
        color: #6B7280 !important;
        font-size: 1rem !important;
        max-width: 780px !important;
        margin: 0 !important;
        line-height: 1.6 !important;
        text-align: justify !important;
    }
    section:not(#platinum) > .container > .mb-5 > p {
        text-align: justify;
    }
    @media (max-width: 575.98px) {
        #platinum > .container > .text-center h2 { font-size: 2.2rem !important; }
    }
    @media (max-width: 575.98px) {
        #platinum .platinum-service-card { padding: 30px 24px; }
    }
</style>

<!-- Services Hero Section -->
<section style="position: relative; min-height: 500px; display: flex; align-items: center; background: linear-gradient(rgba(0, 0, 0, 0.50), rgba(0, 0, 0, 0.50)), url('/public/images/services2.jpeg') center/cover no-repeat;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center text-white">
                <h1 class="mb-4" style="font-size: 3.5rem; font-weight: 700; font-family: 'Playfair Display', serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                    Our Compassionate Services
                </h1>
                <p class="lead mb-5" style="font-size: 1.2rem; max-width: 800px; margin: 0 auto 40px; line-height: 1.6; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                    Providing dignified send-offs and professional support during your most difficult times with grace and respect.
                </p>
                <a href="/membership" class="btn btn-lg" style="background-color: #7F3D9E; color: white; padding: 14px 40px; border-radius: 30px; font-weight: 600; border: none; box-shadow: 0 4px 12px rgba(127, 61, 158, 0.4); text-decoration: none;">
                    View Our Packages
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section style="padding: 80px 0; background: #F7F7F9;">
    <div class="container">
        <!-- Section Header -->
        <div class="mb-5">
            <h2 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #1A1A1A;">
                Last Respect Services
            </h2>
            <p style="color: #6B7280; font-size: 1rem; max-width: 780px; margin: 0;">
                These are the practical funeral arrangements SHENA coordinates for a covered family. Basic is the foundation: when a valid claim is approved, our team works with service providers to organize the items below instead of leaving the family to arrange everything alone.
            </p>
        </div>

        <!-- Services Grid -->
        <div class="row g-4 mb-5">
            <!-- Mortuary Bill -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-briefcase-medical" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Mortuary Bill
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Full coverage for mortuary storage for up to 14 days, ensuring a peaceful transition period.
                    </p>
                </div>
            </div>

            <!-- Body Dressing -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-user-tie" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Body Dressing
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Professional and respectful body preparation by our team of experts, handled with the utmost care.
                    </p>
                </div>
            </div>

            <!-- Body Transportation -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-truck" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Body Transportation
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Secure and well-maintained specialized vehicles for dignified transit from morgue to the final resting place.
                    </p>
                </div>
            </div>

            <!-- Executive Coffin -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-box" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Executive Coffin
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        A selection of high-quality coffins featuring refined craftsmanship and elegant finishes.
                    </p>
                </div>
            </div>

            <!-- Burial Gear -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-tent" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Burial Gear
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Complete provision of lowering gear, trolleys, and high-quality gazebo tents for the service.
                    </p>
                </div>
            </div>

            <!-- Seating -->
            <div class="col-lg-4 col-md-6">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin-bottom: 24px;">
                        <i class="fas fa-chair" style="color: #7F3D9E; font-size: 2rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Seating
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Organized seating arrangement with 100 comfortable chairs to accommodate family and mourners.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>

<section id="platinum" style="padding:80px 0;background:#F7F7F9;border-top:1px solid #E5E7EB;"><div class="container"><div class="text-center mb-4"><h2 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#1A1A1A;margin-bottom:8px;">SHENA Platinum</h2><p style="color:#6B7280;font-size:.95rem;margin:0 auto;max-width:650px;line-height:1.6;">A separate add-on to SHENA Basic providing inpatient bed-cover support for selected covered people.</p></div><div class="row g-4 justify-content-center"><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-hospital" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Inpatient Bed-Cover</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Up to 20 inpatient bed-cover days per selected person each calendar year.</p></div></div><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-user-check" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Selected People</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Choose yourself, registered dependants, or eligible corporate members individually.</p></div></div><div class="col-lg col-md-4 col-sm-6" style="max-width:220px;"><div class="text-center" style="padding:20px;"><div style="width:70px;height:70px;background:#F3E8FF;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;"><i class="fas fa-calendar-check" style="color:#7F3D9E;font-size:2rem;"></i></div><h4 style="color:#2D3748;font-weight:600;font-size:1.1rem;margin-bottom:12px;">Annual Allowance</h4><p style="color:#718096;font-size:.9rem;line-height:1.6;">Active, mature cover provides an annual inpatient support allowance.</p></div></div></div><div class="text-center mt-3"><a href="/membership#platinum" style="color:#7F3D9E;font-weight:600;text-decoration:none;">View Platinum pricing alongside Basic <i class="fas fa-arrow-right"></i></a></div></div></section>

<!-- Service Terms & Conditions -->
<section style="padding: 0 0 64px; background: #F7F7F9;">
    <div class="container">
        <div style="background: linear-gradient(135deg, #F3E8FF 0%, #EDE9FE 100%); border-radius: 20px; padding: 40px; border-left: 5px solid #7F3D9E; position: relative;">
            <div style="display: flex; gap: 24px; align-items: start;">
                <div style="flex-shrink: 0;"><div style="width: 60px; height: 60px; background: #7F3D9E; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fas fa-info" style="color: white; font-size: 1.5rem;"></i></div></div>
                <div style="flex: 1;">
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 700; color: #1A1A1A;">Service Terms & Conditions</h3>
                    <p style="color: #4A5568; line-height: 1.7; font-size: 1rem; margin-bottom: 16px;">Please note that SHENA Companion covers mortuary fees for a maximum of 14 days. <strong style="color: #7F3D9E;">Members are responsible</strong> for all hospital and morgue admission fees upon arrival at the facility.</p>
                    <a href="/membership" style="color: #7F3D9E; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 1rem;">View Full Terms <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cash Benefit Teaser -->
<section class="reveal-exempt" style="padding: 64px 0; background: linear-gradient(135deg, #1A0F2E 0%, #2D1A4A 60%, #3B1F5E 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 40px; justify-content: space-between;">

                    <!-- Left: Text -->
                    <div style="flex: 1; min-width: 260px;">
                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(201,166,89,0.15); border: 1px solid rgba(201,166,89,0.35); border-radius: 20px; padding: 5px 14px; margin-bottom: 18px;">
                            <i class="fas fa-star" style="color: #C9A659; font-size: 0.75rem;"></i>
                            <span style="color: #C9A659; font-size: 0.72rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;">Coming Soon</span>
                        </div>
                        <h2 style="font-family: 'Playfair Display', serif; color: white; font-size: clamp(1.7rem, 3vw, 2.4rem); font-weight: 700; margin: 0 0 14px; line-height: 1.25;">
                            Cash Benefit Cover<br>Up to <span style="color: #C9A659;">KES 500,000</span>
                        </h2>
                        <p style="color: rgba(255,255,255,0.72); font-size: 1rem; line-height: 1.7; margin: 0 0 10px; max-width: 480px;">
                            SHENA Companion is introducing a <strong style="color: rgba(255,255,255,0.92);">direct cash benefit service</strong> to complement our Last Respect Services. Members and their registered dependents will be eligible for a lump-sum cash pay-out of up to <strong style="color: #C9A659;">KES 500,000</strong>.
                        </p>
                        <p style="color: rgba(255,255,255,0.55); font-size: 0.88rem; margin: 0;">
                            Full details, eligibility criteria, and enrolment will be announced to all members. Contact our support team to register your interest.
                        </p>
                    </div>

                    <!-- Right: CTA card -->
                    <div style="flex-shrink: 0; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; padding: 32px 36px; text-align: center; min-width: 220px;">
                        <div style="width: 64px; height: 64px; background: rgba(201,166,89,0.18); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px;">
                            <i class="fas fa-hand-holding-usd" style="color: #C9A659; font-size: 1.7rem;"></i>
                        </div>
                        <p style="color: rgba(255,255,255,0.6); font-size: 0.78rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; margin: 0 0 6px;">Benefit Amount</p>
                        <p style="color: #C9A659; font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; margin: 0 0 20px; line-height: 1;">Up to<br>KES 500,000</p>
                        <a href="/contact" style="display: block; background: #C9A659; color: #1A0F2E; border-radius: 10px; padding: 11px 24px; font-weight: 700; font-size: 0.9rem; text-decoration: none; margin-bottom: 10px;">
                            <i class="fas fa-envelope me-1"></i> Enquire Now
                        </a>
                        <a href="tel:<?php echo defined('ADMIN_PHONE') ? htmlspecialchars(ADMIN_PHONE) : '+254748585067'; ?>" style="display: block; color: rgba(255,255,255,0.65); font-size: 0.82rem; text-decoration: none;">
                            <i class="fas fa-phone me-1"></i> <?php echo defined('ADMIN_PHONE') ? htmlspecialchars(ADMIN_PHONE) : '+254748585067'; ?>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div style="background: linear-gradient(135deg, #9C27B0 0%, #7F3D9E 50%, #5E35B1 100%); border-radius: 30px; padding: 60px 50px; text-align: center; box-shadow: 0 20px 60px rgba(127, 61, 158, 0.3);">
                    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; line-height: 1.2; color: white;">
                        Ready to secure your family's peace of mind?
                    </h2>
                    <p class="mb-5" style="font-size: 1.2rem; line-height: 1.6; max-width: 700px; margin: 0 auto 40px; opacity: 0.95; color: white;">
                        Join SHENA Companion today and ensure a dignified farewell for your loved ones with our comprehensive services.
                    </p>
                    <a href="/register" class="btn btn-lg" style="background-color: #C9A659; color: #1A1A1A; padding: 16px 50px; border-radius: 10px; font-weight: 700; font-size: 1.1rem; border: none; box-shadow: 0 8px 20px rgba(201, 166, 89, 0.3); text-decoration: none; text-transform: uppercase; letter-spacing: 1px;">
                        REGISTER NOW
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
