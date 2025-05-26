<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderModel.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/OrderNotesModel.php';
require_once 'app/models/OrderStatusHistoryModel.php';
require_once 'app/models/UserModel.php';

class AdminOrderService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get comprehensive dashboard data for admin
     */
    public function getDashboardData($dateRange = 30)
    {
        $data = [];
        
        // Basic statistics
        $data['stats'] = $this->getOrderStatistics($dateRange);
        
        // Revenue analytics
        $data['revenue'] = $this->getRevenueAnalytics($dateRange);
        
        // Order trends
        $data['trends'] = $this->getOrderTrends($dateRange);
        
        // Top customers
        $data['top_customers'] = $this->getTopCustomers($dateRange);
        
        // Top products
        $data['top_products'] = $this->getTopProducts($dateRange);
        
        // Recent activities
        $data['recent_activities'] = $this->getRecentActivities();
        
        // Performance metrics
        $data['performance'] = $this->getPerformanceMetrics($dateRange);
        
        return $data;
    }
    
    /**
     * Get order statistics
     */
    public function getOrderStatistics($dateRange = 30)
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_revenue,
                    AVG(total_amount) as avg_order_value,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_orders,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_orders,
                    SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_orders,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_orders
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $result = $this->db->query($sql)->fetch(['days' => $dateRange]);
        
        // Calculate additional metrics
        $result['completion_rate'] = $result['total_orders'] > 0 
            ? ($result['delivered_orders'] / $result['total_orders']) * 100 
            : 0;
        
        $result['cancellation_rate'] = $result['total_orders'] > 0 
            ? ($result['cancelled_orders'] / $result['total_orders']) * 100 
            : 0;
        
        return $result;
    }
    
    /**
     * Get revenue analytics
     */
    public function getRevenueAnalytics($dateRange = 30)
    {
        // Daily revenue for the period
        $dailySql = "SELECT 
                        DATE(created_at) as date,
                        SUM(total_amount) as revenue,
                        COUNT(*) as orders
                     FROM orders 
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                     AND status NOT IN ('cancelled')
                     GROUP BY DATE(created_at)
                     ORDER BY date DESC";
        
        $dailyResults = $this->db->query($dailySql)->fetchAll(['days' => $dateRange]);
        
        // Monthly comparison
        $monthlySql = "SELECT 
                          DATE_FORMAT(created_at, '%Y-%m') as month,
                          SUM(total_amount) as revenue,
                          COUNT(*) as orders
                       FROM orders 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                       AND status NOT IN ('cancelled')
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                       ORDER BY month DESC";
        
        $monthlyResults = $this->db->query($monthlySql)->fetchAll();
        
        return [
            'daily' => $dailyResults,
            'monthly' => $monthlyResults
        ];
    }
    
    /**
     * Get order trends
     */
    public function getOrderTrends($dateRange = 30)
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    AVG(total_amount) as avg_value
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        
        return $this->db->query($sql)->fetchAll(['days' => $dateRange]);
    }
    
    /**
     * Get top customers
     */
    public function getTopCustomers($dateRange = 30, $limit = 10)
    {
        $sql = "SELECT 
                    u.id,
                    u.name,
                    u.email,
                    COUNT(o.id) as order_count,
                    SUM(o.total_amount) as total_spent,
                    AVG(o.total_amount) as avg_order_value,
                    MAX(o.created_at) as last_order_date
                FROM users u
                JOIN orders o ON u.id = o.user_id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                AND o.status NOT IN ('cancelled')
                GROUP BY u.id, u.name, u.email
                ORDER BY total_spent DESC
                LIMIT :limit";
        
        return $this->db->query($sql)->fetchAll([
            'days' => $dateRange,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get top products
     */
    public function getTopProducts($dateRange = 30, $limit = 10)
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.price) as total_revenue,
                    COUNT(DISTINCT oi.order_id) as order_count
                FROM products p
                JOIN order_items oi ON p.id = oi.product_id
                JOIN orders o ON oi.order_id = o.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                AND o.status NOT IN ('cancelled')
                GROUP BY p.id, p.name, p.price
                ORDER BY total_revenue DESC
                LIMIT :limit";
        
        return $this->db->query($sql)->fetchAll([
            'days' => $dateRange,
            'limit' => $limit
        ]);
    }
    
    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 20)
    {
        $sql = "SELECT 
                    'order_created' as activity_type,
                    o.id as order_id,
                    o.order_number,
                    u.name as customer_name,
                    o.total_amount,
                    o.created_at as activity_date,
                    'Order placed' as description
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                
                UNION ALL
                
                SELECT 
                    'status_change' as activity_type,
                    osh.order_id,
                    o.order_number,
                    u.name as customer_name,
                    o.total_amount,
                    osh.created_at as activity_date,
                    CONCAT('Status changed to ', osh.new_status) as description
                FROM order_status_history osh
                JOIN orders o ON osh.order_id = o.id
                JOIN users u ON o.user_id = u.id
                WHERE osh.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                
                ORDER BY activity_date DESC
                LIMIT :limit";
        
        return $this->db->query($sql)->fetchAll(['limit' => $limit]);
    }
    
    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics($dateRange = 30)
    {
        // Average processing time
        $processingTimeSql = "SELECT 
                                AVG(TIMESTAMPDIFF(HOUR, created_at, 
                                    CASE WHEN status = 'shipped' THEN updated_at ELSE NULL END)) as avg_processing_hours
                              FROM orders 
                              WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                              AND status = 'shipped'";
        
        $processingResult = $this->db->query($processingTimeSql)->fetch(['days' => $dateRange]);
        
        // Customer satisfaction (based on delivered vs returned/cancelled)
        $satisfactionSql = "SELECT 
                               SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                               SUM(CASE WHEN status IN ('returned', 'cancelled') THEN 1 ELSE 0 END) as unsatisfied,
                               COUNT(*) as total
                            FROM orders 
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        $satisfactionResult = $this->db->query($satisfactionSql)->fetch(['days' => $dateRange]);
        
        $satisfactionRate = $satisfactionResult['total'] > 0 
            ? ($satisfactionResult['delivered'] / $satisfactionResult['total']) * 100 
            : 0;
        
        return [
            'avg_processing_hours' => round($processingResult['avg_processing_hours'] ?? 0, 1),
            'customer_satisfaction_rate' => round($satisfactionRate, 1),
            'total_processed' => $satisfactionResult['total']
        ];
    }
    
    /**
     * Get orders with advanced filters
     */
    public function getOrdersWithAdvancedFilters($filters, $page = 1, $perPage = 20)
    {
        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
                       COUNT(oi.id) as item_count,
                       GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN order_items oi ON o.id = oi.order_id
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['customer_id'])) {
            $sql .= " AND o.user_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(o.created_at) >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(o.created_at) <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['min_amount'])) {
            $sql .= " AND o.total_amount >= :min_amount";
            $params['min_amount'] = $filters['min_amount'];
        }
        
        if (!empty($filters['max_amount'])) {
            $sql .= " AND o.total_amount <= :max_amount";
            $params['max_amount'] = $filters['max_amount'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (o.order_number LIKE :search OR u.name LIKE :search OR u.email LIKE :search OR p.name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";
        
        // Get total count
        $countSql = str_replace("SELECT o.*, u.name as customer_name, u.email as customer_email,
                       COUNT(oi.id) as item_count,
                       GROUP_CONCAT(p.name SEPARATOR ', ') as product_names", "SELECT COUNT(DISTINCT o.id) as total", $sql);
        $countSql = str_replace("GROUP BY o.id ORDER BY o.created_at DESC", "", $countSql);
        
        $totalResult = $this->db->query($countSql)->fetch($params);
        $total = $totalResult['total'];
        $totalPages = ceil($total / $perPage);
        
        // Add pagination
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;
        
        $results = $this->db->query($sql)->fetchAll($params);
        
        return [
            'orders' => $results,
            'total' => $total,
            'total_pages' => $totalPages,
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }
}
