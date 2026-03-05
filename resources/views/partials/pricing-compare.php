<?php
$packageTiers = $package_tiers ?? [];
?>
<div style="background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; overflow: hidden; box-shadow: 0 6px 18px rgba(0,0,0,0.06);">
    <div style="padding: 18px 20px; border-bottom: 1px solid #F1F5F9;">
        <h3 style="margin: 0; color: #2D1A4A; font-size: 1.2rem;">Compare Plans</h3>
    </div>
    <div style="overflow-x: auto;">
        <table class="table" style="margin: 0; min-width: 700px;">
            <thead style="background: #F8FAFC;">
                <tr>
                    <th style="padding: 14px;">Plan</th>
                    <th style="padding: 14px;">From (KES / Month)</th>
                    <th style="padding: 14px;">Coverage</th>
                    <th style="padding: 14px;">Best For</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packageTiers as $tier): ?>
                    <tr>
                        <td style="padding: 14px; font-weight: 700;"><?php echo e($tier['name'] ?? ''); ?></td>
                        <td style="padding: 14px;">KES <?php echo number_format((int)($tier['price'] ?? 0)); ?></td>
                        <td style="padding: 14px;"><?php echo e($tier['description'] ?? ''); ?></td>
                        <td style="padding: 14px;"><?php echo e(($tier['slug'] ?? '') === 'executive' ? 'Premium support preference' : 'Affordable everyday protection'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
