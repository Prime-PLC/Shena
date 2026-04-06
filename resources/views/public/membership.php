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
                                <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin: 0;">Individual Package Rates</h2>
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

<!-- Compare Plans Section (same design language as cards) -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3" style="font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #1A1A1A;">Compare Plans</h2>
            <p style="color: #6B7280; font-size: 1.1rem; max-width: 700px; margin: 0 auto;">Quick side-by-side plan comparison with the same card design style used across this page.</p>
        </div>

        <div class="row g-4">
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
        </div>
    </div>
</section>

<style>
#packageTabs .nav-link.active {
    color: #7F3D9E !important;
    border-bottom-color: #7F3D9E !important;
}
#packageTabs .nav-link:hover {
    color: #7F3D9E !important;
}
</style>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
