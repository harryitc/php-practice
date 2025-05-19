<?php

require_once 'app/models/UserModel.php';

class AuthController
{
    private $userModel;

    public function __construct()
    {
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new UserModel();
    }

    /**
     * Display login form
     */
    public function login()
    {
        // If user is already logged in, redirect to home page
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;

            // Validate input
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            }

            // If no validation errors, attempt to login
            if (empty($errors)) {
                $user = $this->userModel->findByEmail($email);

                if ($user && $user->verifyPassword($password)) {
                    // Set user session
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_name'] = $user->getName();
                    $_SESSION['user_email'] = $user->getEmail();
                    $_SESSION['user_role'] = $user->getRole();

                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                        // In a real application, you would store this token in the database
                    }

                    // Set success message
                    $_SESSION['success_message'] = 'Login successful. Welcome back, ' . $user->getName() . '!';

                    // Redirect admin users to admin dashboard, others to home page or intended page
                    if ($user->getRole() === 'admin') {
                        $redirect = '/Order/dashboard';
                    } else {
                        $redirect = $_SESSION['redirect_after_login'] ?? '/';
                        unset($_SESSION['redirect_after_login']);
                    }

                    header('Location: ' . $redirect);
                    exit();
                } else {
                    $errors[] = 'Invalid email or password';
                }
            }
        }

        // Display login form
        include 'app/views/auth/login.php';
    }

    /**
     * Display registration form
     */
    public function register()
    {
        // If user is already logged in, redirect to home page
        if ($this->isLoggedIn()) {
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif (strlen($name) < 3 || strlen($name) > 50) {
                $errors[] = 'Name must be between 3 and 50 characters';
            }

            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            } elseif ($this->userModel->findByEmail($email)) {
                $errors[] = 'Email is already registered';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            $hash_password = password_hash($password, PASSWORD_DEFAULT);

            // If no validation errors, create user
            if (empty($errors)) {
                $user = new UserModel(null, $name, $email, $hash_password, 'customer');

                if ($user->save()) {
                    // Set success message
                    $_SESSION['success_message'] = 'Registration successful. You can now log in.';

                    // Redirect to login page
                    header('Location: /Auth/login');
                    exit();
                } else {
                    $errors[] = 'Failed to create account. Please try again.';
                }
            }
        }

        // Display registration form
        include 'app/views/auth/register.php';
    }

    /**
     * Log out user
     */
    public function logout()
    {
        // Clear session data
        session_unset();
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Redirect to login page
        header('Location: /Auth/login');
        exit();
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    private function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Redirect to login page if not logged in
     *
     * @param string $redirect Redirect URL after login
     */
    public function requireLogin($redirect = null)
    {
        if (!$this->isLoggedIn()) {
            if ($redirect) {
                $_SESSION['redirect_after_login'] = $redirect;
            }

            header('Location: /Auth/login');
            exit();
        }
    }

    /**
     * Redirect to home page if not admin
     */
    public function requireAdmin()
    {
        $this->requireLogin();

        if ($_SESSION['user_role'] !== 'admin') {
            $_SESSION['error_message'] = 'Access denied. Admin privileges required.';
            header('Location: /');
            exit();
        }
    }

    /**
     * Display user profile
     */
    public function profile()
    {
        // Require login
        $this->requireLogin();

        // Get user data
        $user = $this->userModel->findById($_SESSION['user_id']);

        if (!$user) {
            $_SESSION['error_message'] = 'User not found';
            header('Location: /');
            exit();
        }

        $errors = [];

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get form data
            $name = $_POST['name'] ?? '';
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($name)) {
                $errors[] = 'Name is required';
            } elseif (strlen($name) < 3 || strlen($name) > 50) {
                $errors[] = 'Name must be between 3 and 50 characters';
            }

            // If changing password
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $errors[] = 'Current password is required';
                } elseif (!$user->verifyPassword($currentPassword)) {
                    $errors[] = 'Current password is incorrect';
                }

                if (strlen($newPassword) < 6) {
                    $errors[] = 'New password must be at least 6 characters';
                }

                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match';
                }
            }

            // If no validation errors, update user
            if (empty($errors)) {
                $user->setName($name);

                if (!empty($newPassword)) {
                    $user->setPassword($newPassword);
                }

                if ($user->save()) {
                    // Update session data
                    $_SESSION['user_name'] = $user->getName();

                    // Set success message
                    $_SESSION['success_message'] = 'Profile updated successfully';

                    // Redirect to profile page
                    header('Location: /Auth/profile');
                    exit();
                } else {
                    $errors[] = 'Failed to update profile. Please try again.';
                }
            }
        }

        // Display profile form
        include 'app/views/auth/profile.php';
    }
}
