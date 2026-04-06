<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SHENA Companion Welfare Association – Kenya's trusted partner in funeral cover and community welfare.">
    <title>SHENA Companion Welfare Association</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #7F3D9E;
            --primary-dark: #6A2F87;
            --gold: #C9A659;
            --text-dark: #1a1a1a;
            --text-gray: #666;
            --border: #e5e5e5;
            --bg-light: #f8f9fa;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: white;
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 1.5rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .logo {
            height: 60px;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            align-items: center;
            margin: 0;
        }

        .nav-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            transition: background 0.2s;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            color: white;
        }

        /* Hero Section */
        .hero {
            padding: 5rem 0;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero .highlight {
            color: var(--primary);
        }

        .hero p {
            font-size: 1.2rem;
            color: var(--text-gray);
            margin-bottom: 2rem;
            max-width: 600px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid var(--primary);
            transition: all 0.2s;
        }

        .btn-secondary:hover {
            background: var(--primary);
            color: white;
        }

        /* Services Section */
        .services {
            padding: 5rem 0;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .section-subtitle {
            text-align: center;
            color: var(--text-gray);
            margin-bottom: 3rem;
            font-size: 1.1rem;
        }

        .service-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            height: 100%;
            transition: all 0.3s;
        }

        .service-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            transform: translateY(-4px);
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: var(--bg-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .service-icon i {
            font-size: 1.8rem;
            color: var(--primary);
        }

        .service-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }

        .service-card p {
            color: var(--text-gray);
            font-size: 0.95rem;
            margin: 0;
        }

        /* Stats Section */
        .stats {
            background: var(--primary);
            color: white;
            padding: 4rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* Contact Section */
        .contact {
            padding: 5rem 0;
            background: var(--bg-light);
        }

        .contact-info {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            height: 100%;
            border: 1px solid var(--border);
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .contact-icon {
            width: 45px;
            height: 45px;
            background: var(--bg-light);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .contact-icon i {
            color: var(--primary);
            font-size: 1.1rem;
        }

        .contact-text {
            flex: 1;
        }

        .contact-text strong {
            display: block;
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .contact-text a {
            color: var(--text-dark);
            text-decoration: none;
        }

        .contact-text a:hover {
            color: var(--primary);
        }

        .paybill-box {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 12px;
            padding: 2.5rem;
            text-align: center;
        }

        .paybill-label {
            font-size: 0.85rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .paybill-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .paybill-text {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Footer */
        .footer {
            background: #1a1a1a;
            color: white;
            padding: 3rem 0 1.5rem;
        }

        .footer-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            justify-content: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.2s;
        }

        .social-links a:hover {
            background: var(--gold);
            transform: translateY(-2px);
        }

        /* Mobile Navigation */
        @media (max-width: 991px) {
            .nav-links {
                display: none;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <img src="/public/images/shena-logo.png" alt="SHENA Logo" class="logo">
                <nav>
                    <ul class="nav-links d-none d-lg-flex">
                        <li><a href="#services">Services</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact</a></li>
                        <li><a href="#" class="btn-primary">Join Now</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Coming Soon Notice -->
    <div style="background: linear-gradient(135deg, #7F3D9E 0%, #6A2F87 100%); color: white; padding: 1rem 0; text-align: center;">
        <div class="container">
            <p style="margin: 0; font-weight: 600;"><i class="fas fa-info-circle"></i> Online Portal Coming Soon | This is an informative page</p>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1>Kenya's <span class="highlight">Royal Standard</span> in Community Welfare</h1>
                    <p>Providing dignified funeral cover and comprehensive welfare support to families across Kenya.</p>
                    <div class="hero-buttons">
                        <a href="#contact" class="btn-primary">Contact Us</a>
                        <a href="#services" class="btn-secondary">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="/public/images/background-image1.jpeg" alt="SHENA Community" class="img-fluid rounded" style="box-shadow: 0 12px 40px rgba(0,0,0,0.1);">
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <p class="section-subtitle">Comprehensive welfare solutions for your peace of mind</p>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h3>Funeral Cover</h3>
                        <p>Comprehensive funeral assistance and support during difficult times.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Community Support</h3>
                        <p>Strong network of support from fellow members across Kenya.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Financial Security</h3>
                        <p>Reliable financial protection for you and your loved ones.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h3>24/7 Support</h3>
                        <p>Round-the-clock assistance whenever you need it most.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Active Members</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">15+</div>
                        <div class="stat-label">Years of Service</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <div class="stat-number">98%</div>
                        <div class="stat-label">Satisfaction Rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="gallery" style="padding: 5rem 0; background: white;">
        <div class="container">
            <h2 class="section-title">Our Community</h2>
            <p class="section-subtitle">See our work and community in action</p>
            
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div style="border-radius: 12px; overflow: hidden; height: 200px; border: 2px solid #e5e5e5;">
                        <img src="/public/images/community.jpeg" alt="Community Event" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="border-radius: 12px; overflow: hidden; height: 200px; border: 2px solid #e5e5e5;">
                        <img src="/public/images/community-mobilization.jpeg" alt="Community Mobilization" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="border-radius: 12px; overflow: hidden; height: 200px; border: 2px solid #e5e5e5;">
                        <img src="/public/images/funeral-service1.jpeg" alt="Funeral Services" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="border-radius: 12px; overflow: hidden; height: 200px; border: 2px solid #e5e5e5;">
                        <img src="/public/images/sensitization1.jpeg" alt="Welfare Activities" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact" id="contact">
        <div class="container">
            <h2 class="section-title">Get in Touch</h2>
            <p class="section-subtitle">We're here to help. Reach out to us anytime.</p>
            
            <!-- Notify Me Box -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-6">
                    <div style="background: linear-gradient(135deg, #7F3D9E 0%, #6A2F87 100%); border-radius: 16px; padding: 2.5rem; text-align: center; color: white;">
                        <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Get Notified When We Launch</h3>
                        <p style="margin-bottom: 1.5rem; opacity: 0.9;">Be the first to know when our online portal goes live</p>
                        <form id="notifyForm" style="max-width: 400px; margin: 0 auto;">
                            <div style="display: flex; background: rgba(255,255,255,0.15); border-radius: 50px; padding: 5px; border: 1px solid rgba(255,255,255,0.3);">
                                <input type="email" id="emailInput" placeholder="Enter your email" required 
                                    style="flex: 1; background: transparent; border: none; outline: none; color: white; padding: 0.6rem 1.2rem; font-size: 0.95rem;">
                                <button type="submit" class="btn-primary" style="border-radius: 50px; padding: 0.6rem 1.5rem; white-space: nowrap;">
                                    Notify Me
                                </button>
                            </div>
                            <p id="formMessage" style="font-size: 0.85rem; margin-top: 0.75rem; opacity: 0.8;"></p>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="contact-info">
                        <h3 class="mb-4">Contact Information</h3>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Phone</strong>
                                <a href="tel:+254748585067">+254 748 585 067</a>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Email</strong>
                                <a href="mailto:info@shenacompanion.org">info@shenacompanion.org</a>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-text">
                                <strong>Head Office</strong>
                                Nairobi, Kenya<br>
                                <small style="color: #999;">Specific address will be updated soon</small>
                            </div>
                        </div>

                        <div class="social-links mt-4">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="paybill-box">
                        <div class="paybill-label">M-Pesa Paybill</div>
                        <div class="paybill-number">4163987</div>
                        <div class="paybill-text">SHENA Companion</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <ul class="footer-links">
                <li><a href="#services">Services</a></li>
                <li><a href="#about">About Us</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Terms of Service</a></li>
            </ul>
            
            <div class="footer-bottom">
                <p>&copy; 2026 SHENA Companion Welfare Association. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle notify form submission
        document.getElementById('notifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('emailInput').value;
            const message = document.getElementById('formMessage');
            
            // Simple validation
            if (email) {
                message.textContent = '✓ Thank you! We\'ll notify you when we launch.';
                message.style.color = '#C9A659';
                document.getElementById('emailInput').value = '';
            }
        });
    </script>
</body>
</html>