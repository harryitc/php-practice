<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/OrderNotesModel.php';
require_once 'app/models/OrderStatusHistoryModel.php';

class CustomerOrderService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get orders with filters for customer
     */
    public function getOrdersWithFilters($filters, $page = 1, $perPage = 10)
    {
        $sql = "SELECT o.*,
                       COUNT(oi.id) as item_count,
                       GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE o.user_id = :user_id";

        $params = ['user_id' => $filters['user_id']];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (o.order_number LIKE :search OR p.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

        // Get total count
        $countSql = "SELECT COUNT(DISTINCT o.id) as total FROM orders o
                     LEFT JOIN order_items oi ON o.id = oi.order_id
                     LEFT JOIN products p ON oi.product_id = p.id
                     WHERE o.user_id = :user_id";

        if (!empty($filters['status'])) {
            $countSql .= " AND o.status = :status";
        }

        if (!empty($filters['search'])) {
            $countSql .= " AND (o.order_number LIKE :search OR p.name LIKE :search)";
        }

        if (!empty($filters['date_from'])) {
            $countSql .= " AND DATE(o.created_at) >= :date_from";
        }

        if (!empty($filters['date_to'])) {
            $countSql .= " AND DATE(o.created_at) <= :date_to";
        }

        $totalResult = $this->db->query($countSql)->fetch($params);
        $total = $totalResult['total'];
        $totalPages = ceil($total / $perPage);

        // Add pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

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

            // Set additional properties
            $order->setOrderNumber($row['order_number'] ?? '');
            $order->setTaxAmount($row['tax_amount'] ?? 0);
            $order->setShippingAmount($row['shipping_amount'] ?? 0);
            $order->setDiscountAmount($row['discount_amount'] ?? 0);
            $order->setPaymentStatus($row['payment_status'] ?? 'pending');
            $order->setTrackingNumber($row['tracking_number'] ?? '');
            $order->setCarrier($row['carrier'] ?? '');

            // Add custom properties
            $order->itemCount = $row['item_count'];
            $order->productNames = $row['product_names'];

            $orders[] = $order;
        }

        return [
            'orders' => $orders,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * Get order statistics for customer
     */
    public function getOrderStatistics($userId)
    {
        $sql = "SELECT
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_spent,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    AVG(total_amount) as avg_order_value
                FROM orders
                WHERE user_id = :user_id";

        $result = $this->db->query($sql)->fetch(['user_id' => $userId]);

        return [
            'total_orders' => $result['total_orders'] ?? 0,
            'total_spent' => $result['total_spent'] ?? 0,
            'pending_orders' => $result['pending_orders'] ?? 0,
            'processing_orders' => $result['processing_orders'] ?? 0,
            'shipped_orders' => $result['shipped_orders'] ?? 0,
            'delivered_orders' => $result['delivered_orders'] ?? 0,
            'cancelled_orders' => $result['cancelled_orders'] ?? 0,
            'avg_order_value' => $result['avg_order_value'] ?? 0
        ];
    }

    /**
     * Get comprehensive dashboard data
     */
    public function getDashboardData($userId)
    {
        $stats = $this->getOrderStatistics($userId);

        // Get recent orders
        $recentOrders = $this->getOrdersWithFilters(['user_id' => $userId], 1, 5);

        // Get orders by status for chart
        $statusData = [
            'pending' => $stats['pending_orders'],
            'processing' => $stats['processing_orders'],
            'shipped' => $stats['shipped_orders'],
            'delivered' => $stats['delivered_orders'],
            'cancelled' => $stats['cancelled_orders']
        ];

        // Get monthly spending for last 6 months
        $monthlySql = "SELECT
                          DATE_FORMAT(created_at, '%Y-%m') as month,
                          SUM(total_amount) as total
                       FROM orders
                       WHERE user_id = :user_id
                       AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                       ORDER BY month DESC";

        $monthlyResults = $this->db->query($monthlySql)->fetchAll(['user_id' => $userId]);

        $monthlySpending = [];
        foreach ($monthlyResults as $row) {
            $monthlySpending[$row['month']] = $row['total'];
        }

        return [
            'stats' => $stats,
            'recent_orders' => $recentOrders['orders'],
            'status_data' => $statusData,
            'monthly_spending' => $monthlySpending
        ];
    }

    /**
     * Cancel order
     */
    public function cancelOrder($orderId, $reason, $userId)
    {
        try {
            $order = OrderModel::findById($orderId);

            if (!$order || $order->getUserId() != $userId) {
                return false;
            }

            // Check if order can be cancelled
            if (!in_array($order->getStatus(), ['pending', 'confirmed'])) {
                return false;
            }

            // Update order status
            $order->setStatus('cancelled');

            // Add cancellation note
            if (!class_exists('OrderNotesModel')) {
                require_once 'app/models/OrderNotesModel.php';
            }

            OrderNotesModel::addNote(
                $orderId,
                "Order cancelled by customer. Reason: " . $reason,
                $userId,
                'customer',
                'Order Cancelled',
                true,
                'high'
            );

            return $order->save();

        } catch (Exception $e) {
            error_log("Cancel order error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Request return/refund
     */
    public function requestReturn($orderId, $reason, $description, $items, $userId)
    {
        try {
            $order = OrderModel::findById($orderId);

            if (!$order || $order->getUserId() != $userId) {
                return false;
            }

            // Check if order can be returned
            if ($order->getStatus() !== 'delivered') {
                return false;
            }

            // Create return request note
            $noteContent = "Return request submitted.\n";
            $noteContent .= "Reason: " . $reason . "\n";
            $noteContent .= "Description: " . $description . "\n";
            $noteContent .= "Items: " . implode(', ', $items);

            if (!class_exists('OrderNotesModel')) {
                require_once 'app/models/OrderNotesModel.php';
            }

            OrderNotesModel::addNote(
                $orderId,
                $noteContent,
                $userId,
                'customer',
                'Return Request',
                true,
                'high'
            );

            // Update order status to indicate return requested
            $order->setStatus('returned');

            return $order->save();

        } catch (Exception $e) {
            error_log("Return request error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reorder items (add to cart)
     */
    public function reorderItems($orderId, $userId)
    {
        try {
            $order = OrderModel::findById($orderId);

            if (!$order || $order->getUserId() != $userId) {
                return false;
            }

            // Load order items
            $order->loadItems();
            $items = $order->getItems();

            if (empty($items)) {
                return false;
            }

            // Add items to cart
            if (!class_exists('CartModel')) {
                require_once 'app/models/CartModel.php';
            }

            $cartModel = new CartModel();

            foreach ($items as $item) {
                $cartModel->addItem($userId, $item->getProductId(), $item->getQuantity());
            }

            return true;

        } catch (Exception $e) {
            error_log("Reorder error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate invoice/receipt
     */
    public function generateInvoice($order)
    {
        try {
            // Load order items
            $order->loadItems();

            // Get customer info
            if (!class_exists('UserModel')) {
                require_once 'app/models/UserModel.php';
            }

            $userModel = new UserModel();
            $customer = $userModel->findById($order->getUserId());

            if (!$customer) {
                throw new Exception("Customer not found");
            }

            // Set headers for HTML download
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="invoice_' . ($order->getOrderNumber() ?: 'order_' . $order->getId()) . '.html"');

            // Generate HTML invoice
            $html = $this->generateInvoiceHTML($order, $customer);

            echo $html;
            exit();

        } catch (Exception $e) {
            error_log("Invoice generation error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to generate invoice';
            header('Location: /Order/myOrders');
            exit();
        }
    }

    /**
     * Generate HTML invoice content
     */
    private function generateInvoiceHTML($order, $customer)
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Invoice - ' . htmlspecialchars($order->getOrderNumber()) . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-info { text-align: center; margin-bottom: 20px; }
                .invoice-details { margin-bottom: 30px; }
                .customer-info { margin-bottom: 30px; }
                .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .items-table th { background-color: #f2f2f2; }
                .total-section { text-align: right; margin-bottom: 30px; }
                .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>INVOICE</h1>
            </div>

            <div class="company-info">
                <h2>Your Store</h2>
                <p>123 Business Street<br>
                City, State 12345<br>
                Phone: (555) 123-4567<br>
                Email: info@yourstore.com</p>
            </div>

            <div class="invoice-details">
                <table>
                    <tr>
                        <td><strong>Invoice Number:</strong></td>
                        <td>' . htmlspecialchars($order->getOrderNumber()) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Order Date:</strong></td>
                        <td>' . date('M d, Y', strtotime($order->getCreatedAt())) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Method:</strong></td>
                        <td>' . htmlspecialchars(ucwords(str_replace('_', ' ', $order->getPaymentMethod()))) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Order Status:</strong></td>
                        <td>' . htmlspecialchars($order->getStatusDisplayName()) . '</td>
                    </tr>
                </table>
            </div>

            <div class="customer-info">
                <h3>Bill To:</h3>
                <p><strong>' . htmlspecialchars($customer->getName()) . '</strong><br>
                ' . htmlspecialchars($customer->getEmail()) . '<br>
                ' . nl2br(htmlspecialchars($order->getShippingAddress())) . '<br>
                ' . htmlspecialchars($order->getShippingCity()) . ', ' . htmlspecialchars($order->getShippingState()) . ' ' . htmlspecialchars($order->getShippingZip()) . '<br>
                ' . htmlspecialchars($order->getShippingCountry()) . '</p>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';

        $items = $order->getItems();
        foreach ($items as $item) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($item->getProductName() ?? 'Product #' . $item->getProductId()) . '</td>
                        <td>' . $item->getQuantity() . '</td>
                        <td>$' . number_format($item->getPrice(), 2) . '</td>
                        <td>$' . number_format($item->getSubtotal(), 2) . '</td>
                      </tr>';
        }

        $html .= '</tbody>
            </table>

            <div class="total-section">
                <table style="margin-left: auto;">
                    <tr>
                        <td><strong>Subtotal:</strong></td>
                        <td>$' . number_format($order->getTotalAmount() - $order->getTaxAmount() - $order->getShippingAmount(), 2) . '</td>
                    </tr>';

        if ($order->getTaxAmount() > 0) {
            $html .= '<tr>
                        <td><strong>Tax:</strong></td>
                        <td>$' . number_format($order->getTaxAmount(), 2) . '</td>
                      </tr>';
        }

        if ($order->getShippingAmount() > 0) {
            $html .= '<tr>
                        <td><strong>Shipping:</strong></td>
                        <td>$' . number_format($order->getShippingAmount(), 2) . '</td>
                      </tr>';
        }

        if ($order->getDiscountAmount() > 0) {
            $html .= '<tr>
                        <td><strong>Discount:</strong></td>
                        <td>-$' . number_format($order->getDiscountAmount(), 2) . '</td>
                      </tr>';
        }

        $html .= '<tr style="border-top: 2px solid #000;">
                    <td><strong>Total:</strong></td>
                    <td><strong>$' . number_format($order->getTotalAmount(), 2) . '</strong></td>
                  </tr>
                </table>
            </div>

            <div class="footer">
                <p>Thank you for your business!</p>
                <p>For questions about this invoice, contact us at support@yourstore.com</p>
            </div>
        </body>
        </html>';

        return $html;
    }
}
