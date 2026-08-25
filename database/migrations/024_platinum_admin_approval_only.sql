-- Platinum requests go directly to admin approval; separate payment is not an activation requirement.
UPDATE platinum_coverages
SET status = 'pending_approval'
WHERE status = 'pending_payment';
