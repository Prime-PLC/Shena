<?php
/**
 * User Model - Handles user authentication and basic user data
 */
class User extends BaseModel 
{
    /**
     * Get user by ID
     * @param int $id
     * @return array|null
     */
    public function getUserById($id)
    {
        return $this->find($id);
    }
    protected $table = 'users';
    
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        return $this->db->fetch($sql, ['email' => $email]);
    }
    
    public function findByPhone($phone)
    {
        $sql = "SELECT * FROM {$this->table} WHERE phone = :phone";
        return $this->db->fetch($sql, ['phone' => $phone]);
    }
    
    public function createUser($data)
    {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $data['status'] = 'pending'; // New users start as pending
        $data['role'] = 'member'; // Default role
        
        return $this->create($data);
    }
    
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    public function activateUser($userId)
    {
        return $this->update($userId, ['status' => 'active']);
    }
    
    public function deactivateUser($userId)
    {
        return $this->update($userId, ['status' => 'inactive']);
    }
    
    public function getUsersByRole($role)
    {
        return $this->findAll(['role' => $role]);
    }
    
    public function getPendingUsers()
    {
        return $this->findAll(['status' => 'pending'], 'created_at DESC');
    }
    
    /**
     * Find user by member number (member_id)
     * @param string $memberNumber
     * @return array|null
     */
    public function findByMemberNumber($memberNumber)
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN members m ON u.id = m.user_id 
                WHERE m.member_number = :member_number";
        return $this->db->fetch($sql, ['member_number' => $memberNumber]);
    }
    
    /**
     * Find user by national ID number
     * @param string $nationalId
     * @return array|null
     */
    public function findByNationalId($nationalId)
    {
        $sql = "SELECT u.* FROM {$this->table} u 
                INNER JOIN members m ON u.id = m.user_id 
                WHERE m.id_number = :id_number";
        return $this->db->fetch($sql, ['id_number' => $nationalId]);
    }
    
    /**
     * Find user by email, member number, or national ID
     * Tries each credential type in order
     * @param string $credential
     * @return array|null
     */
    public function findByAnyCredential($credential)
    {
        $credential = trim($credential);
        
        // Try email first
        $user = $this->findByEmail($credential);
        if ($user) {
            return $user;
        }
        
        // Try member number
        $user = $this->findByMemberNumber($credential);
        if ($user) {
            return $user;
        }
        
        // Try national ID
        $user = $this->findByNationalId($credential);
        if ($user) {
            return $user;
        }
        
        return null;
    }
}
