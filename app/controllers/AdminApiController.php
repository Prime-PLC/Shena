<?php
/**
 * Admin API Controller for AJAX endpoints
 */
class AdminApiController extends BaseController
{
    /**
     * Require admin access (super_admin or manager)
     */
    private function requireAdminAccess()
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) ||
            !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            header('Location: /admin-login');
            exit();
        }
    }
    /**
     * Return a list of members for search/filter (AJAX)
     * GET /admin/api/members?search=...
     */
    public function members()
    {
        $this->requireAdminAccess();
        $search = $_GET['search'] ?? '';
        require_once __DIR__ . '/../models/Member.php';
        $memberModel = new Member();
        $members = $memberModel->getAllMembers(['search' => $search, 'status' => 'active']);
        $members = array_slice($members, 0, 25);
        $result = array_map(function($m) {
            return [
                'id' => $m['id'],
                'member_number' => $m['member_number'],
                'id_number' => $m['id_number'] ?? '',
                'member_name' => trim(($m['first_name'] ?? '') . ' ' . ($m['last_name'] ?? '')),
                'first_name' => $m['first_name'],
                'last_name' => $m['last_name'],
                'email' => $m['email'],
                'phone' => $m['phone']
            ];
        }, $members);
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function memberBeneficiaries($id)
    {
        $this->requireAdminAccess();

        require_once __DIR__ . '/../models/Beneficiary.php';

        $memberId = (int)$id;
        $beneficiaryModel = new Beneficiary();
        $beneficiaries = $beneficiaryModel->getActiveBeneficiaries($memberId);

        $result = array_map(static function ($beneficiary) {
            return [
                'id' => (int)($beneficiary['id'] ?? 0),
                'full_name' => $beneficiary['full_name'] ?? '',
                'relationship' => $beneficiary['relationship'] ?? '',
                'id_number' => $beneficiary['id_number'] ?? '',
            ];
        }, $beneficiaries ?: []);

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}
