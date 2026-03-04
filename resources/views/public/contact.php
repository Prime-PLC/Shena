<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Contact Hero Section -->
<section style="padding: 80px 0; background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=1600&q=80') center/cover no-repeat; border-radius: 30px; margin: 40px auto; max-width: 95%;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center text-white">
                <h1 class="mb-4" style="font-size: 4rem; font-weight: 700; font-family: 'Playfair Display', serif; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                    Get in Touch
                </h1>
                <p style="font-size: 1.1rem; font-style: italic; opacity: 0.95; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                    We are here to support the SHENA Companion community. Your voice matters to us.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Cards -->
<section style="padding: 80px 0; background: #F7F7F9;">
    <div class="container">
        <!-- Three Info Cards -->
        <div class="row g-4 mb-5">
            <!-- Office Address -->
            <div class="col-lg-4">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 60px; height: 60px; background: #F3E8FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-map-marker-alt" style="color: #7F3D9E; font-size: 1.5rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Office Address
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        P.O. Box 40148,<br>
                        40100-Kenya (HQ: Kisumu)
                    </p>
                </div>
            </div>

            <!-- Phone Numbers -->
            <div class="col-lg-4">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 60px; height: 60px; background: #F3E8FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-phone" style="color: #7F3D9E; font-size: 1.5rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Phone Numbers
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin-bottom: 12px;">
                        0748 585 067<br>
                        0748 585 071
                    </p>
                    <span style="background: #F3E8FF; color: #7F3D9E; padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">
                        24/7 EMERGENCY
                    </span>
                </div>
            </div>

            <!-- Digital Channels -->
            <div class="col-lg-4">
                <div style="background: white; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <div style="width: 60px; height: 60px; background: #F3E8FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <i class="fas fa-at" style="color: #7F3D9E; font-size: 1.5rem;"></i>
                    </div>
                    <h3 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">
                        Digital Channels
                    </h3>
                    <p style="color: #6B7280; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        info@shenacompanion.org<br>
                        www.shenacompanion.org
                    </p>
                </div>
            </div>
        </div>

        <!-- Contact Form & M-Pesa Support -->
        <div class="row g-5">
            <!-- Left: Contact Form -->
            <div class="col-lg-7">
                <div style="background: white; border-radius: 20px; padding: 50px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                    <h2 class="mb-4" style="font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: #1A1A1A;">
                        Send a Message
                    </h2>

                    <form action="/contact" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo e($csrf_token ?? ''); ?>">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="fullName" style="color: #1A1A1A; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Full Name</label>
                                <input type="text" class="form-control" id="fullName" name="full_name" placeholder="John Doe" required style="border: 1px solid #E5E7EB; border-radius: 10px; padding: 12px 16px;">
                            </div>
                            <div class="col-md-6">
                                <label for="phoneNumber" style="color: #1A1A1A; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Phone Number</label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phone" placeholder="+254 7XX XXX XXX" required style="border: 1px solid #E5E7EB; border-radius: 10px; padding: 12px 16px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="emailAddress" style="color: #1A1A1A; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Email Address</label>
                                <input type="email" class="form-control" id="emailAddress" name="email" placeholder="john@example.com" required style="border: 1px solid #E5E7EB; border-radius: 10px; padding: 12px 16px;">
                            </div>
                            <div class="col-md-6">
                                <label for="subject" style="color: #1A1A1A; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Subject</label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Inquiry" required style="border: 1px solid #E5E7EB; border-radius: 10px; padding: 12px 16px;">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="message" style="color: #1A1A1A; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="How can we help you?" required style="border: 1px solid #E5E7EB; border-radius: 10px; padding: 12px 16px;"></textarea>
                        </div>

                        <button type="submit" class="btn" style="background-color: #7F3D9E; color: white; padding: 14px 40px; border-radius: 10px; font-weight: 700; border: none; width: auto; display: inline-flex; align-items: center; gap: 8px;">
                            Submit Message <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: M-Pesa Support Card -->
            <div class="col-lg-5">
                <div style="background: linear-gradient(135deg, #9C27B0 0%, #7F3D9E 100%); border-radius: 20px; padding: 50px 40px; height: 100%; box-shadow: 0 10px 40px rgba(127, 61, 158, 0.3); display: flex; flex-direction: column; justify-content: center;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
                        <i class="fas fa-mobile-alt" style="color: white; font-size: 2rem;"></i>
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: white; margin: 0;">
                            M-Pesa Support
                        </h3>
                    </div>

                    <p style="color: rgba(255,255,255,0.9); font-style: italic; font-size: 1rem; margin-bottom: 40px;">
                        Quickly support our welfare initiatives via Lipa na M-Pesa.
                    </p>

                    <!-- Paybill Number -->
                    <div style="background: rgba(255,255,255,0.15); border-radius: 15px; padding: 30px; margin-bottom: 30px; backdrop-filter: blur(10px);">
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">
                            PAYBILL NUMBER
                        </p>
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <h2 style="font-family: 'Playfair Display', serif; font-size: 3.5rem; font-weight: 700; color: white; margin: 0; letter-spacing: 4px;">
                                4163987
                            </h2>
                            <button onclick="navigator.clipboard.writeText('4163987')" style="background: white; border: none; width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                <i class="fas fa-copy" style="color: #7F3D9E;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Account Name -->
                    <div style="background: rgba(255,255,255,0.15); border-radius: 15px; padding: 25px; margin-bottom: 40px; backdrop-filter: blur(10px);">
                        <p style="color: rgba(255,255,255,0.8); font-size: 0.85rem; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px;">
                            ACCOUNT NAME
                        </p>
                        <h4 style="font-weight: 700; color: white; margin: 0; font-size: 1.5rem; letter-spacing: 1px;">
                            SHENA COMPANION
                        </h4>
                    </div>

                    <!-- Tagline -->
                    <p style="color: rgba(255,255,255,0.9); font-style: italic; font-size: 1.1rem; text-align: center; margin: 0;">
                        "We Are Royal"
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Location Section -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <h2 class="mb-5" style="font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #1A1A1A;">
            Office Location
        </h2>

        <div style="background: #E5E7EB; border-radius: 20px; overflow: hidden; position: relative; height: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            <div id="map" style="width: 100%; height: 100%; border-radius: 20px;"></div>
            
            <!-- Map Marker Info Card -->
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; pointer-events: none;">
                <div style="background: white; border-radius: 12px; padding: 20px 30px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); text-align: center; pointer-events: auto;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <div style="width: 40px; height: 40px; background: #7F3D9E; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-map-marker-alt" style="color: white; font-size: 1.2rem;"></i>
                        </div>
                        <div style="text-align: left;">
                            <h5 style="margin: 0; font-weight: 700; color: #1A1A1A; font-size: 1.1rem;">SHENA Companion HQ</h5>
                            <p style="margin: 0; color: #6B7280; font-size: 0.85rem;">P.O. Box 4018, Kenya (HQ: Kisumu)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Map Overlay Text -->
            <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); padding: 12px 24px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 999;">
                <p style="margin: 0; color: #1A1A1A; font-size: 0.9rem; font-weight: 600;">Interactive Map View of SHENA Companion HQ</p>
            </div>
        </div>
    </div>
</section>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    // Initialize the map
    var map = L.map('map', {
        center: [-0.0917, 34.7680], // Kisumu coordinates
        zoom: 13,
        scrollWheelZoom: false
    });

    // Add OpenStreetMap tiles with custom styling
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom marker icon
    var customIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #7F3D9E; width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt" style="color: white; font-size: 20px; position: absolute; top: 8px; left: 12px; transform: rotate(45deg);"></i></div>',
        iconSize: [40, 40],
        iconAnchor: [20, 40]
    });

    // Add marker
    var marker = L.marker([-0.0917, 34.7680], {icon: customIcon}).addTo(map);

    // Add popup to marker
    marker.bindPopup('<div style="text-align: center;"><strong style="color: #7F3D9E;">SHENA Companion HQ</strong><br>P.O. Box 4018<br>Kenya (Kisumu)</div>');

    // Open popup by default
    marker.openPopup();

    // Enable scroll zoom on click
    map.on('click', function() {
        map.scrollWheelZoom.enable();
    });

    // Disable scroll zoom on mouse out
    map.on('mouseout', function() {
        map.scrollWheelZoom.disable();
    });
</script>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
