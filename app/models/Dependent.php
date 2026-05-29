<?php
/**
 * Dependent Model - Manages covered family members under family packages
 */
class Dependent extends BaseModel 
{
    protected $table = 'dependents';
    
    /**
     * Get all dependents for a member
     * 
     * @param int $memberId Member ID
     * @param bool $coveredOnly Only return covered dependents
     * @return array List of dependents
     */
    public function getMemberDependents($memberId, $coveredOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE member_id = :member_id";
        
        if ($coveredOnly) {
            $sql .= " AND is_covered = 1";
        }
        
        $sql .= " ORDER BY relationship, full_name";
        
        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }
    
    /**
     * Get dependents by relationship type
     * 
     * @param int $memberId Member ID
     * @param string $relationship Relationship type (spouse, child, parent, etc.)
     * @return array List of dependents
     */
    public function getDependentsByRelationship($memberId, $relationship)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE member_id = :member_id 
                AND relationship = :relationship 
                AND is_covered = 1 
                ORDER BY full_name";
        
        return $this->db->fetchAll($sql, [
            'member_id' => $memberId,
            'relationship' => $relationship
        ]);
    }
    
    /**
     * Count dependents by relationship
     * 
     * @param int $memberId Member ID
     * @return array Counts by relationship
     */
    public function countDependentsByRelationship($memberId)
    {
        $sql = "SELECT 
                    relationship,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE member_id = :member_id 
                AND is_covered = 1
                GROUP BY relationship";
        
        $results = $this->db->fetchAll($sql, ['member_id' => $memberId]);
        
        $counts = [
            'spouse' => 0,
            'child' => 0,
            'parent' => 0,
            'father_in_law' => 0,
            'mother_in_law' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['relationship']] = (int)$row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Add a dependent to a member's coverage
     * 
     * @param array $data Dependent data
     * @return int Dependent ID
     * @throws Exception If validation fails
     */
    public function addDependent($data)
    {
        // Validate required fields
        $required = ['member_id', 'full_name', 'relationship', 'date_of_birth', 'gender'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: {$field}");
            }
        }
        
        // Validate relationship type
        $validRelationships = ['spouse', 'child', 'parent', 'father_in_law', 'mother_in_law'];
        if (!in_array($data['relationship'], $validRelationships)) {
            throw new Exception("Invalid relationship type");
        }
        
        // Calculate age
        $age = calculateAge($data['date_of_birth']);
        
        // Validate children are under 18
        if ($data['relationship'] === 'child' && $age >= 18) {
            throw new Exception("Children must be under 18 years old to be added as dependents");
        }
        
        // Ensure spouse is of eligible age
        if ($data['relationship'] === 'spouse' && ($age < 18 || $age > 100)) {
            throw new Exception("Spouse must be between 18 and 100 years old");
        }
        
        // Validate only one spouse
        if ($data['relationship'] === 'spouse') {
            $existing = $this->getDependentsByRelationship($data['member_id'], 'spouse');
            if (!empty($existing)) {
                throw new Exception("Member already has a spouse registered");
            }
        }
        
        // Set coverage start date
        if (!isset($data['coverage_start_date'])) {
            $data['coverage_start_date'] = date('Y-m-d');
        }
        
        // Set default values
        $data['is_covered'] = isset($data['is_covered']) ? $data['is_covered'] : true;
        
        return $this->create($data);
    }
    
    /**
     * Update dependent information
     * 
     * @param int $dependentId Dependent ID
     * @param array $data Updated data
     * @return bool Success status
     */
    public function updateDependent($dependentId, $data)
    {
        // Don't allow changing member_id
        unset($data['member_id']);
        
        return $this->update($dependentId, $data);
    }
    
    /**
     * Remove dependent from coverage (soft delete by marking as not covered)
     * 
     * @param int $dependentId Dependent ID
     * @param string $reason Reason for removal
     * @return bool Success status
     */
    public function removeFromCoverage($dependentId, $reason = null)
    {
        $data = [
            'is_covered' => false,
            'coverage_end_date' => date('Y-m-d'),
            'notes' => $reason
        ];
        
        return $this->update($dependentId, $data);
    }
    
    /**
     * Check if children have turned 18 and need to be removed from coverage
     * This should be run as a scheduled task
     * 
     * @return array List of dependents that were aged out
     */
    public function checkChildrenAgeEligibility()
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE relationship = 'child' 
                AND is_covered = 1 
                AND TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 18";
        
        $agedOutChildren = $this->db->fetchAll($sql);
        
        foreach ($agedOutChildren as $child) {
            $this->removeFromCoverage(
                $child['id'], 
                'Child turned 18 years old - automatic coverage termination'
            );
        }
        
        return $agedOutChildren;
    }
    
    /**
     * Validate package limits for dependents
     * 
     * @param int $memberId Member ID
     * @param string $packageKey Package key from configuration
     * @return array Validation result [valid => bool, message => string, limits => array]
     */
    public function validatePackageLimits($memberId, $packageKey)
    {
        global $membership_packages;
        
        if (!isset($membership_packages[$packageKey])) {
            return [
                'valid' => false,
                'message' => 'Invalid package',
                'limits' => []
            ];
        }
        
        $package = $membership_packages[$packageKey];
        $counts = $this->countDependentsByRelationship($memberId);
        
        $errors = [];
        
        // Check spouse limit (max 1)
        if ($package['coverage_type'] !== 'principal_only' && $counts['spouse'] > 1) {
            $errors[] = "Only one spouse allowed";
        }
        
        // Check children limit
        if (isset($package['max_children']) && $counts['child'] > $package['max_children']) {
            $errors[] = "Maximum {$package['max_children']} children allowed for this package";
        }
        
        // Check parents limit (both parents count towards limit)
        if (isset($package['max_parents'])) {
            $parentCount = $counts['parent'];
            if ($parentCount > $package['max_parents']) {
                $errors[] = "Maximum {$package['max_parents']} parents allowed for this package";
            }
        }
        
        // Check in-laws limit
        if (isset($package['max_inlaws'])) {
            $inlawCount = $counts['father_in_law'] + $counts['mother_in_law'];
            if ($inlawCount > $package['max_inlaws']) {
                $errors[] = "Maximum {$package['max_inlaws']} in-laws allowed for this package";
            }
        }
        
        // Check if package allows dependents at all
        if ($package['coverage_type'] === 'principal_only') {
            $totalDependents = array_sum($counts);
            if ($totalDependents > 0) {
                $errors[] = "This package does not cover dependents";
            }
        }
        
        return [
            'valid' => empty($errors),
            'message' => implode('; ', $errors),
            'limits' => [
                'max_children' => $package['max_children'] ?? 0,
                'max_parents' => $package['max_parents'] ?? 0,
                'max_inlaws' => $package['max_inlaws'] ?? 0,
                'current_counts' => $counts
            ]
        ];
    }
    
    /**
     * Get dependent by ID with member info
     * 
     * @param int $dependentId Dependent ID
     * @return array|null Dependent details
     */
    public function getDependentWithMember($dependentId)
    {
        $sql = "SELECT d.*, m.member_number, u.first_name as member_first_name, 
                       u.last_name as member_last_name
                FROM {$this->table} d
                JOIN members m ON d.member_id = m.id
                JOIN users u ON m.user_id = u.id
                WHERE d.id = :id";
        
        return $this->db->fetch($sql, ['id' => $dependentId]);
    }
    
    /**
     * Search dependents by name or ID number
     * 
     * @param string $searchTerm Search term
     * @return array List of dependents
     */
    public function searchDependents($searchTerm)
    {
        $sql = "SELECT d.*, m.member_number, u.first_name as member_first_name, 
                       u.last_name as member_last_name
                FROM {$this->table} d
                JOIN members m ON d.member_id = m.id
                JOIN users u ON m.user_id = u.id
                WHERE d.full_name LIKE :dependent_name_search
                OR d.id_number LIKE :dependent_id_search
                OR d.birth_certificate LIKE :dependent_birth_cert_search
                ORDER BY d.full_name";
        $term = "%{$searchTerm}%";

        return $this->db->fetchAll($sql, [
            'dependent_name_search' => $term,
            'dependent_id_search' => $term,
            'dependent_birth_cert_search' => $term,
        ]);
    }
}
