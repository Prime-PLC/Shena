<?php include VIEWS_PATH . '/layouts/header.php'; ?>

<!-- Policy Booklet Hero -->
<section style="position: relative; min-height: 340px; display: flex; align-items: center; background: linear-gradient(rgba(45,26,74,0.85), rgba(45,26,74,0.85)), url('/public/images/funeral-service1.jpeg') center/cover no-repeat;" class="d-print-none reveal-exempt">
    <div class="container">
        <div class="text-center text-white">
            <p style="color: #C9A659; font-size: 0.8rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 12px;">OFFICIAL DOCUMENTATION</p>
            <h1 style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; margin-bottom: 16px;">SHENA Companion Policy Booklet</h1>
            <p style="opacity: 0.85; font-size: 1rem; max-width: 640px; margin: 0 auto 28px;">The official reference document governing membership, contributions, benefits, and claims for all SHENA Companion Welfare Association members.</p>
            <button onclick="window.print()" style="background: #C9A659; color: #1A1A1A; border: none; padding: 12px 32px; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; gap: 10px;">
                <i class="fas fa-print"></i> Print / Save as PDF
            </button>
        </div>
    </div>
</section>

<!-- Print styles -->
<style>
    @media print {
        .d-print-none { display: none !important; }
        .policy-booklet-body { padding: 0 !important; background: white !important; }
        .booklet-wrap { box-shadow: none !important; border-radius: 0 !important; padding: 20px !important; max-width: 100% !important; }
        .booklet-cover { break-after: page; }
        h2 { break-after: avoid; }
        .section-block { break-inside: avoid; }
    }
    @page { margin: 18mm; size: A4; }
    .booklet-wrap ul { padding-left: 22px; }
    .booklet-wrap ul li { margin-bottom: 6px; }
    .booklet-toc a { text-decoration: none; color: #7F3D9E; font-weight: 500; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dotted #D1D5DB; padding: 6px 0; }
    .booklet-toc a:hover { color: #5A1F73; }
    .policy-sidebar a { text-decoration: none; color: #4B5563; font-size: 0.82rem; padding: 8px 10px; border-radius: 8px; display: flex; align-items: center; gap: 8px; transition: background 0.2s, color 0.2s; }
    .policy-sidebar a:hover { background: #F3E8FF; color: #7F3D9E; }
    @media (min-width: 992px) {
        body:has(.policy-booklet-body) { overflow: hidden; }
        .policy-booklet-body { position: fixed; top: 194px; right: 0; bottom: 0; left: 0; overflow: hidden; padding: 16px 0 24px !important; z-index: 1; }
        .policy-booklet-body > .container > .row { display: block; }
        .policy-sidebar { position: fixed; top: 210px; left: max(16px, calc((100vw - 1160px) / 2)); width: 262px; z-index: 10; }
        .policy-sidebar > div { max-height: calc(100vh - 124px); overflow-y: auto; }
        .policy-document-scroll { width: calc(100% - 292px); margin-left: 292px; height: calc(100vh - 234px); overflow-y: auto; padding-right: 10px; }
        .policy-document-scroll::-webkit-scrollbar { width: 8px; }
        .policy-document-scroll::-webkit-scrollbar-thumb { background: #D8B4FE; border-radius: 8px; }
    }
    @media (max-width: 991.98px) { .policy-sidebar { margin-bottom: 24px; } }
    @media (max-width: 991.98px) {
        .policy-booklet-body { overflow-x: hidden; }
        .policy-booklet-body .container { max-width: 100%; padding-left: 12px; padding-right: 12px; }
        .policy-booklet-body .booklet-wrap { width: 100%; max-width: 100%; padding: 32px 20px !important; overflow-wrap: anywhere; }
        .policy-booklet-body [style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
        .policy-booklet-body table { min-width: 0; }
    }
    @media (max-width: 575.98px) {
        .policy-booklet-body .booklet-wrap { padding: 26px 16px !important; border-radius: 10px; }
        .policy-booklet-body .booklet-cover h1 { font-size: 1.65rem !important; }
        .policy-booklet-body .booklet-cover h2 { font-size: 1.2rem !important; }
        .policy-booklet-body .section-block h2 { font-size: 1.08rem !important; }
    }
    @media print {
        .policy-document-scroll { height: auto !important; overflow: visible !important; padding-right: 0 !important; }
    }
</style>

<section class="policy-booklet-body reveal-exempt" style="padding: 60px 0; background: #F7F7F9;">
    <div class="container" style="max-width: 1160px;">
        <div class="row g-4 align-items-start">
            <aside class="col-lg-3 d-none d-lg-block policy-sidebar d-print-none">
                <div style="background: white; border-radius: 14px; padding: 22px; box-shadow: 0 4px 16px rgba(0,0,0,0.07); position: sticky; top: 80px;">
                    <p style="font-size: 0.72rem; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #7F3D9E; margin: 0 0 14px;">Policy sections</p>
                    <nav style="display: flex; flex-direction: column; gap: 3px;">
                        <?php foreach ([['pb-intro','About SHENA'],['pb-eligibility','Eligibility & Registration'],['pb-packages','Packages & Contributions'],['pb-waiting','Waiting Period'],['pb-benefits','Last Respect Benefits'],['pb-dependents','Registered Dependents'],['pb-claims','Claims Procedure'],['pb-suspension','Suspension & Reinstatement'],['pb-obligations','Member Obligations'],['pb-exclusions','Exclusions'],['pb-termination','Termination'],['pb-amendments','Policy Amendments'],['pb-contact','Contact & Complaints']] as $link): ?>
                            <a href="#<?php echo $link[0]; ?>"><i class="fas fa-chevron-right fa-fw" style="color:#7F3D9E; opacity:.7;"></i><?php echo $link[1]; ?></a>
                        <?php endforeach; ?>
                    </nav>
                    <div style="border-top: 1px solid #F3F4F6; padding-top: 16px; margin-top: 16px;">
                        <a href="/terms-and-conditions" style="color:#7F3D9E; font-weight:600;"><i class="fas fa-file-contract fa-fw"></i> Terms &amp; Conditions</a>
                        <a href="/privacy-policy" style="color:#7F3D9E; font-weight:600;"><i class="fas fa-shield-alt fa-fw"></i> Privacy Policy</a>
                    </div>
                </div>
            </aside>
            <div class="col-lg-9 policy-document-scroll">
        <div class="booklet-wrap" style="background: #fff; border-radius: 16px; padding: 60px 56px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); color: #374151; line-height: 1.85;">

            <!-- Cover Block -->
            <div class="booklet-cover text-center" style="border-bottom: 3px solid #7F3D9E; padding-bottom: 40px; margin-bottom: 40px;">
                <img src="/public/images/shena-logo.png" alt="SHENA Logo" style="height: 80px; margin-bottom: 20px;">
                <h1 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 2.2rem; font-weight: 700; margin-bottom: 8px;">SHENA Companion Welfare Association</h1>
                <h2 style="font-family: 'Playfair Display', serif; color: #7F3D9E; font-size: 1.5rem; font-weight: 600; margin-bottom: 20px;">Official Member Policy Booklet</h2>
                <p style="color: #6B7280; font-size: 0.9rem;">Edition: April 2026 &nbsp;|&nbsp; Kisumu, Kenya &nbsp;|&nbsp; All rights reserved</p>
                <div style="display: flex; justify-content: center; gap: 16px; margin-top: 24px; flex-wrap: wrap;">
                    <a href="/terms-and-conditions" style="background: #F3E8FF; color: #7F3D9E; padding: 9px 20px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none;"><i class="fas fa-file-contract me-1"></i> Terms &amp; Conditions</a>
                    <a href="/privacy-policy" style="background: #F3E8FF; color: #7F3D9E; padding: 9px 20px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none;"><i class="fas fa-shield-alt me-1"></i> Privacy Policy</a>
                    <button onclick="window.print()" class="d-print-none" style="background: #7F3D9E; color: white; border: none; padding: 9px 20px; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer;"><i class="fas fa-download me-1"></i> Download PDF</button>
                </div>
            </div>

            <!-- Table of Contents -->
            <div class="booklet-toc" style="margin-bottom: 48px;">
                <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.4rem; margin-bottom: 20px;">Table of Contents</h2>
                <nav>
                    <a href="#pb-intro">1. About SHENA Companion <span>4</span></a>
                    <a href="#pb-eligibility">2. Membership Eligibility &amp; Registration <span>4</span></a>
                    <a href="#pb-packages">3. Membership Packages &amp; Contributions <span>5</span></a>
                    <a href="#pb-waiting">4. Waiting Period <span>6</span></a>
                    <a href="#pb-benefits">5. Benefits — Last Respect Services <span>6</span></a>
                    <a href="#pb-dependents">6. Registered Dependents <span>7</span></a>
                    <a href="#pb-claims">7. Claims Procedure <span>8</span></a>
                    <a href="#pb-suspension">8. Suspension, Lapsing &amp; Reinstatement <span>9</span></a>
                    <a href="#pb-obligations">9. Member Obligations <span>9</span></a>
                    <a href="#pb-exclusions">10. Exclusions <span>10</span></a>
                    <a href="#pb-termination">11. Termination of Membership <span>10</span></a>
                    <a href="#pb-amendments">12. Policy Amendments <span>11</span></a>
                    <a href="#pb-contact">13. Contact &amp; Complaints <span>11</span></a>
                </nav>
            </div>

            <!-- Sections -->
            <?php
            $pb = [
                ['id' => 'pb-intro', 'num' => '1', 'title' => 'About SHENA Companion', 'body' => '
                    <p>SHENA Companion Welfare Association (hereinafter "SHENA" or "the Association") is a community-based welfare organization registered in Kenya. Its core mandate is to ensure that member families receive a dignified, organized, and professionally supported funeral service upon the death of a covered member or registered dependent.</p>
                    <p>SHENA operates on a <strong>pre-paid, contribution-based</strong> model. Members contribute monthly, and the Association provides Last Respect Services in-kind when a valid claim arises. SHENA is built on the values of dignity, community solidarity, and professional service.</p>
                    <blockquote style="border-left: 4px solid #C9A659; padding-left: 18px; margin: 16px 0; color: #2D1A4A; font-style: italic; font-family: \'Playfair Display\', serif; font-size: 1.1rem;">"We Are Royal" — SHENA Companion</blockquote>
                '],
                ['id' => 'pb-eligibility', 'num' => '2', 'title' => 'Membership Eligibility & Registration', 'body' => '
                    <p><strong>Who may join:</strong></p>
                    <ul>
                        <li>Kenyan citizens and residents aged <strong>18 years and above</strong></li>
                        <li>Applicants must hold a valid Kenya National ID</li>
                        <li>Applicants must have an active M-Pesa-registered phone number</li>
                    </ul>
                    <p><strong>Registration process:</strong></p>
                    <ol>
                        <li>Complete the online or in-person registration form with accurate personal details</li>
                        <li>Pay the one-time, non-refundable <strong>registration fee of KES 200</strong> via M-Pesa Paybill 4163987</li>
                        <li>Submit required identity documents for verification</li>
                        <li>Receive your unique SHENA member number upon approval</li>
                    </ol>
                    <p>Providing false or misleading information during registration is grounds for immediate cancellation of membership without refund.</p>
                '],
                ['id' => 'pb-packages', 'num' => '3', 'title' => 'Membership Packages & Contributions', 'body' => '
                    <p><strong>SHENA BASIC</strong> is our primary welfare cover during unforeseen eventualities. We support our members and their loved ones with funeral services. <strong>SHENA PLATINUM</strong> provides inpatient support for members and their loved ones. It is a complimentary service to SHENA BASIC. It pays for daily bed charges for members and their dependants. Platinum is selected and charged per covered person.</p>
                    <div style="overflow-x: auto; margin: 16px 0;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.93rem;">
                            <thead>
                                <tr style="background: #7F3D9E; color: white;">
                                    <th style="padding: 12px 16px; text-align: left; border-radius: 6px 0 0 6px;">Package</th>
                                    <th style="padding: 12px 16px; text-align: left;">Coverage</th>
                                    <th style="padding: 12px 16px; text-align: left;">Basic monthly schedule</th>
                                    <th style="padding: 12px 16px; text-align: left; border-radius: 0 6px 6px 0;">Platinum monthly schedule</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #E5E7EB;">
                                    <td style="padding: 12px 16px; font-weight: 600;">SHENA Basic — Individual</td>
                                    <td style="padding: 12px 16px; color: #6B7280;">Member only</td>
                                    <td style="padding: 12px 16px;">KES 100–650/mo by age</td>
                                    <td style="padding: 12px 16px;">See Basic schedule</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #E5E7EB; background: #FAF5FF;">
                                    <td style="padding: 12px 16px; font-weight: 600;">SHENA Basic — Family</td>
                                    <td style="padding: 12px 16px; color: #6B7280;">Family package as registered</td>
                                    <td style="padding: 12px 16px;">KES 150–650/mo</td>
                                    <td style="padding: 12px 16px;">See Basic schedule</td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; font-weight: 600;">SHENA Platinum</td>
                                    <td style="padding: 12px 16px; color: #6B7280;">Optional inpatient add-on per selected person</td>
                                    <td style="padding: 12px 16px;">KES 300-850/mo</td>
                                    <td style="padding: 12px 16px;">See Platinum schedule</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p style="font-size: 0.88rem; color: #6B7280; font-style: italic;">* Rates are reviewed periodically. Members will be given 30 days\' notice before any rate change takes effect.</p>
                    <p><strong>Payment method:</strong> M-Pesa Paybill <strong>4163987</strong>. Use your full name or National ID number as the account reference. Retain your M-Pesa confirmation SMS as proof of payment.</p>
                '],
                ['id' => 'pb-waiting', 'num' => '4', 'title' => 'Waiting Period', 'body' => '
                    <p>SHENA Basic cover follows the waiting period applicable to the selected Basic package. For SHENA Platinum, maturity is <strong>4 months for selected people below age 60</strong> and <strong>7 months for selected people aged 60 and above</strong>, calculated from the effective date of that person\'s approved Platinum cover.</p>
                    <p>Platinum inpatient requests are considered only after the selected person\'s cover is active and mature, contributions are current, and the request passes eligibility and administrative review. Newly added or changed covered people may have a new waiting period from their effective date.</p>
                    <p>To request Platinum inpatient support, the member must identify the covered person and provide the facility name and location or contact, admission date, requested number of days, and any available admission or doctor reference. SHENA may approve fewer days than requested when the annual balance is lower.</p>
                    <p>A request may be declined where the person is not selected for Platinum, the admission is not an inpatient case, the annual 20-day balance is exhausted, the facility details cannot be verified, the admission falls outside the active cover period, contributions are overdue, or false information has been provided. Unused days expire at year-end and do not carry forward.</p>
                '],
                ['id' => 'pb-benefits', 'num' => '5', 'title' => 'Benefits — Last Respect Services', 'body' => '
                    <p>Upon approval of a valid claim, SHENA will coordinate and provide the following services directly:</p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 16px 0;">
                        ' . implode('', array_map(function($b) {
                            return '<div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: start; gap: 10px;"><i class="fas fa-check-circle" style="color: #7F3D9E; margin-top: 2px; flex-shrink: 0;"></i><span style="font-size: 0.92rem;">'.$b.'</span></div>';
                        }, [
                            'Mortuary fees for up to <strong>14 days</strong>',
                            'Professional body dressing and preparation',
                            'Hearse transportation (morgue to burial site)',
                            'One executive-grade coffin',
                            'Burial lowering gear and trolleys',
                            'High-quality gazebo tents for the ceremony',
                            '100 chairs for funeral seating',
                        ])) . '
                    </div>
                    <p style="color: #DC2626; font-size: 0.9rem; margin-top: 12px;"><i class="fas fa-exclamation-triangle me-1"></i> <strong>Important:</strong> SHENA covers mortuary fees only from the point of claim approval. Hospital admission fees, initial morgue entry charges, and any costs incurred before claim submission are the responsibility of the member\'s family.</p>
                    <p>All services are provided <strong>in-kind</strong> through SHENA-authorized service providers. Benefits cannot be converted to cash payments.</p>
                    <div style="margin-top: 28px; padding-top: 22px; border-top: 1px solid #E9DDF2;">
                        <h3 style="font-family: \'Playfair Display\', serif; color: #2D1A4A; font-size: 1.35rem; margin: 0 0 8px;">Platinum Inpatient Bed-Cover</h3>
                        <p>For a person specifically selected for SHENA Platinum, the following inpatient support applies after the cover is active and mature:</p>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 16px 0;">
                            ' . implode('', array_map(function($b) {
                                return '<div style="background: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 10px; padding: 14px 16px; display: flex; align-items: start; gap: 10px;"><i class="fas fa-check-circle" style="color: #7F3D9E; margin-top: 2px; flex-shrink: 0;"></i><span style="font-size: 0.92rem;">'.$b.'</span></div>';
                            }, [
                                'Up to <strong>20 inpatient bed-cover days</strong> per selected person each year',
                                'Cover may be used across more than one inpatient admission',
                                'The person must be specifically selected and approved for Platinum',
                                'Facility name, location or contact, admission date, and requested days are required',
                                'Requests are subject to active cover, current contributions, and administrative review',
                                'Unused days expire at year-end and do not carry forward',
                            ])) . '
                        </div>
                        <p style="color: #DC2626; font-size: 0.9rem; margin-top: 12px;"><i class="fas fa-exclamation-triangle me-1"></i> <strong>Important:</strong> Platinum is an optional add-on to Basic. It does not replace Last Respect Services, and SHENA may approve fewer days than requested where the remaining annual balance is lower.</p>
                    </div>
                '],
                ['id' => 'pb-dependents', 'num' => '6', 'title' => 'Registered Dependents', 'body' => '
                    <p>Members may register the following persons as covered dependents (subject to package limits):</p>
                    <ul>
                        <li><strong>Spouse</strong> — legal spouse or co-habitant partner (one per member account)</li>
                        <li><strong>Children</strong> — biological or legally adopted children up to age 24 (or age 18 for non-student dependents, unless confirmed enrolled in full-time education)</li>
                        <li><strong>Parents</strong> — biological parents of the member (Executive package; additional contribution may apply)</li>
                        <li><strong>Parents-in-law</strong> — subject to Executive package and SHENA approval</li>
                    </ul>
                    <p>Each dependent must be registered with full name, National ID number (or birth certificate for minors), date of birth, and relationship. Changes to dependent records must be reported to SHENA within <strong>14 days</strong> of the change.</p>
                    <p>A dependent who reaches an age bracket threshold (e.g., turns 70) will automatically move to the applicable higher contribution rate at the next billing cycle.</p>
                '],
                ['id' => 'pb-claims', 'num' => '7', 'title' => 'Claims Procedure', 'body' => '
                    <p><strong>Step 1 — Notify SHENA immediately</strong><br>Contact SHENA as soon as possible after a death occurs. Our team is available on <strong>0748 585 067 / 0748 585 071</strong> (24/7 emergency line).</p>
                    <p><strong>Step 2 — Submit required documents</strong><br>Provide the following within <strong>30 days</strong> of the date of death:</p>
                    <ul>
                        <li>Certified copy of <strong>Death Certificate</strong></li>
                        <li>Copy of the <strong>deceased\'s National ID</strong></li>
                        <li>Copy of the <strong>member\'s National ID</strong></li>
                        <li><strong>Burial Permit</strong></li>
                        <li>Completed <strong>SHENA Claim Form</strong> (available on the member portal)</li>
                    </ul>
                    <p><strong>Step 3 — Verification</strong><br>SHENA management will verify documents and confirm the deceased\'s registration status. This process typically takes <strong>1–3 business days</strong>.</p>
                    <p><strong>Step 4 — Service Authorization</strong><br>Upon approval, SHENA will coordinate directly with service providers (mortuary, hearse operator, casket supplier) to fulfill the claim. The family will be informed of all arrangements.</p>
                    <p style="background: #FEF3C7; border-left: 4px solid #D97706; padding: 12px 16px; border-radius: 6px; margin-top: 16px;"><strong>Note:</strong> Claims submitted beyond 30 days of the date of death may be declined at SHENA\'s discretion.</p>
                '],
                ['id' => 'pb-suspension', 'num' => '8', 'title' => 'Suspension, Lapsing & Reinstatement', 'body' => '
                    <p><strong>Suspension</strong> occurs when contributions fall <strong>3 or more months</strong> in arrears. Suspended members may not file new claims until arrears are fully cleared.</p>
                    <p><strong>Lapsing</strong> occurs when contributions are unpaid for <strong>6 or more consecutive months</strong>. A lapsed membership loses all accrued benefit rights.</p>
                    <p><strong>Reinstatement</strong> of a lapsed account requires:</p>
                    <ul>
                        <li>Payment of all outstanding contribution arrears</li>
                        <li>Payment of a reinstatement administrative fee (determined by SHENA management at the time of reinstatement)</li>
                        <li>Completion of a new waiting period as if a new member</li>
                    </ul>
                '],
                ['id' => 'pb-obligations', 'num' => '9', 'title' => 'Member Obligations', 'body' => '
                    <ul>
                        <li>Pay all monthly contributions on time via the designated M-Pesa channel</li>
                        <li>Register all covered dependents accurately and update records promptly when changes occur</li>
                        <li>Provide honest, accurate information on all registration and claims documents</li>
                        <li>Keep login credentials for the SHENA member portal confidential and secure</li>
                        <li>Inform SHENA of any personal contact detail changes (phone number, address)</li>
                        <li>Treat SHENA staff, agents, and fellow members with respect and dignity</li>
                        <li>Read and remain familiar with the terms of this Policy Booklet</li>
                    </ul>
                '],
                ['id' => 'pb-exclusions', 'num' => '10', 'title' => 'Exclusions', 'body' => '
                    <p>SHENA will <strong>not</strong> provide benefits in the following circumstances:</p>
                    <ul>
                        <li>Death occurring during a waiting period</li>
                        <li>Membership is suspended or lapsed at the time of death</li>
                        <li>The deceased is not registered as a dependent under the relevant member account</li>
                        <li>Fraudulent, forged, or altered claim documents</li>
                        <li>Failure to submit a claim within 30 days of the date of death</li>
                        <li>Hospital fees and morgue admission charges incurred prior to SHENA claim approval</li>
                        <li>Costs of burial land, grave digging, catering, music, or transport costs beyond the defined hearse route</li>
                        <li>Any service not explicitly listed under Section 5 (Benefits)</li>
                    </ul>
                '],
                ['id' => 'pb-termination', 'num' => '11', 'title' => 'Termination of Membership', 'body' => '
                    <p><strong>Voluntary termination:</strong> A member may terminate membership by submitting a written notice to SHENA management. No refunds of contributions or registration fees will be issued.</p>
                    <p><strong>Involuntary termination:</strong> SHENA may terminate membership for:</p>
                    <ul>
                        <li>Persistent contribution default (6+ months arrears)</li>
                        <li>Submission of fraudulent claims or falsified documents</li>
                        <li>Harassment or threats directed at SHENA staff or agents</li>
                        <li>Any material breach of these Terms and Conditions</li>
                    </ul>
                    <p>Upon termination, all benefit rights cease immediately.</p>
                '],
                ['id' => 'pb-amendments', 'num' => '12', 'title' => 'Policy Amendments', 'body' => '
                    <p>SHENA management reserves the right to amend this Policy Booklet, contribution rates, benefit definitions, and operational procedures at any time.</p>
                    <p>All amendments will be communicated to members via <strong>SMS</strong> and/or <strong>email</strong> with a minimum of <strong>30 days\' notice</strong> before taking effect.</p>
                    <p>The current and authoritative version of this Policy Booklet is always available at <a href="/policy-booklet" style="color: #7F3D9E; font-weight: 600;">shenacompanion.ac.ke/policy-booklet</a>.</p>
                '],
                ['id' => 'pb-contact', 'num' => '13', 'title' => 'Contact & Complaints', 'body' => '
                    <p>Members with enquiries, complaints, or formal requests should contact SHENA through the following channels:</p>
                    <div style="background: #F3E8FF; border-radius: 12px; padding: 20px 24px; margin-top: 12px;">
                        <p style="margin: 0 0 10px; font-weight: 700; font-size: 1rem; color: #2D1A4A;">SHENA Companion Welfare Association</p>
                        <p style="margin: 0 0 6px;"><i class="fas fa-map-marker-alt" style="color: #7F3D9E; width: 20px;"></i> P.O. Box 4018, Kisumu, Kenya</p>
                        <p style="margin: 0 0 6px;"><i class="fas fa-phone" style="color: #7F3D9E; width: 20px;"></i> <a href="tel:+254748585067" style="color: #7F3D9E; font-weight: 600;">0748 585 067</a> / <a href="tel:+254748585071" style="color: #7F3D9E; font-weight: 600;">0748 585 071</a> &nbsp;<em style="font-size:0.82rem; color: #6B7280;">(24/7 Emergency)</em></p>
                        <p style="margin: 0;"><i class="fas fa-envelope" style="color: #7F3D9E; width: 20px;"></i> <a href="mailto:info@shenacompanion.co.ke" style="color: #7F3D9E; font-weight: 600;">info@shenacompanion.co.ke</a></p>
                    </div>
                    <p style="margin-top: 16px;">Formal complaints should be submitted in writing (letter or email). SHENA commits to acknowledging all complaints within <strong>5 business days</strong> and providing a resolution or progress update within <strong>21 business days</strong>.</p>
                '],
            ];
            foreach ($pb as $section): ?>
            <div class="section-block" id="<?php echo $section['id']; ?>" style="margin-bottom: 48px;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #F3E8FF;">
                    <div style="width: 38px; height: 38px; background: #7F3D9E; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: white; font-weight: 700; font-size: 1rem;"><?php echo $section['num']; ?></div>
                    <h2 style="font-family: 'Playfair Display', serif; color: #2D1A4A; font-size: 1.3rem; font-weight: 700; margin: 0;"><?php echo $section['title']; ?></h2>
                </div>
                <?php echo $section['body']; ?>
            </div>
            <?php endforeach; ?>

            <!-- Footer signature -->
            <div style="background: linear-gradient(135deg, #2D1A4A, #1A0F2E); color: white; border-radius: 12px; padding: 28px 32px; text-align: center; margin-top: 16px;">
                <img src="/public/images/shena-logo.png" alt="SHENA" style="height: 50px; margin-bottom: 12px; filter: brightness(0) invert(1);">
                <p style="margin: 0 0 6px; font-family: 'Playfair Display', serif; font-size: 1.1rem;">SHENA Companion Welfare Association</p>
                <p style="margin: 0; opacity: 0.6; font-size: 0.82rem;">Document Version: April 2026 &nbsp;|&nbsp; <em>"We Are Royal"</em></p>
            </div>

        </div>
            </div>
        </div>
    </div>
</section>

<?php include VIEWS_PATH . '/layouts/footer.php'; ?>
