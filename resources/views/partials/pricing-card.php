<?php
$plan = $plan ?? [];
$planSlug = $plan['slug'] ?? 'plan';
$planName = $plan['name'] ?? 'Plan';
$planPrice = (int)($plan['price'] ?? 0);
$planDescription = $plan['description'] ?? '';
$planFeatures = $plan['features'] ?? [];
$examplePackageName = $plan['example_package_name'] ?? $planName;
?>
<div class="col-lg-3 col-md-6">
    <div style="background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; padding: 24px; height: 100%; box-shadow: 0 8px 20px rgba(0,0,0,0.06); display: flex; flex-direction: column;">
        <h3 style="font-size: 1.2rem; font-weight: 700; color: #2D1A4A; margin-bottom: 6px;"><?php echo e($planName); ?></h3>
        <p style="color: #6B7280; font-size: 0.9rem; margin-bottom: 14px;"><?php echo e($planDescription); ?></p>
        <div style="font-size: 2rem; font-weight: 700; color: #7F3D9E; margin-bottom: 16px;">
            KES <?php echo number_format($planPrice); ?><span style="font-size: 0.9rem; color: #6B7280;"> / month</span>
        </div>

        <ul style="padding-left: 18px; color: #4B5563; font-size: 0.9rem; line-height: 1.8; flex: 1;">
            <?php foreach ($planFeatures as $feature): ?>
                <li><?php echo e($feature); ?></li>
            <?php endforeach; ?>
        </ul>

        <div style="display: flex; gap: 10px; margin-top: 12px;">
            <a href="/register" class="btn" style="flex: 1; background: #7F3D9E; color: #fff; font-weight: 600; border-radius: 10px; text-decoration: none;">Choose Plan</a>
            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#plan-details-<?php echo e($planSlug); ?>" style="flex: 1; border: 1px solid #7F3D9E; color: #7F3D9E; font-weight: 600; border-radius: 10px;">View Details</button>
        </div>
    </div>
</div>

<div class="modal fade" id="plan-details-<?php echo e($planSlug); ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo e($planName); ?> Plan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 8px;"><strong>Reference Package:</strong> <?php echo e($examplePackageName); ?></p>
                <p style="margin-bottom: 8px;"><strong>Monthly Contribution:</strong> KES <?php echo number_format($planPrice); ?></p>
                <p style="margin-bottom: 8px;"><strong>Coverage Type:</strong> <?php echo e($planDescription); ?></p>
                <ul style="padding-left: 18px; margin-bottom: 0;">
                    <?php foreach ($planFeatures as $feature): ?>
                        <li><?php echo e($feature); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
