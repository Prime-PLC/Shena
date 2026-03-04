<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Membership Hero Section -->
<section style="padding: 80px 0; background: white;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div style="background: linear-gradient(135deg, #F3E8FF 0%, #EDE9FE 100%); border-radius: 30px; padding: 80px 60px; text-align: center; box-shadow: 0 20px 60px rgba(127, 61, 158, 0.15);">
                    <span style="background: rgba(127, 61, 158, 0.15); color: #7F3D9E; padding: 8px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1.5px; display: inline-block; margin-bottom: 30px;">
                        PREMIUM COVERAGE
                    </span>
                    <h1 class="mb-4" style="font-family: 'Playfair Display', serif; font-size: 3.5rem; font-weight: 700; line-height: 1.2; color: #7F3D9E;">
                        Choose Your Royal Protection
                    </h1>
                    <p class="mb-5" style="font-size: 1.1rem; line-height: 1.7; max-width: 700px; margin: 0 auto 40px; color: #4A5568;">
                        Comprehensive coverage tailored for all ages and family sizes. Secure your future and your loved ones' peace of mind with our flexible monthly contribution plans.
                    </p>
                    <a href="#packages" class="btn btn-lg" style="background-color: #7F3D9E; color: white; padding: 16px 50px; border-radius: 10px; font-weight: 700; font-size: 1.1rem; border: none; box-shadow: 0 8px 20px rgba(127, 61, 158, 0.3); text-decoration: none;">
                        View All Packages
                    </a>
                </div>
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
                            Individual Packages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="family-tab" data-bs-toggle="tab" data-bs-target="#family" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">
                            Family Packages
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="executive-tab" data-bs-toggle="tab" data-bs-target="#executive" type="button" role="tab" style="color: #6B7280; font-weight: 600; border: none; border-bottom: 3px solid transparent; padding: 12px 24px;">
                            Executive Packages
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
                                        <td style="padding: 20px; text-align: right;"><a href="/register" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">71 - 80 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">350</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #E5E7EB;">
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">81 - 90 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">450</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 20px; font-weight: 600; color: #1A1A1A;">91 - 100 Years</td>
                                        <td style="padding: 20px; font-size: 1.5rem; font-weight: 700; color: #7F3D9E;">650</td>
                                        <td style="padding: 20px; text-align: right;"><a href="/register" class="btn" style="background-color: #7F3D9E; color: white; padding: 10px 30px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem;">Get Started</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="family" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                            <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin-bottom: 30px;">Family Package Options</h2>
                            <p style="color: #6B7280; font-size: 1rem; line-height: 1.7; margin-bottom: 0;">Family plans include couple, children, and optional parents/in-laws extensions. Use Compare Plans below to choose the best fit.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="executive" role="tabpanel">
                        <div style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                            <h2 style="font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: #1A1A1A; margin-bottom: 30px;">Executive Package Details</h2>
                            <p style="color: #6B7280; font-size: 1rem; line-height: 1.7; margin-bottom: 0;">Executive plans offer premium handling and enhanced support for members who need top-tier cover.</p>
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
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-user" style="color: #7F3D9E; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Individual</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Principal member only</p>
                    <div style="margin-bottom: 20px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 100+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin: 0;">Best for single-member protection by age bracket.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;">
                    <div style="width: 70px; height: 70px; background: #F3E8FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-heart" style="color: #7F3D9E; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Couple</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Member + spouse</p>
                    <div style="margin-bottom: 20px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 150+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin: 0;">Best for couples planning together under one cover.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #7F3D9E; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 8px 30px rgba(127, 61, 158, 0.2); text-align: center; position: relative; transform: scale(1.02);">
                    <span style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #7F3D9E; color: white; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 1px;">POPULAR</span>
                    <div style="width: 70px; height: 70px; background: #7F3D9E; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-users" style="color: white; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Family</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Couple + children (+ options)</p>
                    <div style="margin-bottom: 20px;"><span style="color: #7F3D9E; font-size: 2.2rem; font-weight: 700;">KES 200+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin: 0;">Best for household-level protection and flexibility.</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div style="background: white; border: 2px solid #E5E7EB; border-radius: 20px; padding: 40px 30px; height: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.06); text-align: center;">
                    <div style="width: 70px; height: 70px; background: #FEF3E8; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;"><i class="fas fa-crown" style="color: #D97706; font-size: 2rem;"></i></div>
                    <h3 class="mb-2" style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #1A1A1A;">Executive</h3>
                    <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 18px;">Premium handling</p>
                    <div style="margin-bottom: 20px;"><span style="color: #D97706; font-size: 2.2rem; font-weight: 700;">KES 400+</span></div>
                    <p style="color: #6B7280; font-size: 0.88rem; margin: 0;">Best for premium support and prioritized experience.</p>
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
