<?php
/**
 * Base Controller Class
 */
abstract class BaseController 
{
    protected $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    protected function view($template, $data = [])
    {
        extract($data);
        
        $templatePath = VIEWS_PATH . '/' . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template {$template} not found");
        }
        
        include $templatePath;
    }
    
    protected function json($data, $code = 200)
    {
        if (is_array($data) && !empty($_SESSION['sms_review_ids']) && in_array($_SESSION['user_role'] ?? '', ['super_admin', 'manager', 'agent'], true)) {
            $draftId = (int)end($_SESSION['sms_review_ids']);
            $data['sms_review'] = ['target' => '/sms-review?draft=' . $draftId . '#sms-review-' . $draftId,
                'message' => 'Your action is saved. Review and edit the SMS before sending. No SMS has been sent.'];
        }
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    protected function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
    
    protected function requireAdmin()
    {
        $this->requireAuth();
        
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['super_admin', 'manager'])) {
            $this->redirect('/error/403');
        }
    }
    
    protected function requireRole($roles)
    {
        $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles)) {
            $this->redirect('/error/403');
        }
    }
    
    protected function render($template, $data = [])
    {
        // Alias for view() method
        return $this->view($template, $data);
    }
    
    protected function setFlashMessage($message, $type = 'info')
    {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    protected function validateCsrf()
    {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("CSRF token mismatch");
        }
    }
    
    protected function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    protected function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    protected function validatePhone($phone)
    {
        // Use helper pattern: allow 07..., +2547..., 2547..., or 7...
        $pattern = '/^(\+254|254|0)?(7[0-9]{8})$/';
        return preg_match($pattern, $phone) === 1;
    }
}
