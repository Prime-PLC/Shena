<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Terms Hero -->
<section class="reveal-exempt" style="position: relative; min-height: 340px; display: flex; align-items: center; background: linear-gradient(rgba(45,26,74,0.80), rgba(45,26,74,0.80)), url('/public/images/community-mobilization1.jpeg') center/cover no-repeat;">
    <div class="container">
        <div class="text-center text-white">
            <p style="color: #C9A659; font-size: 0.8rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;">LEGAL &amp; COMPLIANCE</p>
            <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; margin-bottom: 16px; text-shadow: 2px 2px 6px rgba(0,0,0,0.3);">Terms &amp; Conditions</h1>
            <p style="opacity: 0.85; font-size: 1rem; max-width: 640px; margin: 0 auto;">The rules that govern membership, contributions, benefits, and use of SHENA Companion services.</p>
        </div>
    </div>
</section>

<section class="reveal-exempt" style="padding: 70px 0; background: #F7F7F9;">
    <div class="container" style="max-width: 860px;">

        <!-- Meta strip -->
        <div style="background: #fff; border-radius: 16px; padding: 28px 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); margin-bottom: 36px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center; justify-content: space-between;">
            <div>
                <p style="margin: 0 0 4px; color: #6B7280; font-size: 0.82rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Effective Date</p>
                <p style="margin: 0; color: #1A1A1A; font-weight: 600;">1 January 2024</p>
            </div>
            <div>
                <p style="margin: 0 0 4px; color: #6B7280; font-size: 0.82rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Last Updated</p>
                <p style="margin: 0; color: #1A1A1A; font-weight: 600;">April 2026</p>
            </div>
            <div>
                <p style="margin: 0 0 4px; color: #6B7280; font-size: 0.82rem; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">Governing Law</p>
                <p style="margin: 0; color: #1A1A1A; font-weight: 600;">Republic of Kenya</p>
            </div>
            <a href="/policy-booklet" style="background: #7F3D9E; color: white; padding: 10px 22px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 8px; white-space: nowrap;">
                <i class="fas fa-book-open"></i> Policy Booklet
            </a>
        </div>

        <div style="background: #fff; border-radius: 16px; padding: 48px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); color: #374151; line-height: 1.8;">

            <?php
            $terms = [
                ['icon' => 'fas fa-handshake', 'title' => '1. Acceptance of Terms', 'content' => '
                    <p>By registering as a member of SHENA Companion Welfare Association, you agree to be bound by these Terms and Conditions, our Privacy Policy, and our Policy Booklet. If you do not agree, you must not register or use our services.</p>
                    <p>These terms constitute a binding agreement between you and SHENA Companion Welfare Association (hereinafter "SHENA", "the Association").</p>
                '],
                ['icon' => 'fas fa-user-plus', 'title' => '2. Membership Eligibility', 'content' => '
                    <ul>
                        <li>Membership is open to Kenyan citizens and residents aged <strong>18 years and above</strong>.</li>
                        <li>Applicants must provide a valid National ID number and a registered M-Pesa phone number.</li>
                        <li>Members may register dependents (spouse, children, parents) subject to package limits.</li>
                        <li>Providing false information during registration constitutes grounds for immediate termination without refund.</li>
                        <li>A non-refundable registration fee of <strong>KES 200</strong> is payable upon joining.</li>
                    </ul>
                '],
                ['icon' => 'fas fa-money-check-alt', 'title' => '3. Contributions & Payments', 'content' => '
                    <ul>
                        <li>Monthly contributions are payable via <strong>M-Pesa Paybill 4163987</strong>, using your name or ID number as the account reference.</li>
                        <li>Contribution rates are determined by your selected package and the age brackets of covered individuals.</li>
                        <li>A member is considered <strong>active</strong> only when all current and past contributions are fully settled.</li>
                        <li>Contributions that are <strong>3 or more months in arrears</strong> will result in suspension of benefit coverage.</li>
                        <li>Contributions that are <strong>6 or more months in arrears</strong> may result in termination of membership.</li>
                        <li>SHENA reserves the right to adjust contribution rates with at least <strong>30 days\' prior notice</strong> to members.</li>
                        <li>Payments are non-refundable except where SHENA has accepted a formal written request and confirmed an error.</li>
                    </ul>
                '],
                ['icon' => 'fas fa-heartbeat', 'title' => '4. Benefits & Coverage', 'content' => '
                    <p><strong>Last Respect Services</strong> cover the following in-kind funeral support services for the member or a registered dependent upon verified death:</p>
                    <ul>
                        <li>Mortuary fees for up to <strong>14 days</strong></li>
                        <li>Professional body dressing and preparation</li>
                        <li>Body transportation (hearse) from morgue to burial site</li>
                        <li>One executive-grade coffin</li>
                        <li>Burial lowering gear and gazebo tents</li>
                        <li>100 chairs for the funeral service</li>
                    </ul>
                    <p><strong>Exclusions &amp; Limitations:</strong></p>
                    <ul>
                        <li>Hospital admission fees and morgue or hospital entry charges are <strong>not covered</strong> — members are responsible for these upon arrival.</li>
                        <li>Benefits are <strong>not available</strong> to suspended or lapsed members.</li>
                        <li>Coverage does not apply to deaths that occur during a <strong>waiting period</strong> as defined by your package terms.</li>
                        <li>SHENA does not cover funeral costs for individuals not registered as dependents under the member\'s account.</li>
                        <li>Benefits are provided in-kind by SHENA-contracted service providers and are <strong>not transferable to cash</strong>.</li>
                    </ul>
                '],
                ['icon' => 'fas fa-file-alt', 'title' => '5. Claims Process', 'content' => '
                    <ul>
                        <li>Claims must be submitted within <strong>30 days</strong> of the date of death.</li>
                        <li>The following documents are required to process a valid claim:
                            <ul>
                                <li>Certified copy of the Death Certificate</li>
                                <li>Copy of the deceased\'s National ID</li>
                                <li>Copy of the claimant\'s (member\'s) National ID</li>
                                <li>Burial Permit</li>
                                <li>Completed SHENA Claim Form (available via the member portal)</li>
                            </ul>
                        </li>
                        <li>Fraudulent claims or submission of falsified documents will result in <strong>immediate termination</strong> and may be reported to law enforcement.</li>
                        <li>All claims are subject to review and verification by SHENA management before service delivery is authorized.</li>
                    </ul>
                '],
                ['icon' => 'fas fa-users-cog', 'title' => '6. Member Responsibilities', 'content' => '
                    <ul>
                        <li>Keep your contact information, dependent records, and ID details up to date via the member portal.</li>
                        <li>Ensure contribution payments are made on time and using the correct M-Pesa reference.</li>
                        <li>Notify SHENA within <strong>14 days</strong> of any change in family status affecting dependent registration.</li>
                        <li>Do not share portal login credentials with third parties.</li>
                        <li>Uphold the dignity and spirit of mutual welfare within the SHENA community.</li>
                    </ul>
                '],
                ['icon' => 'fas fa-ban', 'title' => '7. Termination & Suspension', 'content' => '
                    <p>SHENA may suspend or terminate a membership for the following reasons:</p>
                    <ul>
                        <li>Non-payment of contributions for 6 or more consecutive months</li>
                        <li>Provision of false or fraudulent information during registration or claims</li>
                        <li>Abusive, threatening, or disruptive behaviour toward SHENA staff or agents</li>
                        <li>Violation of these Terms and Conditions</li>
                    </ul>
                    <p>A member may voluntarily withdraw by submitting a written notice to SHENA management. No refunds of contributions will be issued.</p>
                '],
                ['icon' => 'fas fa-balance-scale', 'title' => '8. Limitation of Liability', 'content' => '
                    <p>SHENA Companion shall not be liable for:</p>
                    <ul>
                        <li>Delays in service delivery arising from incomplete or inaccurate claim documentation</li>
                        <li>Acts of God, civil unrest, pandemics, or other force majeure events</li>
                        <li>Third-party service failures beyond SHENA\'s reasonable control</li>
                        <li>Any indirect, consequential, or punitive losses arising from use of or inability to use SHENA services</li>
                    </ul>
                    <p>SHENA\'s total liability in any circumstance shall not exceed the value of services that would have been due under the applicable package.</p>
                '],
                ['icon' => 'fas fa-edit', 'title' => '9. Amendments', 'content' => '
                    <p>SHENA reserves the right to amend these Terms and Conditions at any time. Material changes will be communicated to members via SMS or email with at least <strong>30 days\' notice</strong>. Continued use of SHENA services after the effective date of changes constitutes acceptance of the updated terms.</p>
                '],
                ['icon' => 'fas fa-gavel', 'title' => '10. Governing Law & Disputes', 'content' => '
                    <p>These Terms and Conditions are governed by the laws of the <strong>Republic of Kenya</strong>. Any disputes arising from or in connection with membership in SHENA Companion shall first be attempted to be resolved through good-faith negotiation. If unresolved, disputes shall be referred to mediation or arbitration in Kisumu, Kenya, before recourse to the courts.</p>
                '],
                ['icon' => 'fas fa-envelope', 'title' => '11. Contact', 'content' => '
                    <p>For enquiries regarding these terms:</p>
                    <div style="background: #F3E8FF; border-radius: 12px; padding: 20px 24px; margin-top: 12px;">
                        <p style="margin: 0 0 8px;"><strong>SHENA Companion Welfare Association</strong></p>
                        <p style="margin: 0 0 4px;">P.O. Box 4018, Kisumu, Kenya</p>
                        <p style="margin: 0 0 4px;">Phone: <a href="tel:+254748585067" style="color: #7F3D9E; font-weight: 600;">0748 585 067</a> / <a href="tel:+254748585071" style="color: #7F3D9E; font-weight: 600;">0748 585 071</a></p>
                        <p style="margin: 0;">Email: <a href="mailto:info@shenacompanion.co.ke" style="color: #7F3D9E; font-weight: 600;">info@shenacompanion.co.ke</a></p>
                    </div>
                '],
            ];
            foreach ($terms as $t): ?>
            <div style="margin-bottom: 44px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <div style="width: 42px; height: 42px; background: #F3E8FF; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="<?php echo $t['icon']; ?>" style="color: #7F3D9E;"></i>
                    </div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php echo $t['title']; ?></h2>
                </div>
                <div style="padding-left: 54px;"><?php echo $t['content']; ?></div>
            </div>
            <?php endforeach; ?>

            <!-- Related links -->
            <div style="border-top: 2px solid #F3F4F6; padding-top: 32px; margin-top: 16px; display: flex; flex-wrap: wrap; gap: 12px;">
                <a href="/privacy-policy" style="display: inline-flex; align-items: center; gap: 8px; color: #7F3D9E; font-weight: 600; text-decoration: none; background: #F3E8FF; padding: 10px 20px; border-radius: 8px;">
                    <i class="fas fa-shield-alt"></i> Privacy Policy
                </a>
                <a href="/policy-booklet" style="display: inline-flex; align-items: center; gap: 8px; color: #7F3D9E; font-weight: 600; text-decoration: none; background: #F3E8FF; padding: 10px 20px; border-radius: 8px;">
                    <i class="fas fa-book-open"></i> Policy Booklet
                </a>
                <a href="/contact" style="display: inline-flex; align-items: center; gap: 8px; color: #7F3D9E; font-weight: 600; text-decoration: none; background: #F3E8FF; padding: 10px 20px; border-radius: 8px;">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
