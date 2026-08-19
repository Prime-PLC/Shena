<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Membership Hero Section -->
<section style="position: relative; min-height: 480px; display: flex; align-items: center; background: linear-gradient(rgba(45,26,74,0.72), rgba(45,26,74,0.72)), url('/public/images/community.jpeg') center/cover no-repeat;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center text-white">
                <span style="background: rgba(201,166,89,0.25); color: #C9A659; padding: 8px 22px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; display: inline-block; margin-bottom: 24px; border: 1px solid rgba(201,166,89,0.4);">
                    PREMIUM COVERAGE
                </span>
                <h1 class="mb-4" style="font-family: 'Playfair Display', serif; font-size: clamp(2.2rem, 5vw, 3.5rem); font-weight: 700; line-height: 1.2; text-shadow: 2px 2px 6px rgba(0,0,0,0.4);">
                    Choose Your Royal Protection
                </h1>
                <p class="mb-5" style="font-size: 1.1rem; line-height: 1.7; max-width: 660px; margin: 0 auto 36px; opacity: 0.92; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                    Comprehensive coverage tailored for all ages and family sizes. Secure your future and your loved ones' peace of mind with our flexible monthly contribution plans.
                </p>
                <a href="#packages" class="btn btn-lg" style="background-color: #C9A659; color: #1A1A1A; padding: 16px 50px; border-radius: 10px; font-weight: 700; font-size: 1.1rem; border: none; box-shadow: 0 8px 20px rgba(201,166,89,0.35); text-decoration: none;">
                    View All Packages
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Package Options Section -->
<section id="packages" style="padding: 80px 0; background: #F7F7F9;">
    <div class="container">
        <div style="max-width: 980px; margin: 0 auto 34px;">
            <span style="color: #7F3D9E; font-size: 0.82rem; font-weight: 700; letter-spacing: 1.5px;">SHENA BASIC</span>
            <p style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.45rem; line-height: 1.3; margin: 10px 0;">Your main funeral cover for yourself and your family.</p>
            <p style="color: #6B7280; line-height: 1.7; margin: 0;">Choose an individual, family, or Executive package below. Basic is the cover that supports your family with funeral arrangements when a covered person dies.</p>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <ul class="nav nav-tabs mb-4" id="packageTabs" role="tablist" style="border-bottom: 2px solid #E5E7EB;">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab" style="color: #7F3D9E; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">
                            Individual
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">
                            Family Plans
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="executive-tab" data-bs-toggle="tab" data-bs-target="#executive" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">
                            Executive
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="packageTabContent">
                    <div class="tab-pane fade show active" id="individual" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin: 0;">SHENA Basic Packages</h2>
                                <span style="color: #6B7280; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">PRICING 2026</span>
                            </div>

                            <table class="table" style="margin-bottom: 40px;">
                                <thead style="background: #F7F7F9;">
                                    <tr>
                                        <th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Age Bracket</th>
                                        <th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th>
                                        <th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none; text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">Below 70 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">100</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=individual&bracket=below_70" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">71 - 80 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">350</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=individual&bracket=71_80" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">81 - 90 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">450</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=individual&bracket=81_90" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">91 - 100 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">650</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=individual&bracket=91_100" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="family" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                            <!-- Family (couple) -->
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1A1A1A; margin-bottom: 6px;">Family Plan <span style="font-size: 0.8rem; background: #F3E8FF; color: #7F3D9E; border-radius: 20px; padding: 4px 12px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE</span></h3>
                            <p style="color: #6B7280; margin-bottom: 16px;">Principal member + spouse. Flat rate regardless of age.</p>
                            <table class="table" style="margin-bottom: 36px;">
                                <thead style="background: #F7F7F9;">
                                    <tr>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Coverage</th>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th>
                                        <th style="padding: 14px; border: none; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">Any Age (Flat Rate)</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">150</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=family" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Extended Family 1 -->
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1A1A1A; margin-bottom: 6px;">Extended Family 1 <span style="font-size: 0.8rem; background: #F3E8FF; color: #7F3D9E; border-radius: 20px; padding: 4px 12px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE + CHILDREN + PARENTS</span></h3>
                            <p style="color: #6B7280; margin-bottom: 16px;">Rate based on the age of the oldest parent being covered.</p>
                            <table class="table" style="margin-bottom: 36px;">
                                <thead style="background: #F7F7F9;">
                                    <tr>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Oldest Parent Age</th>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th>
                                        <th style="padding: 14px; border: none; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">Below 70 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">250</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_1&bracket=below_70" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">70 - 80 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">350</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_1&bracket=70_80" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">81 - 90 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">450</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_1&bracket=81_90" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">91 - 100 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">650</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_1&bracket=91_100" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Extended Family 2 -->
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: #1A1A1A; margin-bottom: 6px;">Extended Family 2 <span style="font-size: 0.8rem; background: #FEF3E8; color: #D97706; border-radius: 20px; padding: 4px 12px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE + CHILDREN + PARENTS + IN-LAWS</span></h3>
                            <p style="color: #6B7280; margin-bottom: 16px;">Rate based on the age of the oldest parent or in-law being covered.</p>
                            <table class="table" style="margin-bottom: 0;">
                                <thead style="background: #F7F7F9;">
                                    <tr>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Oldest Parent / In-law Age</th>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th>
                                        <th style="padding: 14px; border: none; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">Below 70 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #D97706;">300</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_2&bracket=below_70" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">71 - 80 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #D97706;">400</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_2&bracket=71_80" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">81 - 90 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #D97706;">550</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_2&bracket=81_90" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 18px; font-weight: 600; color: #1A1A1A;">91 - 100 Years</td>
                                        <td style="padding: 18px; font-size: 1.5rem; font-weight: 700; color: #D97706;">650</td>
                                        <td style="padding: 18px; text-align: right;"><a href="/register?plan=extended_family_2&bracket=91_100" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="executive" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin: 0;">Executive Package</h2>
                                <span style="color: #D97706; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px;">PREMIUM INDIVIDUAL</span>
                            </div>
                            <p style="color: #6B7280; margin-bottom: 24px;">Priority handling, enhanced support, and premium services for individuals.</p>
                            <table class="table" style="margin-bottom: 0;">
                                <thead style="background: #FEF3E8;">
                                    <tr>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Age Bracket</th>
                                        <th style="padding: 14px; color: #6B7280; font-size: 0.8rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th>
                                        <th style="padding: 14px; border: none; text-align: right;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">Below 70 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #D97706;">300</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=executive&bracket=below_70" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">70 Years and Above</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #D97706;">500</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register?plan=executive&bracket=above_70" class="btn" style="background-color: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div style="position: sticky; top: 100px;">
                    <div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                            <i class="fas fa-shield-alt" style="color: #7F3D9E; font-size: 1.5rem;"></i>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A; margin: 0;">Policy at a Glance</h3>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <p style="color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px;">Maturity Period</p>
                            <div style="background: #F7F7F9; border-radius: 12px; padding: 20px; display: flex; justify-content: space-around;">
                                <div style="text-align: center;">
                                    <p style="color: #6B7280; font-size: 0.85rem; margin-bottom: 8px;">Under 80</p>
                                    <p style="color: #7F3D9E; font-size: 2rem; font-weight: 700; margin: 0;">4<span style="font-size: 1rem; font-weight: 500;"> mos</span></p>
                                </div>
                                <div style="width: 1px; background: #E5E7EB;"></div>
                                <div style="text-align: center;">
                                    <p style="color: #6B7280; font-size: 0.85rem; margin-bottom: 8px;">81-100</p>
                                    <p style="color: #7F3D9E; font-size: 2rem; font-weight: 700; margin: 0;">5<span style="font-size: 1rem; font-weight: 500;"> mos</span></p>
                                </div>
                            </div>
                        </div>

                        <div style="margin-bottom: 30px;">
                            <p style="color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px;">Grace Period</p>
                            <div style="background: linear-gradient(135deg, #F3E8FF 0%, #EDE9FE 100%); border-radius: 12px; padding: 20px;">
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                                    <i class="fas fa-calendar-check" style="color: #7F3D9E; font-size: 1.5rem;"></i>
                                    <p style="color: #7F3D9E; font-size: 1.75rem; font-weight: 700; margin: 0;">60 Days</p>
                                </div>
                                <p style="color: #6B7280; font-size: 0.85rem; margin: 0;">Arrears window</p>
                            </div>
                        </div>

                        <a href="/policy-booklet" class="btn" style="background: white; color: #7F3D9E; border: 2px solid #7F3D9E; padding: 14px 0; border-radius: 10px; font-weight: 700; text-decoration: none; width: 100%; display: block; text-align: center;">Read Policy Booklet</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SHENA Platinum Packages -->
<section id="platinum-packages" style="padding: 80px 0; background: #F7F7F9; border-top: 1px solid #E5E7EB;">
    <div class="container">
        <div style="max-width: 980px; margin: 0 auto 34px;">
            <span style="color: #7F3D9E; font-size: 0.82rem; font-weight: 700; letter-spacing: 1.5px;">SHENA PLATINUM</span>
            <p style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.45rem; line-height: 1.3; margin: 10px 0;">Extra inpatient support for selected family members.</p>
            <p style="color: #6B7280; line-height: 1.7; margin: 0;">SHENA Platinum provides inpatient support for members and their loved ones. It is a complimentary service to SHENA BASIC. It pays for daily bed charges for members and their dependants. Up to 20 inpatient bed-cover days per selected person each year.</p>
        </div>
        <div class="row">
            <div class="col-lg-8">
                <ul class="nav nav-tabs mb-4" id="platinumPackageTabs" role="tablist" style="border-bottom: 2px solid #E5E7EB;">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="platinum-individual-tab" data-bs-toggle="tab" data-bs-target="#platinum-individual" type="button" role="tab" style="color: #7F3D9E; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">Individual</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="platinum-family-tab" data-bs-toggle="tab" data-bs-target="#platinum-family" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">Family Plans</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="platinum-executive-tab" data-bs-toggle="tab" data-bs-target="#platinum-executive" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">Executive</button></li>
                </ul>

                <div class="tab-content" id="platinumPackageTabContent">
                    <div class="tab-pane fade show active" id="platinum-individual" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;"><h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin: 0;">SHENA Platinum Packages</h2><span style="color: #7F3D9E; font-size: 0.85rem; font-weight: 600; letter-spacing: 1px;">OPTIONAL ADD-ON</span></div>
                            <table class="table" style="margin-bottom: 0;"><thead style="background: #F7F7F9;"><tr><th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Age Bracket</th><th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none;">Monthly Rate (KES)</th><th style="padding: 16px; color: #6B7280; font-size: 0.85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; border: none; text-align: right;">Action</th></tr></thead><tbody>
                                <tr style="border-bottom: 1px solid #E5E7EB;"><td style="padding: 20px; font-weight: 600;">Below 70 Years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">300</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                                <tr style="border-bottom: 1px solid #E5E7EB;"><td style="padding: 20px; font-weight: 600;">71 - 80 Years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">550</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                                <tr style="border-bottom: 1px solid #E5E7EB;"><td style="padding: 20px; font-weight: 600;">81 - 90 Years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">650</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                                <tr><td style="padding: 20px; font-weight: 600;">91 - 100 Years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">850</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                            </tbody></table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="platinum-family" role="tabpanel"><div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);"><h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin-bottom: 8px;">SHENA Platinum Family Plans</h2><p style="color: #6B7280; margin-bottom: 24px;">Platinum is priced per family package according to the age band shown below.</p><table class="table" style="margin-bottom: 0;"><thead style="background: #F7F7F9;"><tr><th style="padding: 14px; border: none;">Family package and age band</th><th style="padding: 14px; border: none;">Monthly rate (KES)</th><th style="padding: 14px; border: none; text-align: right;">Action</th></tr></thead><tbody>
                        <tr><td colspan="3" style="padding: 14px 0 8px; border: none;"><h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #1A1A1A; margin: 0 0 4px;">Family Plan <span style="font-size: .7rem; background: #F3E8FF; color: #7F3D9E; border-radius: 20px; padding: 4px 10px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE</span></h3><p style="color: #6B7280; margin: 0; font-size: .85rem;">Principal member + spouse.</p></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple below 70 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">350</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple below 70 years & children below 18</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">400</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td colspan="3" style="padding: 20px 0 8px; border: none;"><h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #1A1A1A; margin: 0 0 4px;">Extended Family 1 <span style="font-size: .7rem; background: #F3E8FF; color: #7F3D9E; border-radius: 20px; padding: 4px 10px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE + CHILDREN + PARENTS</span></h3><p style="color: #6B7280; margin: 0; font-size: .85rem;">Rate based on the age of the oldest parent being covered.</p></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents below 70</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">450</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children, parents & in-laws below 70</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">500</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td colspan="3" style="padding: 20px 0 8px; border: none;"><h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #1A1A1A; margin: 0 0 4px;">Extended Family 2 <span style="font-size: .7rem; background: #FEF3E8; color: #D97706; border-radius: 20px; padding: 4px 10px; font-family: sans-serif; font-weight: 700; vertical-align: middle;">COUPLE + CHILDREN + PARENTS + IN-LAWS</span></h3><p style="color: #6B7280; margin: 0; font-size: .85rem;">Rate based on the age of the oldest parent or in-law being covered.</p></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents, 71-80 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">550</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents & in-laws, 71-80 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">600</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents, 81-90 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">650</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents & in-laws, 81-90 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">750</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents, 91-100 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">850</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                        <tr><td style="padding: 14px; font-weight: 600;">Couple, children & parents & in-laws, 91-100 years</td><td style="padding: 14px; color: #7F3D9E; font-weight: 700;">850</td><td style="padding: 14px; text-align: right;"><a href="/login" class="btn" style="background: #7F3D9E; color: white; padding: 8px 20px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr>
                    </tbody></table></div></div>
                    <div class="tab-pane fade" id="platinum-executive" role="tabpanel"><div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);"><h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin-bottom: 8px;">SHENA Platinum Executive</h2><p style="color: #6B7280; margin-bottom: 24px;">Age-based Platinum add-on rates for Executive membership.</p><table class="table" style="margin-bottom: 0;"><thead style="background: #FEF3E8;"><tr><th style="padding: 16px; border: none;">Executive covered person</th><th style="padding: 16px; border: none;">Monthly rate (KES)</th><th style="padding: 16px; border: none; text-align: right;">Action</th></tr></thead><tbody><tr><td style="padding: 20px; font-weight: 600;">Executive individual below 70 years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #D97706;">500</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr><tr><td style="padding: 20px; font-weight: 600;">Executive individual above 70 years</td><td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #D97706;">700</td><td style="padding: 20px; text-align: right;"><a href="/login" class="btn" style="background: #D97706; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none;">Get Started</a></td></tr></tbody></table></div></div>
                </div>
            </div>
            <div class="col-lg-4"><div style="position: sticky; top: 100px;"><div style="background: white; border-radius: 20px; padding: 35px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);"><div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;"><i class="fas fa-hospital" style="color: #7F3D9E; font-size: 1.5rem;"></i><h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; margin: 0;">Platinum at a Glance</h3></div><p style="color: #6B7280; font-size: .85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px;">ANNUAL INPATIENT SUPPORT</p><div style="background: #F3E8FF; border-radius: 12px; padding: 20px; margin-bottom: 24px;"><p style="color: #7F3D9E; font-size: 2rem; font-weight: 700; margin: 0;">20 <span style="font-size: 1rem; font-weight: 500;">days</span></p><p style="color: #6B7280; font-size: .85rem; margin: 0;">per selected person each year</p></div><p style="color: #6B7280; font-size: .85rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px;">MATURITY PERIOD</p><div style="background: #F7F7F9; border-radius: 12px; padding: 20px; display: flex; justify-content: space-around;"><div style="text-align: center;"><p style="color: #6B7280; font-size: .85rem; margin-bottom: 8px;">Under 60</p><p style="color: #7F3D9E; font-size: 1.6rem; font-weight: 700; margin: 0;">4<span style="font-size: .9rem; font-weight: 500;"> mos</span></p></div><div style="width: 1px; background: #E5E7EB;"></div><div style="text-align: center;"><p style="color: #6B7280; font-size: .85rem; margin-bottom: 8px;">60+</p><p style="color: #7F3D9E; font-size: 1.6rem; font-weight: 700; margin: 0;">7<span style="font-size: .9rem; font-weight: 500;"> mos</span></p></div></div></div></div></div>
        </div>
    </div>
</section>

<?php
$platinumPriceByBracket = ['Below 70' => 300, '71 - 80' => 550, '81 - 90' => 650, '91 - 100' => 850];
$comparisonRows = [];
foreach (($packages ?? []) as $package) {
    $name = (string) ($package['name'] ?? 'Basic package');
    $base = (int) ($package['monthly_contribution'] ?? 0);
    $category = (string) ($package['category'] ?? '');
    $bracket = str_contains($name, '71-80') ? '71 - 80' : (str_contains($name, '81-90') ? '81 - 90' : (str_contains($name, '91-100') ? '91 - 100' : 'Below 70'));
    $platinum = $platinumPriceByBracket[$bracket];
    $comparisonRows[] = ['name' => $name, 'base' => $base, 'platinum' => $platinum, 'category' => $category];
}
?>

<!-- Compare Plans Section (same design language as cards) -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #1A1A1A;">Compare Plans</h2>
            <p style="color: #6B7280; font-size: 1.1rem; max-width: 760px; margin: 0 auto;">This comparison helps you see the difference at a glance: SHENA BASIC is our primary welfare cover during unforeseen eventualities supporting members and their loved ones with funeral services. SHENA PLATINUM provides inpatient support for members and their loved ones as a complimentary service to SHENA BASIC, paying for daily bed charges.</p>
        </div>

        <div class="table-responsive mb-5" style="display:none;">
            <table class="table mb-0 align-middle"><thead style="background:#2D1A4A;color:#fff;"><tr><th style="padding:16px;">Basic plan</th><th style="padding:16px;">Basic / month</th><th style="padding:16px;">Platinum add-on*</th><th style="padding:16px;">Basic + Platinum example</th></tr></thead><tbody>
            <?php foreach ($comparisonRows as $row): ?><tr><td style="padding:15px;font-weight:600;color:#2D1A4A;"><?php echo htmlspecialchars($row['name']); ?></td><td style="padding:15px;">KES <?php echo number_format($row['base']); ?></td><td style="padding:15px;color:#7F3D9E;font-weight:700;">KES <?php echo number_format($row['platinum']); ?> / selected person</td><td style="padding:15px;font-weight:700;color:#7F3D9E;">KES <?php echo number_format($row['base'] + $row['platinum']); ?> / month</td></tr><?php endforeach; ?>
            </tbody></table>
        </div>
        <p style="display:none;">* Platinum age bands: below 70 = KES 300; 71-80 = KES 550; 81-90 = KES 650; 91-100 = KES 850. Family plans may select Platinum for one or more eligible covered people, so the final total depends on who is selected.</p>

        <div class="row g-4" style="display:none;">
            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-user" style="color: #7F3D9E; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Individual</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Principal member only</p>
                    <div style="margin-bottom: 12px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 100+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; flex: 1;">Best for single-member protection by age bracket.</p>
                    <a href="/register?plan=individual" class="btn" style="background-color: #7F3D9E; color: white; border-radius: 8px; font-weight: 600; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-heart" style="color: #7F3D9E; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Family</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Member + spouse</p>
                    <div style="margin-bottom: 12px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 150</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; flex: 1;">Best for principal and spouse — flat rate, any age.</p>
                    <a href="/register?plan=family" class="btn" style="background-color: #7F3D9E; color: white; border-radius: 8px; font-weight: 600; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #7F3D9E; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 8px 30px rgba(127, 61, 158, 0.2); text-align: center; position: relative; transform: scale(1.02); display: flex; flex-direction: column;">
                    <span style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #7F3D9E; color: white; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px;">POPULAR</span>
                    <div style="width: 70px; height: 70px; background: #7F3D9E; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-users" style="color: white; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Extended Family 1</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Couple + children + parents</p>
                    <div style="margin-bottom: 12px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 250+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; flex: 1;">Best for nuclear family with parents included.</p>
                    <a href="/register?plan=extended_family_1" class="btn" style="background-color: #7F3D9E; color: white; border-radius: 8px; font-weight: 600; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column;">
                    <div style="width: 70px; height: 70px; background: #FEF3E8; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-crown" style="color: #D97706; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Extended Family 2</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">+ In-laws included</p>
                    <div style="margin-bottom: 12px;"><span style="color: #D97706; font-size: 2.2rem; font-weight: 700;">KES 300+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; flex: 1;">Best for the broadest family-unit coverage.</p>
                    <a href="/register?plan=extended_family_2" class="btn" style="background-color: #D97706; color: white; border-radius: 8px; font-weight: 600; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center; display: flex; flex-direction: column;">
                    <div style="width: 70px; height: 70px; background: #FEF3E8; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-crown" style="color: #D97706; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Executive</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Premium individual handling</p>
                    <div style="margin-bottom: 12px;"><span style="color: #D97706; font-size: 2.2rem; font-weight: 700;">KES 300 - 500</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin-bottom: 20px; flex: 1;">Best for premium support and prioritized experience.</p>
                    <a href="/register?plan=executive" class="btn" style="background-color: #D97706; color: white; border-radius: 8px; font-weight: 600; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" id="platinum">
                <div style="background: #2D1A4A; border: 2px solid #C9A659; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 8px 30px rgba(45,26,74,0.18); text-align: center; display: flex; flex-direction: column;">
                    <div style="width: 70px; height: 70px; background: #C9A659; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-hospital" style="color: #2D1A4A; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: white;">Platinum Add-on</h3>
                    <p style="color: rgba(255,255,255,.75); font-size: 0.9rem; margin-bottom: 18px;">Optional inpatient support</p>
                    <div style="margin-bottom: 12px;"><span style="color: #E8C879; font-size: 2rem; font-weight: 700;">KES 300+</span><small style="color:rgba(255,255,255,.7);"> / selected person / month</small></div>
                    <p style="color: rgba(255,255,255,.75); font-size: 0.88rem; margin-bottom: 20px; flex: 1;">The exact add-on amount follows the person's age band. Add it to any Basic plan after membership.</p>
                    <a href="/login" class="btn" style="background-color: #C9A659; color: #2D1A4A; border-radius: 8px; font-weight: 700; text-decoration: none; padding: 10px 0; width: 100%; display: block;">Get Started</a>
                </div>
            </div>
        </div>

        <div style="max-width: 980px; margin: 0 auto; border: 1px solid #E5E7EB; border-radius: 20px; overflow: hidden; box-shadow: 0 12px 35px rgba(45,26,74,.10); background: white;">
            <div class="row g-0" style="background: #2D1A4A; color: white; margin: 0;">
                <div class="col-6" style="padding: 22px 28px; font-weight: 700; font-size: 1.05rem;">Feature</div>
                <div class="col-3 text-center" style="padding: 22px 12px; background: #7F3D9E; font-weight: 700;">SHENA Basic</div>
                <div class="col-3 text-center" style="padding: 22px 12px; background: #C9A659; color: #2D1A4A; font-weight: 700;">SHENA Platinum</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; border-bottom: 1px solid #E5E7EB;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Last Respect funeral cover</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-size: 1.25rem;">&#10003;</div>
                <div class="col-3 text-center" style="padding: 18px; color: #6B7280;">Add-on</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; border-bottom: 1px solid #E5E7EB; background: #FCFBFE;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Mortuary support up to 14 days</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-size: 1.25rem;">&#10003;</div>
                <div class="col-3 text-center" style="padding: 18px; color: #6B7280;">Included with Basic</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; border-bottom: 1px solid #E5E7EB;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Inpatient bed-cover support</div>
                <div class="col-3 text-center" style="padding: 18px; color: #9CA3AF; font-size: 1.25rem;">&mdash;</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-size: 1.25rem;">&#10003;</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; border-bottom: 1px solid #E5E7EB; background: #FCFBFE;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Annual inpatient allowance</div>
                <div class="col-3 text-center" style="padding: 18px; color: #9CA3AF;">&mdash;</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-weight: 700;">20 days</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; border-bottom: 1px solid #E5E7EB;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Selected per covered person</div>
                <div class="col-3 text-center" style="padding: 18px; color: #6B7280;">Basic member</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-size: 1.25rem;">&#10003;</div>
            </div>
            <div class="row g-0 align-items-center" style="margin: 0; background: #FCFBFE;">
                <div class="col-6" style="padding: 18px 28px; color: #2D1A4A; font-weight: 600;">Monthly contribution</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-weight: 700;">Basic rates</div>
                <div class="col-3 text-center" style="padding: 18px; color: #7F3D9E; font-weight: 700;">KES 300 - 850</div>
            </div>
        </div>
        <p style="color:#6B7280;font-size:.9rem;max-width:980px;margin:20px auto 0;text-align:center;">SHENA Platinum is an optional add-on to SHENA Basic. Its contribution is charged per selected covered person and follows the age and package schedules above.</p>
    </div>
</section>

<style>
#packages .row > div[style*="border-left"],
#platinum-packages .row > div[style*="border-left"] {
    border-left: 0 !important;
    border-top: 1px solid #D9D3DF;
}
#packageTabs .nav-link.active {
    color: #7F3D9E !important;
    border-bottom-color: #7F3D9E !important;
}
#packageTabs .nav-link:hover {
    color: #7F3D9E !important;
}
</style>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
