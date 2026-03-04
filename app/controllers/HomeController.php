<?php
/**
 * Home Controller - Handles public website pages
 */
class HomeController extends BaseController 
{
    public function index()
    {
        $data = [
            'title' => 'Welcome to Shena Companion Welfare Association',
            'page' => 'home'
        ];
        
        $this->view('public.home', $data);
    }
    
    public function about()
    {
        $data = [
            'title' => 'About Us - Shena Companion Welfare Association',
            'page' => 'about'
        ];
        
        $this->view('public.about', $data);
    }
    
    public function membership()
    {
        global $membership_packages;

        $packageTiers = [
            'individual' => ['slug' => 'individual', 'name' => 'Individual', 'price' => null, 'description' => 'Principal member cover', 'features' => ['Main member coverage', 'Mortuary support', 'Body dressing', 'Body transport', 'Executive coffin', 'Burial gear']],
            'couple' => ['slug' => 'couple', 'name' => 'Couple', 'price' => null, 'description' => 'Principal + spouse cover', 'features' => ['Principal + spouse', 'Mortuary support', 'Body dressing', 'Body transport', 'Executive coffin', 'Burial gear']],
            'family' => ['slug' => 'family', 'name' => 'Family', 'price' => null, 'description' => 'Couple and children support', 'features' => ['Couple + children', 'Parents options by plan', 'Mortuary support', 'Body dressing', 'Body transport', 'Burial gear']],
            'executive' => ['slug' => 'executive', 'name' => 'Executive', 'price' => null, 'description' => 'Premium package support', 'features' => ['Priority response', 'Premium support coordination', 'All standard services', 'Enhanced package handling']]
        ];

        foreach ($membership_packages as $key => $package) {
            $category = strtolower((string)($package['category'] ?? ''));

            if ($category === 'extended_family' || $category === 'maximum_family') {
                $category = 'family';
            }

            if (!isset($packageTiers[$category])) {
                continue;
            }

            $price = (int)($package['monthly_contribution'] ?? 0);
            if ($packageTiers[$category]['price'] === null || $price < $packageTiers[$category]['price']) {
                $packageTiers[$category]['price'] = $price;
                $packageTiers[$category]['example_package_key'] = $key;
                $packageTiers[$category]['example_package_name'] = $package['name'] ?? ucfirst($category);
            }
        }

        $packageTiers = array_values($packageTiers);
        
        $data = [
            'title' => 'Membership Packages - Shena Companion Welfare Association',
            'page' => 'membership',
            'packages' => $membership_packages,
            'package_tiers' => $packageTiers
        ];
        
        $this->view('public.membership', $data);
    }
    
    public function services()
    {
        $data = [
            'title' => 'Our Services - Shena Companion Welfare Association',
            'page' => 'services'
        ];
        
        $this->view('public.services', $data);
    }
    
    public function contact()
    {
        $data = [
            'title' => 'Contact Us - Shena Companion Welfare Association',
            'page' => 'contact',
            'csrf_token' => $this->generateCsrfToken()
        ];
        
        $this->view('public.contact', $data);
    }

    public function gallery()
    {
        $data = [
            'title' => 'Gallery - Shena Companion Welfare Association',
            'page' => 'gallery'
        ];

        $this->view('public.gallery', $data);
    }

    public function privacyPolicy()
    {
        $data = [
            'title' => 'Privacy Policy - Shena Companion Welfare Association',
            'page' => 'privacy'
        ];

        $this->view('public.privacy-policy', $data);
    }

    public function termsAndConditions()
    {
        $data = [
            'title' => 'Terms & Conditions - Shena Companion Welfare Association',
            'page' => 'terms'
        ];

        $this->view('public.terms-and-conditions', $data);
    }

    public function policyBooklet()
    {
        $data = [
            'title' => 'Policy Booklet - Shena Companion Welfare Association',
            'page' => 'booklet'
        ];

        $this->view('public.policy-booklet', $data);
    }
    
    public function submitContact()
    {
        try {
            $this->validateCsrf();
            
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $subject = $this->sanitizeInput($_POST['subject'] ?? '');
            $message = $this->sanitizeInput($_POST['message'] ?? '');
            
            // Validate required fields
            if (empty($name) || empty($email) || empty($message)) {
                $_SESSION['error'] = 'Please fill in all required fields.';
                $this->redirect('/contact');
                return;
            }
            
            // Validate email
            if (!$this->validateEmail($email)) {
                $_SESSION['error'] = 'Please enter a valid email address.';
                $this->redirect('/contact');
                return;
            }
            
            // Validate phone if provided
            if (!empty($phone) && !$this->validatePhone($phone)) {
                $_SESSION['error'] = 'Please enter a valid Kenyan phone number.';
                $this->redirect('/contact');
                return;
            }
            
            // Send email notification to admin
            $emailService = new EmailService();
            $emailSent = $emailService->sendContactFormEmail([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
            
            if ($emailSent) {
                $_SESSION['success'] = 'Thank you for contacting us. We will get back to you soon.';
            } else {
                $_SESSION['error'] = 'There was an error sending your message. Please try again.';
            }
            
        } catch (Exception $e) {
            error_log('Contact form error: ' . $e->getMessage());
            $_SESSION['error'] = 'There was an error processing your request. Please try again.';
        }
        
        $this->redirect('/contact');
    }
}
