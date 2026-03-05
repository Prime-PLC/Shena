<?php
/**
 * Member Model
 * Manages member information and operations
 * 
 * @package Shena\Models
 */

class Member extends BaseModel
{
    protected $table = 'members';
    
    /**
     * Get all members with optional filters
     * 
     * @param array $filters Optional filters (status, search, package)
     * @return array List of members
     */
    public function getAllMembers($filters = [])
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name, u.role
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (m.member_number LIKE :search 
                      OR u.first_name LIKE :search 
                      OR u.last_name LIKE :search 
                      OR u.email LIKE :search 
                      OR u.phone LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (!empty($filters['package'])) {
            $sql .= " AND m.package = :package";
            $params['package'] = $filters['package'];
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get member by ID
     * 
     * @param int $id Member ID
     * @return array|null Member data or null
     */
    public function getMemberById($id)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id";
        
        return $this->db->fetch($sql, ['id' => $id]);
    }
    
    /**
     * Get member by user ID
     * 
     * @param int $userId User ID
     * @return array|null Member data or null
     */
    public function getMemberByUserId($userId)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.user_id = :user_id";
        
        return $this->db->fetch($sql, ['user_id' => $userId]);
    }
    
    /**
     * Alias for getMemberByUserId
     * 
     * @param int $userId User ID
     * @return array|null Member data or null
     */
    public function findByUserId($userId)
    {
        return $this->getMemberByUserId($userId);
    }

    /**
     * Normalize package identifiers to canonical tier values used by lifecycle flows.
     *
     * @param string|null $packageValue Raw stored package/package key
     * @param array $packageDefinition Optional package config definition
     * @return string
     */
    public function normalizePackageTier($packageValue, array $packageDefinition = [])
    {
        $raw = strtolower((string)$packageValue);
        $category = strtolower((string)($packageDefinition['category'] ?? ''));

        if ($raw === 'executive' || $category === 'executive' || strpos($raw, 'executive') !== false) {
            return 'executive';
        }

        if ($raw === 'couple' || strpos($raw, 'couple') !== false) {
            return 'couple';
        }

        if ($raw === 'family' || strpos($raw, 'family') !== false || in_array($category, ['family', 'extended_family', 'maximum_family'], true)) {
            return 'family';
        }

        return 'individual';
    }
    
    /**
     * Get member by member number
     * 
     * @param string $memberNumber Member number
     * @return array|null Member data or null
     */
    public function getMemberByNumber($memberNumber)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.member_number = :member_number";
        
        return $this->db->fetch($sql, ['member_number' => $memberNumber]);
    }
    
    /**
     * Create a new member
     * 
     * @param array $data Member data
     * @return int|false Member ID or false on failure
     */
    public function createMember($data)
    {
        return $this->create($data);
    }
    
    /**
     * Update member information
     * 
     * @param int $id Member ID
     * @param array $data Update data
     * @return bool Success status
     */
    public function updateMember($id, $data)
    {
        return $this->update($id, $data);
    }
    
    /**
     * Get members registered by a specific agent
     * 
     * @param int $agentId Agent ID
     * @param int $limit Optional limit for results
     * @return array List of members
     */
    public function getMembersByAgent($agentId, $limit = null)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.agent_id = :agent_id
                ORDER BY m.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
            return $this->db->fetchAll($sql, ['agent_id' => $agentId, 'limit' => $limit]);
        }
        
        return $this->db->fetchAll($sql, ['agent_id' => $agentId]);
    }
    
    /**
     * Count total active members
     * 
     * @return int Member count
     */
    public function countActiveMembers()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get members with expiring coverage
     * 
     * @param int $days Days until coverage expires
     * @return array List of members
     */
    public function getMembersWithExpiringCoverage($days = 7)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.coverage_ends IS NOT NULL
                AND m.coverage_ends BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                AND m.status = 'active'
                ORDER BY m.coverage_ends ASC";
        
        return $this->db->fetchAll($sql, ['days' => $days]);
    }
    
    /**
     * Calculate age from date of birth
     * 
     * @param string $dateOfBirth Date of birth (YYYY-MM-DD)
     * @return int Age in years
     */
    public function calculateAge(string $dateOfBirth): int
    {
        if (empty($dateOfBirth) || $dateOfBirth === '0000-00-00') {
            return 0;
        }
        
        $birthDate = new DateTime($dateOfBirth);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }

    /**
     * Calculate the monthly contribution for a member based on age, package and dependents.
     * Centralized logic that uses canonical package definitions from config/packages.php
     *
     * @param array $member Member record (expects at least date_of_birth and package)
     * @param array $dependents Array of dependent records (each may include date_of_birth)
     * @return int Monthly contribution in Ksh
     */
    public function calculateMonthlyContribution($member, $dependents = [])
    {
        global $membership_packages;

        $basePackage = $this->resolveBasePackageForContribution($member, $membership_packages);
        $baseAmount = (int)($basePackage['monthly_contribution'] ?? 100);

        if (empty($dependents)) {
            return $baseAmount;
        }

        $limits = $this->extractDependentCoverageLimits($basePackage);
        $overageAmount = $this->calculateDependentsOverageAmount($dependents, $limits, $membership_packages);

        return $baseAmount + $overageAmount;
    }

    /**
     * Resolve member base package for contribution calculations.
     *
     * @param array $member
     * @param array $membershipPackages
     * @return array
     */
    private function resolveBasePackageForContribution(array $member, array $membershipPackages): array
    {
        $packageKey = $member['package_key'] ?? null;
        if (!empty($packageKey) && isset($membershipPackages[$packageKey])) {
            return $membershipPackages[$packageKey];
        }

        $packageOrKey = $member['package'] ?? null;
        if (!empty($packageOrKey) && isset($membershipPackages[$packageOrKey])) {
            return $membershipPackages[$packageOrKey];
        }

        $age = $this->calculateAge((string) ($member['date_of_birth'] ?? ($member['dob'] ?? '')));
        $requestedCategory = $member['package'] ?? null;

        $matched = $this->findPackageByCategoryAndAge($membershipPackages, $requestedCategory, $age);
        if ($matched !== null) {
            return $matched;
        }

        $fallback = $this->findPackageByCategoryAndAge($membershipPackages, 'individual', $age);
        if ($fallback !== null) {
            return $fallback;
        }

        if (isset($membershipPackages['individual_below_70'])) {
            return $membershipPackages['individual_below_70'];
        }

        return ['monthly_contribution' => 100, 'coverage_type' => 'principal_only'];
    }

    /**
     * Find the cheapest package matching category and age.
     *
     * @param array $membershipPackages
     * @param string|null $category
     * @param int $age
     * @return array|null
     */
    private function findPackageByCategoryAndAge(array $membershipPackages, $category, int $age)
    {
        if (empty($category)) {
            return null;
        }

        $normalizedCategory = strtolower((string)$category);
        $categoryAliasMap = [
            'family' => ['family', 'extended_family', 'maximum_family'],
            'couple' => ['couple'],
            'executive' => ['executive'],
            'individual' => ['individual']
        ];

        $categoryTargets = $categoryAliasMap[$normalizedCategory] ?? [$normalizedCategory];
        $best = null;

        foreach ($membershipPackages as $package) {
            $packageCategory = strtolower((string)($package['category'] ?? ''));
            if (!in_array($packageCategory, $categoryTargets, true)) {
                continue;
            }

            if (isset($package['age_min'], $package['age_max']) && $age > 0) {
                if ($age < (int)$package['age_min'] || $age > (int)$package['age_max']) {
                    continue;
                }
            }

            if ($best === null || (int)$package['monthly_contribution'] < (int)$best['monthly_contribution']) {
                $best = $package;
            }
        }

        return $best;
    }

    /**
     * Extract dependent coverage limits from the selected package.
     *
     * @param array $package
     * @return array
     */
    private function extractDependentCoverageLimits(array $package): array
    {
        $coverageType = (string)($package['coverage_type'] ?? 'principal_only');

        $limits = [
            'spouse' => 0,
            'children' => 0,
            'parents' => 0,
            'inlaws' => 0,
            'other' => 0
        ];

        if ($coverageType !== 'principal_only') {
            $limits['spouse'] = 1;
            $limits['children'] = (int)($package['max_children'] ?? 0);
            $limits['parents'] = (int)($package['max_parents'] ?? 0);
            $limits['inlaws'] = (int)($package['max_inlaws'] ?? 0);
        }

        return $limits;
    }

    /**
     * Calculate extra monthly amount for dependents beyond plan coverage limits.
     *
     * @param array $dependents
     * @param array $limits
     * @param array $membershipPackages
     * @return int
     */
    private function calculateDependentsOverageAmount(array $dependents, array $limits, array $membershipPackages): int
    {
        $counts = [
            'spouse' => 0,
            'children' => 0,
            'parents' => 0,
            'inlaws' => 0,
            'other' => 0
        ];

        $overageAmount = 0;

        foreach ($dependents as $dependent) {
            $bucket = $this->normalizeRelationshipBucket($dependent['relationship'] ?? '');
            $counts[$bucket]++;

            $coveredSlots = (int)($limits[$bucket] ?? 0);
            if ($counts[$bucket] <= $coveredSlots) {
                continue;
            }

            $age = $this->calculateAge((string) ($dependent['date_of_birth'] ?? ($dependent['dob'] ?? '')));
            $overageAmount += $this->getAgeBracketRate($age, $membershipPackages);
        }

        return $overageAmount;
    }

    /**
     * Normalize free-text relationship into a pricing bucket.
     *
     * @param string $relationship
     * @return string
     */
    private function normalizeRelationshipBucket($relationship): string
    {
        $value = strtolower(trim((string)$relationship));
        $value = str_replace(['-', '_'], ' ', $value);

        if ($value === '') {
            return 'other';
        }

        if (strpos($value, 'spouse') !== false || strpos($value, 'wife') !== false || strpos($value, 'husband') !== false) {
            return 'spouse';
        }

        if (strpos($value, 'child') !== false || strpos($value, 'son') !== false || strpos($value, 'daughter') !== false) {
            return 'children';
        }

        if (strpos($value, 'in law') !== false || strpos($value, 'inlaw') !== false || strpos($value, 'father in law') !== false || strpos($value, 'mother in law') !== false) {
            return 'inlaws';
        }

        if (strpos($value, 'parent') !== false || $value === 'father' || $value === 'mother') {
            return 'parents';
        }

        return 'other';
    }

    /**
     * Check whether adding a dependent fits the member's current plan coverage.
     *
     * @param array $member
     * @param array $existingDependents
     * @param string $relationship
     * @return array{allowed: bool, bucket: string, required_package: ?string}
     */
    public function evaluateDependentCoverageForAddition(array $member, array $existingDependents, string $relationship): array
    {
        $packageTier = $this->normalizePackageTier($member['package_key'] ?? ($member['package'] ?? 'individual'));
        $limits = $this->getPlanCoverageLimitsByTier($packageTier);
        $bucket = $this->normalizeRelationshipBucket($relationship);

        $currentCount = 0;
        foreach ($existingDependents as $dependent) {
            $dependentBucket = $this->normalizeRelationshipBucket($dependent['relationship'] ?? '');
            if ($dependentBucket === $bucket) {
                $currentCount++;
            }
        }

        $allowedSlots = (int)($limits[$bucket] ?? 0);
        $allowed = ($currentCount + 1) <= $allowedSlots;

        return [
            'allowed' => $allowed,
            'bucket' => $bucket,
            'required_package' => $allowed ? null : $this->suggestUpgradePackageForBucket($packageTier, $bucket)
        ];
    }

    /**
     * Get relationship slot limits by normalized package tier.
     *
     * @param string $packageTier
     * @return array
     */
    private function getPlanCoverageLimitsByTier(string $packageTier): array
    {
        $default = [
            'spouse' => 0,
            'children' => 0,
            'parents' => 0,
            'inlaws' => 0,
            'other' => 0
        ];

        if ($packageTier === 'couple') {
            $default['spouse'] = 1;
            return $default;
        }

        if ($packageTier === 'family') {
            $default['spouse'] = 1;
            $default['children'] = 10;
            return $default;
        }

        if ($packageTier === 'executive') {
            $default['spouse'] = 1;
            $default['children'] = 10;
            $default['parents'] = 4;
            $default['inlaws'] = 4;
            return $default;
        }

        return $default;
    }

    /**
     * Build plan coverage summary for dependent UI guidance.
     *
     * @param array $member
     * @return array{tier: string, limits: array, total_slots: int}
     */
    public function getPlanCoverageSummary(array $member): array
    {
        $tier = $this->normalizePackageTier($member['package_key'] ?? ($member['package'] ?? 'individual'));
        $limits = $this->getPlanCoverageLimitsByTier($tier);

        return [
            'tier' => $tier,
            'limits' => $limits,
            'total_slots' => array_sum(array_map('intval', $limits))
        ];
    }

    /**
     * Suggest the minimum package needed to cover an additional relationship bucket.
     *
     * @param string $currentPackageTier
     * @param string $bucket
     * @return string|null
     */
    private function suggestUpgradePackageForBucket(string $currentPackageTier, string $bucket)
    {
        if ($currentPackageTier === 'executive') {
            return null;
        }

        if ($bucket === 'spouse') {
            return 'couple';
        }

        if ($bucket === 'children') {
            if ($currentPackageTier === 'individual' || $currentPackageTier === 'couple') {
                return 'family';
            }

            return 'executive';
        }

        if ($bucket === 'parents' || $bucket === 'inlaws' || $bucket === 'other') {
            return 'executive';
        }

        return 'executive';
    }

    /**
     * Get age-bracket monthly rate for an extra dependent.
     * Uses individual package rates as pricing bands.
     *
     * @param int $age
     * @param array $membershipPackages
     * @return int
     */
    private function getAgeBracketRate(int $age, array $membershipPackages): int
    {
        $best = null;

        foreach ($membershipPackages as $package) {
            $isIndividual = ($package['category'] ?? '') === 'individual';
            if (!$isIndividual) {
                continue;
            }

            $min = (int)($package['age_min'] ?? 0);
            $max = (int)($package['age_max'] ?? 200);

            if ($age > 0 && $age >= $min && $age <= $max) {
                if ($best === null || (int)$package['monthly_contribution'] < (int)$best['monthly_contribution']) {
                    $best = $package;
                }
            }
        }

        if ($best !== null) {
            return (int)$best['monthly_contribution'];
        }

        if (isset($membershipPackages['individual_below_70'])) {
            return (int)$membershipPackages['individual_below_70']['monthly_contribution'];
        }

        return 100;
    }
    
    /**
     * Find member by national ID
     * 
     * @param string $nationalId National ID number
     * @return array|null Member data or null
     */
    public function findByNationalId($nationalId)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.id_number = :id_number";
        
        return $this->db->fetch($sql, ['id_number' => $nationalId]);
    }
    
    /**
     * Get last member registered in a specific year (for member number generation)
     * 
     * @param int $year Year (e.g., 2026)
     * @return array|null Last member data or null
     */
    public function getLastMemberByYear($year)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE YEAR(created_at) = :year 
                ORDER BY member_number DESC 
                LIMIT 1";
        
        return $this->db->fetch($sql, ['year' => $year]);
    }
    
    /**
     * Get total members count
     * 
     * @return int Total members
     */
    public function getTotalMembers()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get active members count
     *
     * @return int Active members
     */
    public function getActiveMembers()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'active'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Get inactive members count
     *
     * @return int Inactive members
     */
    public function getInactiveMembers()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'inactive'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get pending members (awaiting activation)
     * 
     * @return array Pending members with details
     */
    public function getPendingMembers()
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name, p.transaction_id
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                LEFT JOIN payments p ON m.id = p.member_id AND p.status = 'pending'
                WHERE m.status = 'inactive' OR m.status = 'pending'
                ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get count of pending members
     * 
     * @return int Count of pending members
     */
    public function getPendingMembersCount()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'inactive' OR status = 'pending'";
        $result = $this->db->fetch($sql);
        return $result['count'] ?? 0;
    }
    
    /**
     * Get recent members
     * 
     * @param int $limit Number of members to retrieve
     * @return array List of recent members
     */
    public function getRecentMembers($limit = 10)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                ORDER BY m.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => $limit]);
    }
    
    /**
     * Get active members list
     *
     * @return array List of active members
     */
    public function getActiveMembersList()
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.status = 'active'
                ORDER BY m.created_at DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get inactive members list
     *
     * @return array List of inactive members
     */
    public function getInactiveMembersList()
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.status = 'inactive'
                ORDER BY m.created_at DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get new registrations between dates
     *
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array List of new members
     */
    public function getNewRegistrations($startDate, $endDate)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE DATE(m.created_at) BETWEEN :start_date AND :end_date
                ORDER BY m.created_at DESC";

        return $this->db->fetchAll($sql, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }

    /**
     * Get member with user details by member ID
     *
     * @param int $memberId Member ID
     * @return array|null Member data with user details or null
     */
    public function getMemberWithUser($memberId)
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name, u.role, u.status as user_status
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE m.id = :id";

        return $this->db->fetch($sql, ['id' => $memberId]);
    }

    /**
     * Refresh member status based on coverage and grace period rules
     *
     * @param int $memberId Member ID
     * @return array|null Updated member data or null
     */
    public function refreshStatusFromCoverage($memberId)
    {
        $member = $this->getMemberWithUser($memberId);
        if (!$member) {
            return null;
        }

        // Get current date
        $today = date('Y-m-d');

        // Check if coverage has expired
        if (!empty($member['coverage_ends']) && $member['coverage_ends'] < $today) {
            // Coverage expired - check grace period
            $gracePeriodDays = 4; // Default grace period
            if (!empty($member['date_of_birth'])) {
                $age = $this->calculateAge((string) ($member['date_of_birth'] ?? ''));
                $gracePeriodDays = ($age >= 80) ? 5 : 4;
            }

            $graceEndDate = date('Y-m-d', strtotime($member['coverage_ends'] . " + {$gracePeriodDays} months"));

            if ($today > $graceEndDate) {
                // Grace period expired - set to defaulted
                $this->update($memberId, ['status' => 'defaulted']);
                $member['status'] = 'defaulted';
            } elseif ($member['status'] !== 'grace_period') {
                // Within grace period - set to grace_period
                $this->update($memberId, ['status' => 'grace_period']);
                $member['status'] = 'grace_period';
            }
        } elseif ($member['status'] === 'defaulted' || $member['status'] === 'grace_period') {
            // Coverage is active again - set to active
            $this->update($memberId, ['status' => 'active']);
            $member['status'] = 'active';
        }

        return $member;
    }

    /**
     * Reactivate a member (set status to active and extend coverage)
     *
     * @param int $memberId Member ID
     * @return bool Success status
     */
    public function reactivateMember($memberId)
    {
        $member = $this->find($memberId);
        if (!$member) {
            return false;
        }

        // Update member status to active
        $updateData = ['status' => 'active'];

        // Policy Section 11: Reset maturity period to 4 months upon reactivation
        $updateData['maturity_ends'] = date('Y-m-d', strtotime('+4 months'));
        $updateData['reactivated_at'] = date('Y-m-d H:i:s');

        // Extend coverage by one year from today if coverage has expired
        if (!empty($member['coverage_ends']) && $member['coverage_ends'] < date('Y-m-d')) {
            $updateData['coverage_ends'] = date('Y-m-d', strtotime('+1 year'));
        }

        return $this->update($memberId, $updateData);
    }
    
    /**
     * Get all members with details and filtering
     * 
     * @param string $search Search term
     * @param string $status Status filter
     * @param string $package Package filter
     * @return array List of members
     */
    public function getAllMembersWithDetails($search = '', $status = 'all', $package = 'all')
    {
        $sql = "SELECT m.*, u.email, u.phone, u.first_name, u.last_name, u.role
                FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (m.member_number LIKE :search 
                      OR u.first_name LIKE :search 
                      OR u.last_name LIKE :search 
                      OR u.email LIKE :search 
                      OR u.phone LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if ($status !== 'all' && !empty($status)) {
            $sql .= " AND m.status = :status";
            $params['status'] = $status;
        }
        
        if ($package !== 'all' && !empty($package)) {
            $sql .= " AND m.package = :package";
            $params['package'] = $package;
        }
        
        $sql .= " ORDER BY m.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Find member by ID number (for payment reconciliation)
     */
    public function findByIdNumber($idNumber)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_number = :id_number LIMIT 1";
        return $this->db->fetch($sql, ['id_number' => $idNumber]);
    }
    
    /**
     * Find member by member number (for payment reconciliation)
     */
    public function findByMemberNumber($memberNumber)
    {
        $sql = "SELECT * FROM {$this->table} WHERE member_number = :member_number LIMIT 1";
        return $this->db->fetch($sql, ['member_number' => $memberNumber]);
    }
    
    /**
     * Find member by phone number (for payment reconciliation)
     */
    public function findByPhone($phone)
    {
        $sql = "SELECT m.* FROM {$this->table} m
                JOIN users u ON m.user_id = u.id
                WHERE u.phone = :phone
                LIMIT 1";
        return $this->db->fetch($sql, ['phone' => $phone]);
    }
    
    /**
     * Search members by multiple criteria
     */
    public function search($searchTerm)
    {
        $query = "SELECT m.*, u.first_name, u.last_name, u.phone, u.email
                  FROM members m
                  JOIN users u ON m.user_id = u.id
                  WHERE m.member_number LIKE :search
                  OR m.id_number LIKE :search
                  OR u.first_name LIKE :search
                  OR u.last_name LIKE :search
                  OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search
                  LIMIT 10";
        
        return $this->db->fetchAll($query, ['search' => "%{$searchTerm}%"]);
    }
    
    /**
     * Get member dependents/beneficiaries
     * 
     * @param int $memberId Member ID
     * @return array List of dependents
     */
    public function getMemberDependents($memberId)
    {
        $sql = "SELECT * FROM beneficiaries 
                WHERE member_id = :member_id 
                ORDER BY relationship, created_at";
        
        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }
    
    /**
     * Get member payment history
     * 
     * @param int $memberId Member ID
     * @param int $limit Optional limit for results
     * @return array List of payments
     */
    public function getMemberPaymentHistory($memberId, $limit = null)
    {
        $sql = "SELECT p.*, p.created_at as payment_date
                FROM payments p
                WHERE p.member_id = :member_id
                AND p.status = 'completed'
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        return $this->db->fetchAll($sql, ['member_id' => $memberId]);
    }
    
    /**
     * Get member growth statistics
     * Returns the percentage growth of members compared to previous month
     * 
     * @return array Growth statistics
     */
    public function getMemberGrowth()
    {
        // Get current month member count
        $currentMonthSql = "SELECT COUNT(*) as count 
                           FROM members 
                           WHERE YEAR(created_at) = YEAR(CURRENT_DATE)
                           AND MONTH(created_at) = MONTH(CURRENT_DATE)";
        
        $currentMonth = $this->db->query($currentMonthSql)->fetch();
        $currentCount = $currentMonth ? (int)$currentMonth['count'] : 0;
        
        // Get previous month member count
        $previousMonthSql = "SELECT COUNT(*) as count 
                            FROM members 
                            WHERE YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))
                            AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH))";
        
        $previousMonth = $this->db->query($previousMonthSql)->fetch();
        $previousCount = $previousMonth ? (int)$previousMonth['count'] : 0;
        
        // Calculate percentage growth
        $growth = 0;
        if ($previousCount > 0) {
            $growth = (($currentCount - $previousCount) / $previousCount) * 100;
        } elseif ($currentCount > 0) {
            $growth = 100; // 100% growth if no previous members
        }
        
        return [
            'current_month' => $currentCount,
            'previous_month' => $previousCount,
            'growth_percentage' => round($growth, 2)
        ];
    }
}
