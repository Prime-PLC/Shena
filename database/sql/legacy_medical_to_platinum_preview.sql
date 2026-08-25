-- READ-ONLY PREVIEW for legacy "medical" corporate placeholders.
-- This script makes no changes. Review every returned row before importing
-- legacy_medical_to_platinum.sql.
SELECT
    c.id AS legacy_corporate_member_id,
    m.id AS member_id,
    m.member_number,
    CONCAT(u.first_name, ' ', u.last_name) AS member_name,
    c.label AS legacy_label,
    m.package_key AS basic_package_key,
    m.date_of_birth AS principal_date_of_birth,
    TIMESTAMPDIFF(YEAR, m.date_of_birth, CURDATE()) AS principal_age,
    pc.id AS existing_principal_platinum_coverage,
    CASE
        WHEN m.date_of_birth IS NULL THEN 'STOP: principal date of birth is missing'
        WHEN pc.id IS NOT NULL THEN 'STOP: a principal Platinum coverage already exists'
        WHEN m.package_key NOT IN (
            'individual_below_70','individual_71_80','individual_81_90','individual_91_100',
            'couple_below_70','couple_children_below_70',
            'couple_children_parents_below_70','couple_children_parents_70_80','couple_children_parents_81_90','couple_children_parents_91_100',
            'couple_children_parents_inlaws_below_70','couple_children_parents_inlaws_71_80','couple_children_parents_inlaws_81_90','couple_children_parents_inlaws_91_100',
            'executive_below_70','executive_above_70'
        ) THEN 'STOP: selected Basic package is not recognised'
        ELSE 'READY: converts to principal Platinum coverage'
    END AS conversion_status
FROM member_corporate_members c
JOIN members m ON m.id = c.member_id
JOIN users u ON u.id = m.user_id
LEFT JOIN platinum_coverages pc
    ON pc.member_id = m.id
   AND pc.covered_person_type = 'principal'
   AND pc.covered_person_id IS NULL
WHERE LOWER(COALESCE(c.label, '')) LIKE '%medical%'
   OR LOWER(COALESCE(c.relationship, '')) LIKE '%medical%'
   OR LOWER(COALESCE(c.package_name, '')) LIKE '%medical%'
ORDER BY m.member_number, c.id;
