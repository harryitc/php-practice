<?php

require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/OrderStatusHistoryModel.php';
require_once 'app/models/OrderTrackingModel.php';
require_once 'app/models/OrderNotesModel.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/ProductModel.php';
require_once 'app/models/CartModel.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/core/Database.php';

class OrderController
{
    private $authController;
    private $orderModel;
    private $orderItemModel;
    private $userModel;
    private $productModel;
    private $db;

    public function __construct()
    {
        // Only start session if one doesn't already exist
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->authController = new AuthController();
    }

    /**
     * Admin dashboard with order statistics
     */
    public function dashboard()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get order statistics
        $totalOrders = $this->countOrders();
        $pendingOrders = $this->countOrdersByStatus('pending');
        $processingOrders = $this->countOrdersByStatus('processing');
        $shippedOrders = $this->countOrdersByStatus('shipped');
        $deliveredOrders = $this->countOrdersByStatus('delivered');
        $cancelledOrders = $this->countOrdersByStatus('cancelled');

        // Get revenue statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $dailyRevenue = $this->calculateDailyRevenue();

        // Get customer statistics
        $totalCustomers = $this->countCustomers();

        // Get recent orders
        $recentOrders = $this->getRecentOrders(10);

        // Include the dashboard view
        include 'app/views/admin/dashboard.php';
    }

    /**
     * List all orders with pagination and filtering
     */
    public function list()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get query parameters
        $status = $_GET['status'] ?? '';
        $userId = $_GET['user_id'] ?? '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 10; // Number of orders per page

        // Create filters array
        $filters = [
            'status' => $status,
            'user_id' => $userId
        ];

        // Get total number of filtered orders
        $totalOrders = $this->countOrdersWithFilters($filters);
        $totalPages = ceil($totalOrders / $perPage);

        // Ensure page is within valid range
        if ($page < 1) $page = 1;
        if ($page > $totalPages && $totalPages > 0) $page = $totalPages;

        // Calculate offset for pagination
        $offset = ($page - 1) * $perPage;

        // Get orders for current page
        $orders = $this->getOrdersWithFilters($filters, $perPage, $offset);

        // Get all order statuses for filter dropdown
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        // Make variables available to the view
        $currentPage = $page;
        $selectedStatus = $status;
        $selectedUserId = $userId;

        include 'app/views/admin/orders/list.php';
    }

    /**
     * View order details
     *
     * @param int $id Order ID
     */
    public function detail($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($id);

        // If order not found, show error
        if (!$order) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Get customer information
        $customer = $this->userModel->findById($order->getUserId());

        // Include the order detail view
        include 'app/views/admin/orders/detail.php';
    }

    /**
     * Update order status
     *
     * @param int $id Order ID
     */
    public function updateStatus($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($id);

        // If order not found, show error
        if (!$order) {
            include 'app/views/error/not_found.php';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $status = $_POST['status'] ?? '';

            if (in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
                $order->setStatus($status);

                if ($order->save()) {
                    $_SESSION['success_message'] = 'Order status updated successfully.';
                } else {
                    $_SESSION['error_message'] = 'Failed to update order status.';
                }
            } else {
                $_SESSION['error_message'] = 'Invalid order status.';
            }

            header("Location: /Order/detail/{$id}");
            exit();
        }
    }

    /**
     * List all customers
     */
    public function customers()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get all customers
        $customers = $this->userModel->findAll();

        // Include the customers view
        include 'app/views/admin/customers/list.php';
    }

    /**
     * View customer details and orders
     *
     * @param int $id Customer ID
     */
    public function customerDetail($id)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $customer = $this->userModel->findById($id);

        // If customer not found, show error
        if (!$customer) {
            include 'app/views/error/not_found.php';
            return;
        }

        // Get customer's orders
        $orders = $this->getOrdersByUserId($id);

        // Include the customer detail view
        include 'app/views/admin/customers/detail.php';
    }

    /**
     * Revenue statistics page
     */
    public function revenue()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        // Get revenue statistics
        $totalRevenue = $this->calculateTotalRevenue();
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $dailyRevenue = $this->calculateDailyRevenue();

        // Include the revenue statistics view
        include 'app/views/admin/revenue.php';
    }

    /**
     * Count all orders
     *
     * @return int
     */
    private function countOrders()
    {
        $sql = "SELECT COUNT(*) as count FROM orders";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    /**
     * Count orders by status
     *
     * @param string $status
     * @return int
     */
    private function countOrdersByStatus($status)
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE status = :status";
        $result = $this->db->query($sql)->fetch(['status' => $status]);
        return $result['count'];
    }

    /**
     * Count orders with filters
     *
     * @param array $filters
     * @return int
     */
    private function countOrdersWithFilters($filters)
    {
        $sql = "SELECT COUNT(*) as count FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $result = $this->db->query($sql)->fetch($params);
        return $result['count'];
    }

    /**
     * Get orders with filters and pagination
     *
     * @param array $filters
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getOrdersWithFilters($filters, $limit = null, $offset = null)
    {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        $sql .= " ORDER BY created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;

            if ($offset !== null) {
                $sql .= " OFFSET " . (int)$offset;
            }
        }

        $results = $this->db->query($sql)->fetchAll($params);

        $orders = [];
        foreach ($results as $row) {
            $order = new OrderModel(
                $row['id'],
                $row['user_id'],
                $row['total_amount'],
                $row['status'],
                $row['shipping_address'],
                $row['shipping_city'],
                $row['shipping_state'],
                $row['shipping_zip'],
                $row['shipping_country'],
                $row['payment_method'],
                $row['created_at'],
                $row['updated_at']
            );
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return array
     */
    private function getRecentOrders($limit)
    {
        $sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT " . (int)$limit;
        $results = $this->db->query($sql)->fetchAll();

        $orders = [];
        foreach ($results as $row) {
            $order = new OrderModel(
                $row['id'],
                $row['user_id'],
                $row['total_amount'],
                $row['status'],
                $row['shipping_address'],
                $row['shipping_city'],
                $row['shipping_state'],
                $row['shipping_zip'],
                $row['shipping_country'],
                $row['payment_method'],
                $row['created_at'],
                $row['updated_at']
            );
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Get orders by user ID
     *
     * @param int $userId
     * @return array
     */
    private function getOrdersByUserId($userId)
    {
        $filters = ['user_id' => $userId];
        return $this->getOrdersWithFilters($filters);
    }

    /**
     * Count all customers
     *
     * @return int
     */
    private function countCustomers()
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE role = 'customer'";
        $result = $this->db->query($sql)->fetch();
        return $result['count'];
    }

    /**
     * Calculate total revenue
     *
     * @return float
     */
    private function calculateTotalRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Calculate monthly revenue
     *
     * @return float
     */
    private function calculateMonthlyRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Calculate daily revenue
     *
     * @return float
     */
    private function calculateDailyRevenue()
    {
        $sql = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled' AND DATE(created_at) = CURRENT_DATE()";
        $result = $this->db->query($sql)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Helper method to build pagination URL
     *
     * @param int $page
     * @return string
     */
    public function buildPaginationUrl($page)
    {
        $params = $_GET;
        $params['page'] = $page;

        return '/Order/list?' . http_build_query($params);
    }

    /**
     * Helper method to remove a query parameter from URL
     *
     * @param string $param
     * @return string
     */
    public function removeQueryParam($param)
    {
        $params = $_GET;
        unset($params[$param]);

        if (empty($params)) {
            return '/Order/list';
        }

        return '/Order/list?' . http_build_query($params);
    }

    /**
     * Checkout page - display cart items and shipping form
     */
    public function checkout()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Please login to proceed with checkout';
            header('Location: /Auth/login?redirect=/Cart');
            exit();
        }

        // Check if cart is not empty
        $cart = new CartModel();

        if ($cart->isEmpty()) {
            $_SESSION['error_message'] = 'Your cart is empty';
            header('Location: /Cart');
            exit();
        }

        $cartItems = $cart->getCartItemsWithDetails();
        $totalAmount = $cart->getTotalAmount();
        $itemCount = $cart->getItemCount();

        // Get user information for pre-filling form
        $user = $this->userModel->findById($_SESSION['user_id']);

        include 'app/views/order/checkout.php';
    }

    /**
     * Process checkout form and create order
     */
    public function processCheckout()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'Please login to proceed with checkout';
            header('Location: /Auth/login?redirect=/Cart');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/checkout');
            exit();
        }

        // Check if cart is not empty
        $cart = new CartModel();

        if ($cart->isEmpty()) {
            $_SESSION['error_message'] = 'Your cart is empty';
            header('Location: /Cart');
            exit();
        }

        $errors = [];

        // Validate form data
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingCity = trim($_POST['shipping_city'] ?? '');
        $shippingState = trim($_POST['shipping_state'] ?? '');
        $shippingZip = trim($_POST['shipping_zip'] ?? '');
        $shippingCountry = trim($_POST['shipping_country'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? '';

        // Validation
        if (empty($shippingAddress)) {
            $errors[] = 'Shipping address is required';
        }
        if (empty($shippingCity)) {
            $errors[] = 'City is required';
        }
        if (empty($shippingState)) {
            $errors[] = 'State is required';
        }
        if (empty($shippingZip)) {
            $errors[] = 'ZIP code is required';
        }
        if (empty($shippingCountry)) {
            $errors[] = 'Country is required';
        }
        if (!in_array($paymentMethod, ['cod', 'bank_transfer', 'credit_card'])) {
            $errors[] = 'Please select a valid payment method';
        }

        if (!empty($errors)) {
            $_SESSION['checkout_errors'] = $errors;
            $_SESSION['checkout_data'] = $_POST;
            header('Location: /Order/checkout');
            exit();
        }

        // Create order
        try {
            $cartItems = $cart->getCartItemsWithDetails();
            $totalAmount = $cart->getTotalAmount();

            if (empty($cartItems)) {
                $_SESSION['error_message'] = 'Your cart is empty';
                header('Location: /Cart');
                exit();
            }

            $order = new OrderModel(
                null,
                $_SESSION['user_id'],
                $totalAmount,
                'pending',
                $shippingAddress,
                $shippingCity,
                $shippingState,
                $shippingZip,
                $shippingCountry,
                $paymentMethod
            );

            // Add items to order before saving
            foreach ($cartItems as $item) {
                $orderItem = new OrderItemModel(
                    null,
                    null, // Will be set when order is saved
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                );
                $order->addItem($orderItem);
            }

            // Save order (this will also save order items)
            if ($order->save()) {
                $orderId = $order->getId();

                // Update product inventory
                foreach ($cartItems as $item) {
                    $product = $item['product'];
                    if ($product) {
                        $newInventory = $product->getInventoryCount() - $item['quantity'];
                        $product->setInventoryCount(max(0, $newInventory)); // Ensure inventory doesn't go negative
                        $product->save();
                    }
                }

                // Clear cart
                $cart->clearCart();

                $_SESSION['success_message'] = 'Order placed successfully! Order ID: ' . $orderId;
                header('Location: /Order/success/' . $orderId);
                exit();
            } else {
                $_SESSION['error_message'] = 'Failed to create order. Please try again.';
                header('Location: /Order/checkout');
                exit();
            }
        } catch (Exception $e) {
            error_log("Checkout error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while processing your order. Please try again.';
            header('Location: /Order/checkout');
            exit();
        }
    }

    /**
     * Order success page
     */
    public function success($orderId = null)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        if (!$orderId) {
            header('Location: /Product/list');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Product/list');
            exit();
        }

        include 'app/views/order/success.php';
    }

    /**
     * User's order history with enhanced features
     */
    public function myOrders()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        // Get filters from request
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10;

        // Use CustomerOrderService for enhanced functionality
        require_once 'app/services/CustomerOrderService.php';
        $customerOrderService = new CustomerOrderService();

        $filters = [
            'user_id' => $_SESSION['user_id'],
            'status' => $status,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        $result = $customerOrderService->getOrdersWithFilters($filters, $page, $perPage);
        $orders = $result['orders'];
        $totalOrders = $result['total'];
        $totalPages = $result['total_pages'];

        // Get order statistics for dashboard
        $orderStats = $customerOrderService->getOrderStatistics($_SESSION['user_id']);

        include 'app/views/order/my_orders.php';
    }

    /**
     * View specific order details for current user
     */
    public function view($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        include 'app/views/order/view.php';
    }

    /**
     * Order tracking page for customers
     */
    public function tracking($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        // Use TrackingService for comprehensive tracking info
        require_once 'app/services/TrackingService.php';
        $trackingService = new TrackingService();
        $trackingInfo = $trackingService->getOrderTrackingInfo($orderId);

        // Extract data for view
        $trackingHistory = $trackingInfo['tracking_history'];
        $latestTracking = $trackingInfo['latest_tracking'];
        $progressPercentage = $trackingInfo['progress_percentage'];
        $estimatedDelivery = $trackingInfo['estimated_delivery'];
        $isDelayed = $trackingInfo['is_delayed'];
        $nextUpdateExpected = $trackingInfo['next_update_expected'];

        // Load customer notes
        $customerNotes = OrderNotesModel::getCustomerNotes($orderId);

        include 'app/views/order/tracking.php';
    }

    /**
     * Order timeline page for customers - Interactive timeline view
     */
    public function timeline($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        include 'app/views/order/timeline.php';
    }

    /**
     * Admin order tracking management
     */
    public function adminTracking($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/list');
            exit();
        }

        // Load tracking history and get customer info
        $trackingHistory = OrderTrackingModel::getByOrderId($orderId);
        $customer = $this->userModel->findById($order->getUserId());

        include 'app/views/admin/orders/tracking.php';
    }

    /**
     * Admin order timeline management - Complete timeline view
     */
    public function adminTimeline($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/list');
            exit();
        }

        // Get customer info
        $customer = $this->userModel->findById($order->getUserId());

        include 'app/views/admin/orders/timeline.php';
    }

    /**
     * Add tracking update (Admin only)
     */
    public function addTracking($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/adminTracking/' . $orderId);
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/list');
            exit();
        }

        // Use TrackingService for comprehensive tracking update
        require_once 'app/services/TrackingService.php';
        $trackingService = new TrackingService();

        $trackingData = [
            'tracking_number' => $_POST['tracking_number'] ?? '',
            'carrier' => $_POST['carrier'] ?? '',
            'status' => $_POST['status'] ?? '',
            'location' => $_POST['location'] ?? '',
            'description' => $_POST['description'] ?? '',
            'tracking_date' => $_POST['tracking_date'] ?? date('Y-m-d H:i:s'),
            'recipient_name' => $_POST['recipient_name'] ?? '',
            'signature_obtained' => isset($_POST['signature_obtained'])
        ];

        $tracking = $trackingService->createTrackingUpdate($orderId, $trackingData);

        if ($tracking) {
            $_SESSION['success_message'] = 'Tracking update added successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to add tracking update';
        }

        header('Location: /Order/adminTracking/' . $orderId);
        exit();
    }

    /**
     * Add order note (Admin only)
     */
    public function addNote($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/detail/' . $orderId);
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/list');
            exit();
        }

        $noteType = $_POST['note_type'] ?? 'internal';
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $isVisibleToCustomer = isset($_POST['visible_to_customer']);
        $priority = $_POST['priority'] ?? 'normal';

        if (empty($content)) {
            $_SESSION['error_message'] = 'Note content is required';
            header('Location: /Order/detail/' . $orderId);
            exit();
        }

        if (OrderNotesModel::addNote($orderId, $content, $_SESSION['user_id'], $noteType, $title, $isVisibleToCustomer, $priority)) {
            $_SESSION['success_message'] = 'Note added successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to add note';
        }

        header('Location: /Order/detail/' . $orderId);
        exit();
    }

    /**
     * Update order status with history tracking
     */
    public function updateStatusWithHistory($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/detail/' . $orderId);
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        if (!$order) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/list');
            exit();
        }

        $newStatus = $_POST['status'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $notes = $_POST['notes'] ?? '';

        $validStatuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned', 'refunded'];

        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['error_message'] = 'Invalid order status';
            header('Location: /Order/detail/' . $orderId);
            exit();
        }

        try {
            $oldStatus = $order->getStatus();

            // Update order status
            $order->setStatus($newStatus);

            // Add admin note if provided
            if (!empty($notes)) {
                require_once 'app/models/OrderNotesModel.php';
                OrderNotesModel::addNote(
                    $orderId,
                    $notes,
                    $_SESSION['user_id'],
                    'admin',
                    'Status Update: ' . ucfirst(str_replace('_', ' ', $newStatus)),
                    true,
                    'normal'
                );
            }

            if ($order->save()) {
                $_SESSION['success_message'] = 'Order status updated successfully';
            } else {
                $_SESSION['error_message'] = 'Failed to update order status';
            }
        } catch (Exception $e) {
            error_log("Order status update error: " . $e->getMessage());
            $_SESSION['error_message'] = 'An error occurred while updating order status';
        }

        header('Location: /Order/detail/' . $orderId);
        exit();
    }

    /**
     * Generate order report
     */
    public function generateReport()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $status = $_GET['status'] ?? '';

        // Get orders within date range
        $sql = "SELECT o.*, u.name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date";

        $params = [
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        if ($status) {
            $sql .= " AND o.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY o.created_at DESC";

        $db = Database::getInstance();
        $orders = $db->query($sql)->fetchAll($params);

        // Calculate statistics
        $totalOrders = count($orders);
        $totalRevenue = array_sum(array_column($orders, 'total_amount'));
        $statusCounts = [];

        foreach ($orders as $order) {
            $statusCounts[$order['status']] = ($statusCounts[$order['status']] ?? 0) + 1;
        }

        include 'app/views/admin/orders/report.php';
    }

    /**
     * Simulate tracking update (Admin only - for testing)
     */
    public function simulateTracking($orderId)
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/TrackingService.php';
        $trackingService = new TrackingService();

        $tracking = $trackingService->simulateCarrierUpdate($orderId);

        if ($tracking) {
            $_SESSION['success_message'] = 'Tracking update simulated successfully';
        } else {
            $_SESSION['error_message'] = 'Failed to simulate tracking update';
        }

        header('Location: /Order/adminTracking/' . $orderId);
        exit();
    }

    /**
     * Get tracking statistics (Admin only)
     */
    public function trackingStats()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/TrackingService.php';
        $trackingService = new TrackingService();

        $stats = $trackingService->getTrackingStatistics();
        $ordersNeedingUpdates = $trackingService->getOrdersNeedingUpdates();

        include 'app/views/admin/orders/tracking_stats.php';
    }

    /**
     * Export tracking data (Admin only)
     */
    public function exportTracking()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        $dateFrom = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        $sql = "SELECT
                    o.order_number,
                    o.tracking_number,
                    o.carrier,
                    o.status as order_status,
                    ot.status as tracking_status,
                    ot.location,
                    ot.description,
                    ot.tracking_date,
                    u.name as customer_name,
                    u.email as customer_email
                FROM orders o
                LEFT JOIN order_tracking ot ON o.id = ot.order_id
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.created_at BETWEEN :date_from AND :date_to
                ORDER BY o.created_at DESC, ot.tracking_date DESC";

        $db = Database::getInstance();
        $results = $db->query($sql)->fetchAll([
            'date_from' => $dateFrom . ' 00:00:00',
            'date_to' => $dateTo . ' 23:59:59'
        ]);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tracking_export_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Order Number',
            'Tracking Number',
            'Carrier',
            'Order Status',
            'Tracking Status',
            'Location',
            'Description',
            'Tracking Date',
            'Customer Name',
            'Customer Email'
        ]);

        // CSV data
        foreach ($results as $row) {
            fputcsv($output, [
                $row['order_number'],
                $row['tracking_number'],
                $row['carrier'],
                $row['order_status'],
                $row['tracking_status'],
                $row['location'],
                $row['description'],
                $row['tracking_date'],
                $row['customer_name'],
                $row['customer_email']
            ]);
        }

        fclose($output);
        exit();
    }

    /**
     * Customer order dashboard
     */
    public function customerDashboard()
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        require_once 'app/services/CustomerOrderService.php';
        $customerOrderService = new CustomerOrderService();

        // Get comprehensive dashboard data
        $dashboardData = $customerOrderService->getDashboardData($_SESSION['user_id']);

        include 'app/views/order/dashboard.php';
    }

    /**
     * Cancel order (Customer)
     */
    public function cancelOrder($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        // Check if order can be cancelled
        if (!in_array($order->getStatus(), ['pending', 'confirmed'])) {
            $_SESSION['error_message'] = 'This order cannot be cancelled';
            header('Location: /Order/view/' . $orderId);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['reason'] ?? '';

            require_once 'app/services/CustomerOrderService.php';
            $customerOrderService = new CustomerOrderService();

            if ($customerOrderService->cancelOrder($orderId, $reason, $_SESSION['user_id'])) {
                $_SESSION['success_message'] = 'Order cancelled successfully';
                header('Location: /Order/myOrders');
            } else {
                $_SESSION['error_message'] = 'Failed to cancel order';
                header('Location: /Order/view/' . $orderId);
            }
            exit();
        }

        include 'app/views/order/cancel.php';
    }

    /**
     * Request return/refund (Customer)
     */
    public function requestReturn($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        // Check if order can be returned
        if ($order->getStatus() !== 'delivered') {
            $_SESSION['error_message'] = 'Only delivered orders can be returned';
            header('Location: /Order/view/' . $orderId);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $reason = $_POST['reason'] ?? '';
            $description = $_POST['description'] ?? '';
            $items = $_POST['items'] ?? [];

            require_once 'app/services/CustomerOrderService.php';
            $customerOrderService = new CustomerOrderService();

            if ($customerOrderService->requestReturn($orderId, $reason, $description, $items, $_SESSION['user_id'])) {
                $_SESSION['success_message'] = 'Return request submitted successfully';
                header('Location: /Order/view/' . $orderId);
            } else {
                $_SESSION['error_message'] = 'Failed to submit return request';
                header('Location: /Order/view/' . $orderId);
            }
            exit();
        }

        // Load order items for return selection
        $order->loadItems();
        include 'app/views/order/return_request.php';
    }

    /**
     * Reorder functionality
     */
    public function reorder($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        require_once 'app/services/CustomerOrderService.php';
        $customerOrderService = new CustomerOrderService();

        if ($customerOrderService->reorderItems($orderId, $_SESSION['user_id'])) {
            $_SESSION['success_message'] = 'Items added to cart successfully';
            header('Location: /Cart/view');
        } else {
            $_SESSION['error_message'] = 'Failed to add items to cart';
            header('Location: /Order/view/' . $orderId);
        }
        exit();
    }

    /**
     * Download invoice/receipt
     */
    public function downloadInvoice($orderId)
    {
        // Require user to be logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /Auth/login');
            exit();
        }

        $order = $this->orderModel->findById($orderId);

        // Check if order exists and belongs to current user
        if (!$order || $order->getUserId() != $_SESSION['user_id']) {
            $_SESSION['error_message'] = 'Order not found';
            header('Location: /Order/myOrders');
            exit();
        }

        require_once 'app/services/CustomerOrderService.php';
        $customerOrderService = new CustomerOrderService();

        $customerOrderService->generateInvoice($order);
    }

    /**
     * Enhanced admin dashboard
     */
    public function adminDashboard()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/AdminOrderService.php';
        $adminOrderService = new AdminOrderService();

        $dateRange = $_GET['range'] ?? 30;
        $dashboardData = $adminOrderService->getDashboardData($dateRange);

        include 'app/views/admin/orders/admin_dashboard.php';
    }

    /**
     * Advanced order management interface
     */
    public function adminManage()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/AdminOrderService.php';
        $adminOrderService = new AdminOrderService();

        // Get filters
        $filters = [
            'status' => $_GET['status'] ?? '',
            'customer_id' => $_GET['customer_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'min_amount' => $_GET['min_amount'] ?? '',
            'max_amount' => $_GET['max_amount'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);

        $result = $adminOrderService->getOrdersWithAdvancedFilters($filters, $page, $perPage);

        // Get filter options
        $statuses = ['pending', 'confirmed', 'processing', 'packed', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'returned'];

        // Get customers for filter
        $db = Database::getInstance();
        $customersSql = "SELECT DISTINCT u.id, u.name FROM users u JOIN orders o ON u.id = o.user_id ORDER BY u.name";
        $customers = $db->query($customersSql)->fetchAll();

        include 'app/views/admin/orders/manage.php';
    }

    /**
     * Bulk order actions
     */
    public function bulkAction()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Order/adminManage');
            exit();
        }

        $action = $_POST['bulk_action'] ?? '';
        $orderIds = $_POST['order_ids'] ?? [];

        if (empty($action) || empty($orderIds)) {
            $_SESSION['error_message'] = 'Please select an action and orders';
            header('Location: /Order/adminManage');
            exit();
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->orderModel->findById($orderId);
            if (!$order) {
                $errorCount++;
                continue;
            }

            switch ($action) {
                case 'confirm':
                    if ($order->getStatus() === 'pending') {
                        $order->setStatus('confirmed');
                        if ($order->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    }
                    break;

                case 'process':
                    if (in_array($order->getStatus(), ['pending', 'confirmed'])) {
                        $order->setStatus('processing');
                        if ($order->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    }
                    break;

                case 'ship':
                    if (in_array($order->getStatus(), ['confirmed', 'processing', 'packed'])) {
                        $order->setStatus('shipped');
                        if ($order->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    }
                    break;

                case 'cancel':
                    if (!in_array($order->getStatus(), ['delivered', 'cancelled'])) {
                        $order->setStatus('cancelled');
                        if ($order->save()) {
                            $successCount++;
                        } else {
                            $errorCount++;
                        }
                    }
                    break;

                case 'export':
                    // Handle export separately
                    $this->exportSelectedOrders($orderIds);
                    return;
            }
        }

        if ($successCount > 0) {
            $_SESSION['success_message'] = "Successfully processed {$successCount} orders";
        }

        if ($errorCount > 0) {
            $_SESSION['error_message'] = "Failed to process {$errorCount} orders";
        }

        header('Location: /Order/adminManage');
        exit();
    }

    /**
     * Export selected orders
     */
    private function exportSelectedOrders($orderIds)
    {
        $db = Database::getInstance();
        $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';

        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id IN ($placeholders)
                ORDER BY o.created_at DESC";

        $results = $db->query($sql)->fetchAll($orderIds);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="selected_orders_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'Order ID',
            'Order Number',
            'Customer Name',
            'Customer Email',
            'Status',
            'Total Amount',
            'Created Date',
            'Payment Method'
        ]);

        // CSV data
        foreach ($results as $row) {
            fputcsv($output, [
                $row['id'],
                $row['order_number'],
                $row['customer_name'],
                $row['customer_email'],
                $row['status'],
                $row['total_amount'],
                $row['created_at'],
                $row['payment_method']
            ]);
        }

        fclose($output);
        exit();
    }

    /**
     * Order analytics page
     */
    public function analytics()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/AdminOrderService.php';
        $adminOrderService = new AdminOrderService();

        $dateRange = $_GET['range'] ?? 30;
        $analyticsData = $adminOrderService->getDashboardData($dateRange);

        include 'app/views/admin/orders/analytics.php';
    }

    /**
     * Customer management from orders perspective
     */
    public function customerManagement()
    {
        // Require admin privileges
        $this->authController->requireAdmin();

        require_once 'app/services/AdminOrderService.php';
        $adminOrderService = new AdminOrderService();

        $dateRange = $_GET['range'] ?? 30;
        $topCustomers = $adminOrderService->getTopCustomers($dateRange, 50);

        include 'app/views/admin/orders/customers.php';
    }
}
