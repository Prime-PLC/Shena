-- ONE-TIME LEGACY CONVERSION: "medical" corporate placeholders -> principal Platinum.
-- Prerequisites: migrations 018-023 have completed and a database backup exists.
-- Run database/sql/legacy_medical_to_platinum_preview.sql first. Import this
-- file only after approving that preview.
-- A legacy placeholder is identified only by "medical" in its label, relationship, or package name.
-- It is archived before removal. Existing Platinum coverage, missing DOB, and unsupported
-- Basic packages are deliberately excluded and must be corrected manually.

START TRANSACTION;

INSERT IGNORE INTO legacy_medical_corporate_archive (
    legacy_corporate_member_id, member_id, label, relationship, date_of_birth,
    package_key, package_name, monthly_contribution, status, archived_at
)
SELECT c.id, c.member_id, c.label, c.relationship, c.date_of_birth,
       c.package_key, c.package_name, c.monthly_contribution, c.status, NOW()
FROM member_corporate_members c
JOIN members m ON m.id = c.member_id
LEFT JOIN platinum_coverages pc
    ON pc.member_id = m.id
   AND pc.covered_person_type = 'principal'
   AND pc.covered_person_id IS NULL
WHERE (LOWER(COALESCE(c.label, '')) LIKE '%medical%'
    OR LOWER(COALESCE(c.relationship, '')) LIKE '%medical%'
    OR LOWER(COALESCE(c.package_name, '')) LIKE '%medical%')
  AND pc.id IS NULL
  AND m.date_of_birth IS NOT NULL
  AND m.package_key IN (
      'individual_below_70','individual_71_80','individual_81_90','individual_91_100',
      'couple_below_70','couple_children_below_70',
      'couple_children_parents_below_70','couple_children_parents_70_80','couple_children_parents_71_80','couple_children_parents_81_90','couple_children_parents_91_100',
      'couple_children_parents_inlaws_below_70','couple_children_parents_inlaws_71_80','couple_children_parents_inlaws_81_90','couple_children_parents_inlaws_91_100',
      'executive_below_70','executive_above_70'
  );

INSERT INTO platinum_coverages (
    member_id, covered_person_type, covered_person_id, package_key, package_name,
    status, activation_method, monthly_contribution, maturity_months,
    requested_at, approved_at, effective_from, maturity_date,
    override_reason, override_at
)
SELECT q.member_id, 'principal', NULL, q.package_key, q.package_name,
       'active', 'admin_direct', q.platinum_amount,
       IF(q.age < 60, 4, 7), NOW(), NOW(), CURDATE(), DATE_ADD(CURDATE(), INTERVAL IF(q.age < 60, 4, 7) MONTH),
       'Converted from reviewed legacy medical corporate placeholder', NOW()
FROM (
    SELECT DISTINCT m.id AS member_id, CASE WHEN m.package_key = 'couple_children_parents_70_80' THEN 'couple_children_parents_71_80' ELSE m.package_key END AS package_key,
        TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) AS age,
        CASE m.package_key
            WHEN 'individual_below_70' THEN 'Individual 70 Years and Below'
            WHEN 'individual_71_80' THEN 'Individual 71-80 Years'
            WHEN 'individual_81_90' THEN 'Individual 81-90 Years'
            WHEN 'individual_91_100' THEN 'Individual 91-100 Years'
            WHEN 'couple_below_70' THEN 'Couple 70 Years and Below'
            WHEN 'couple_children_below_70' THEN 'Couple & Children 70 Years and Below'
            WHEN 'couple_children_parents_below_70' THEN 'Couple, Children & Parents 70 Years and Below'
            WHEN 'couple_children_parents_71_80' THEN 'Couple, Children & Parents 71-80 Years'
            WHEN 'couple_children_parents_70_80' THEN 'Couple, Children & Parents 71-80 Years' -- legacy stored-key compatibility
            WHEN 'couple_children_parents_81_90' THEN 'Couple, Children & Parents 81-90 Years'
            WHEN 'couple_children_parents_91_100' THEN 'Couple, Children & Parents 91-100 Years'
            WHEN 'couple_children_parents_inlaws_below_70' THEN 'Couple, Children, Parents & In-laws 70 Years and Below'
            WHEN 'couple_children_parents_inlaws_71_80' THEN 'Couple, Children, Parents & In-laws 71-80 Years'
            WHEN 'couple_children_parents_inlaws_81_90' THEN 'Couple, Children, Parents & In-laws 81-90 Years'
            WHEN 'couple_children_parents_inlaws_91_100' THEN 'Couple, Children, Parents & In-laws 91-100 Years'
            WHEN 'executive_below_70' THEN 'Executive Package 70 Years and Below'
            WHEN 'executive_above_70' THEN 'Executive Package Above 70 Years'
        END AS package_name,
        CASE m.package_key
            WHEN 'individual_below_70' THEN 300
            WHEN 'individual_71_80' THEN 550
            WHEN 'individual_81_90' THEN 650
            WHEN 'individual_91_100' THEN 850
            WHEN 'couple_below_70' THEN 350
            WHEN 'couple_children_below_70' THEN 400
            WHEN 'couple_children_parents_below_70' THEN 450
            WHEN 'couple_children_parents_71_80' THEN 550
            WHEN 'couple_children_parents_70_80' THEN 550 -- legacy stored-key compatibility
            WHEN 'couple_children_parents_81_90' THEN 650
            WHEN 'couple_children_parents_91_100' THEN 850
            WHEN 'couple_children_parents_inlaws_below_70' THEN 500
            WHEN 'couple_children_parents_inlaws_71_80' THEN 600
            WHEN 'couple_children_parents_inlaws_81_90' THEN 750
            WHEN 'couple_children_parents_inlaws_91_100' THEN 850
            WHEN 'executive_below_70' THEN 500
            WHEN 'executive_above_70' THEN 700
        END AS platinum_amount
    FROM members m
    JOIN legacy_medical_corporate_archive a ON a.member_id = m.id
    LEFT JOIN platinum_coverages pc ON pc.member_id = m.id AND pc.covered_person_type = 'principal' AND pc.covered_person_id IS NULL
    WHERE pc.id IS NULL
) q
WHERE q.platinum_amount IS NOT NULL;

DELETE c
FROM member_corporate_members c
JOIN legacy_medical_corporate_archive a ON a.legacy_corporate_member_id = c.id
JOIN platinum_coverages pc ON pc.member_id = c.member_id
    AND pc.covered_person_type = 'principal'
    AND pc.covered_person_id IS NULL
    AND pc.override_reason = 'Converted from reviewed legacy medical corporate placeholder';

COMMIT;

-- POST-CHECK: this must show the converted Platinum coverage and no source placeholder.
SELECT pc.member_id, pc.package_name, pc.monthly_contribution, pc.status,
       pc.maturity_date, pc.override_reason
FROM platinum_coverages pc
WHERE pc.override_reason = 'Converted from reviewed legacy medical corporate placeholder'
ORDER BY pc.member_id;
