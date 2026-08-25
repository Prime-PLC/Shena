-- ONE-TIME REPAIR FOR ACCOUNTS ALREADY CONVERTED FROM LEGACY "MEDICAL".
-- Run this only after checking the SELECT below. It updates the stored Basic
-- baseline for the 94 converted members; it does not change payments, claims,
-- Platinum coverage records, or genuine active corporate members.

SELECT COUNT(DISTINCT pc.member_id) AS converted_members_to_repair
FROM platinum_coverages pc
WHERE pc.covered_person_type = 'principal'
  AND pc.covered_person_id IS NULL
  AND pc.override_reason = 'Converted from reviewed legacy medical corporate placeholder';

START TRANSACTION;

UPDATE members m
JOIN platinum_coverages pc
  ON pc.member_id = m.id
 AND pc.covered_person_type = 'principal'
 AND pc.covered_person_id IS NULL
 AND pc.override_reason = 'Converted from reviewed legacy medical corporate placeholder'
LEFT JOIN (
    SELECT member_id, COALESCE(SUM(monthly_contribution), 0) AS corporate_basic_total
    FROM member_corporate_members
    WHERE status = 'active'
    GROUP BY member_id
) c ON c.member_id = m.id
SET m.monthly_contribution =
    CASE m.package_key
        WHEN 'individual_below_70' THEN 100
        WHEN 'individual_71_80' THEN 350
        WHEN 'individual_81_90' THEN 450
        WHEN 'individual_91_100' THEN 650
        WHEN 'couple_below_70' THEN 150
        WHEN 'couple_children_below_70' THEN 200
        WHEN 'couple_children_parents_below_70' THEN 250
        WHEN 'couple_children_parents_70_80' THEN 350
        WHEN 'couple_children_parents_81_90' THEN 450
        WHEN 'couple_children_parents_91_100' THEN 650
        WHEN 'couple_children_parents_inlaws_below_70' THEN 300
        WHEN 'couple_children_parents_inlaws_71_80' THEN 400
        WHEN 'couple_children_parents_inlaws_81_90' THEN 550
        WHEN 'couple_children_parents_inlaws_91_100' THEN 650
        WHEN 'executive_below_70' THEN 300
        WHEN 'executive_above_70' THEN 500
        ELSE GREATEST(0, m.monthly_contribution - COALESCE(c.corporate_basic_total, 0))
    END + COALESCE(c.corporate_basic_total, 0);

SELECT ROW_COUNT() AS repaired_member_rows;

COMMIT;

-- Confirm no active legacy placeholder remains and review the new Basic
-- baseline alongside each principal Platinum price.
SELECT m.member_number,
       CONCAT_WS(' ', u.first_name, u.last_name) AS member_name,
       m.monthly_contribution AS basic_account_baseline,
       pc.monthly_contribution AS principal_platinum_rate,
       COALESCE(c.corporate_basic_total, 0) AS genuine_corporate_basic_total,
       COALESCE(c.corporate_current_total, 0) AS current_corporate_payable,
       (COALESCE(c.corporate_current_total, 0) + pc.monthly_contribution) AS current_monthly_payable
FROM members m
JOIN users u ON u.id = m.user_id
JOIN platinum_coverages pc
  ON pc.member_id = m.id
 AND pc.covered_person_type = 'principal'
 AND pc.covered_person_id IS NULL
 AND pc.override_reason = 'Converted from reviewed legacy medical corporate placeholder'
LEFT JOIN (
    SELECT cm.member_id,
           COALESCE(SUM(cm.monthly_contribution), 0) AS corporate_basic_total,
           COALESCE(SUM(CASE WHEN cpc.id IS NULL THEN cm.monthly_contribution
                             ELSE cpc.monthly_contribution END), 0) AS corporate_current_total
    FROM member_corporate_members cm
    LEFT JOIN platinum_coverages cpc
      ON cpc.member_id = cm.member_id
     AND cpc.covered_person_type = 'corporate_member'
     AND cpc.covered_person_id = cm.id
     AND cpc.status IN ('pending_payment', 'pending_approval', 'active')
    WHERE cm.status = 'active'
    GROUP BY cm.member_id
) c ON c.member_id = m.id
ORDER BY m.member_number;
