<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderItemModel.php';
require_once 'app/models/OrderStatusHistoryModel.php';
require_once 'app/models/OrderTrackingModel.php';
require_once 'app/models/OrderNotesModel.php';

class OrderModel
{
    private $id;
    private $orderNumber;
    private $userId;
    private $totalAmount;
    private $taxAmount;
    private $shippingAmount;
    private $discountAmount;
    private $status;
    private $paymentStatus;
    private $shippingAddress;
    private $shippingCity;
    private $shippingState;
    private $shippingZip;
    private $shippingCountry;
    private $billingAddress;
    private $billingCity;
    private $billingState;
    private $billingZip;
    private $billingCountry;
    private $paymentMethod;
    private $trackingNumber;
    private $carrier;
    private $estimatedDeliveryDate;
    private $actualDeliveryDate;
    private $priority;
    private $source;
    private $notes;
    private $internalNotes;
    private $createdAt;
    private $updatedAt;

    private $items = [];
    private $statusHistory = [];
    private $trackingHistory = [];
    private $orderNotes = [];
    private $db;

    public function __construct($id = null, $userId = null, $totalAmount = 0, $status = 'pending', $shippingAddress = '', $shippingCity = '', $shippingState = '', $shippingZip = '', $shippingCountry = '', $paymentMethod = '', $createdAt = null, $updatedAt = null)
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->orderNumber = $id ? null : $this->generateOrderNumber(); // Only generate for new orders
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->taxAmount = 0;
        $this->shippingAmount = 0;
        $this->discountAmount = 0;
        $this->status = $status;
        $this->paymentStatus = 'pending';
        $this->shippingAddress = $shippingAddress;
        $this->shippingCity = $shippingCity;
        $this->shippingState = $shippingState;
        $this->shippingZip = $shippingZip;
        $this->shippingCountry = $shippingCountry;
        $this->paymentMethod = $paymentMethod;
        $this->trackingNumber = '';
        $this->carrier = '';
        $this->priority = 'normal';
        $this->source = 'website';
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * Find all orders
     *
     * @param array $filters Optional filters
     * @return array
     */
    public function findAll($filters = [])
    {
        $sql = "SELECT * FROM orders WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY created_at DESC";

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
            $order->setEstimatedDeliveryDate($row['estimated_delivery_date'] ?? null);
            $order->setActualDeliveryDate($row['actual_delivery_date'] ?? null);
            $order->setPriority($row['priority'] ?? 'normal');
            $order->setSource($row['source'] ?? 'website');
            $order->setNotes($row['notes'] ?? '');
            $order->setInternalNotes($row['internal_notes'] ?? '');
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Find order by ID
     *
     * @param int $id
     * @return OrderModel|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM orders WHERE id = :id";
        $result = $this->db->query($sql)->fetch(['id' => $id]);

        if (!$result) {
            return null;
        }

        $order = new OrderModel(
            $result['id'],
            $result['user_id'],
            $result['total_amount'],
            $result['status'],
            $result['shipping_address'],
            $result['shipping_city'],
            $result['shipping_state'],
            $result['shipping_zip'],
            $result['shipping_country'],
            $result['payment_method'],
            $result['created_at'],
            $result['updated_at']
        );

        // Set additional properties
        $order->setOrderNumber($result['order_number'] ?? '');
        $order->setTaxAmount($result['tax_amount'] ?? 0);
        $order->setShippingAmount($result['shipping_amount'] ?? 0);
        $order->setDiscountAmount($result['discount_amount'] ?? 0);
        $order->setPaymentStatus($result['payment_status'] ?? 'pending');
        $order->setTrackingNumber($result['tracking_number'] ?? '');
        $order->setCarrier($result['carrier'] ?? '');
        $order->setEstimatedDeliveryDate($result['estimated_delivery_date'] ?? null);
        $order->setActualDeliveryDate($result['actual_delivery_date'] ?? null);
        $order->setPriority($result['priority'] ?? 'normal');
        $order->setSource($result['source'] ?? 'website');
        $order->setNotes($result['notes'] ?? '');
        $order->setInternalNotes($result['internal_notes'] ?? '');

        // Load order items
        $order->loadItems();

        return $order;
    }

    /**
     * Load order items
     */
    public function loadItems()
    {
        if (!$this->id) {
            return;
        }

        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $results = $this->db->query($sql)->fetchAll(['order_id' => $this->id]);

        $this->items = [];
        foreach ($results as $row) {
            $item = new OrderItemModel(
                $row['id'],
                $row['order_id'],
                $row['product_id'],
                $row['quantity'],
                $row['price']
            );
            $this->items[] = $item;
        }
    }

    /**
     * Save order (insert or update)
     *
     * @return bool
     */
    public function save()
    {
        if ($this->id) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Insert new order
     *
     * @return bool
     */
    private function insert()
    {
        $this->db->beginTransaction();

        try {
            // Check if order_number column exists
            $checkSql = "SHOW COLUMNS FROM orders LIKE 'order_number'";
            $hasOrderNumber = $this->db->query($checkSql)->fetch();

            if ($hasOrderNumber) {
                // Use full SQL with order_number
                $sql = "INSERT INTO orders (order_number, user_id, total_amount, status, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, payment_method)
                        VALUES (:order_number, :user_id, :total_amount, :status, :shipping_address, :shipping_city, :shipping_state, :shipping_zip, :shipping_country, :payment_method)";

                $params = [
                    'order_number' => $this->orderNumber,
                    'user_id' => $this->userId,
                    'total_amount' => $this->totalAmount,
                    'status' => $this->status,
                    'shipping_address' => $this->shippingAddress,
                    'shipping_city' => $this->shippingCity,
                    'shipping_state' => $this->shippingState,
                    'shipping_zip' => $this->shippingZip,
                    'shipping_country' => $this->shippingCountry,
                    'payment_method' => $this->paymentMethod
                ];
            } else {
                // Use basic SQL without order_number
                $sql = "INSERT INTO orders (user_id, total_amount, status, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, payment_method)
                        VALUES (:user_id, :total_amount, :status, :shipping_address, :shipping_city, :shipping_state, :shipping_zip, :shipping_country, :payment_method)";

                $params = [
                    'user_id' => $this->userId,
                    'total_amount' => $this->totalAmount,
                    'status' => $this->status,
                    'shipping_address' => $this->shippingAddress,
                    'shipping_city' => $this->shippingCity,
                    'shipping_state' => $this->shippingState,
                    'shipping_zip' => $this->shippingZip,
                    'shipping_country' => $this->shippingCountry,
                    'payment_method' => $this->paymentMethod
                ];
            }

            $result = $this->db->query($sql)->bind($params)->execute();

            if (!$result) {
                throw new Exception("Failed to insert order");
            }

            $this->id = $this->db->lastInsertId();

            // Update order_number if it wasn't set during insert
            if ($hasOrderNumber && !$this->orderNumber) {
                $this->orderNumber = 'ORD-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
                $updateSql = "UPDATE orders SET order_number = :order_number WHERE id = :id";
                $this->db->query($updateSql)->bind([
                    'order_number' => $this->orderNumber,
                    'id' => $this->id
                ])->execute();
            }

            // Save order items
            foreach ($this->items as $item) {
                $item->setOrderId($this->id);
                if (!$item->save()) {
                    throw new Exception("Failed to insert order item");
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            error_log("Order insert error: " . $e->getMessage());
            $this->db->rollback();
            return false;
        }
    }

    /**
     * Update existing order
     *
     * @return bool
     */
    private function update()
    {
        try {
            // Check which columns exist
            $checkSql = "SHOW COLUMNS FROM orders LIKE 'order_number'";
            $hasOrderNumber = $this->db->query($checkSql)->fetch();

            if ($hasOrderNumber) {
                // Use SQL with order_number
                $sql = "UPDATE orders
                        SET order_number = :order_number,
                            user_id = :user_id,
                            total_amount = :total_amount,
                            status = :status,
                            shipping_address = :shipping_address,
                            shipping_city = :shipping_city,
                            shipping_state = :shipping_state,
                            shipping_zip = :shipping_zip,
                            shipping_country = :shipping_country,
                            payment_method = :payment_method
                        WHERE id = :id";

                $params = [
                    'id' => $this->id,
                    'order_number' => $this->orderNumber,
                    'user_id' => $this->userId,
                    'total_amount' => $this->totalAmount,
                    'status' => $this->status,
                    'shipping_address' => $this->shippingAddress,
                    'shipping_city' => $this->shippingCity,
                    'shipping_state' => $this->shippingState,
                    'shipping_zip' => $this->shippingZip,
                    'shipping_country' => $this->shippingCountry,
                    'payment_method' => $this->paymentMethod
                ];
            } else {
                // Use basic SQL without order_number
                $sql = "UPDATE orders
                        SET user_id = :user_id,
                            total_amount = :total_amount,
                            status = :status,
                            shipping_address = :shipping_address,
                            shipping_city = :shipping_city,
                            shipping_state = :shipping_state,
                            shipping_zip = :shipping_zip,
                            shipping_country = :shipping_country,
                            payment_method = :payment_method
                        WHERE id = :id";

                $params = [
                    'id' => $this->id,
                    'user_id' => $this->userId,
                    'total_amount' => $this->totalAmount,
                    'status' => $this->status,
                    'shipping_address' => $this->shippingAddress,
                    'shipping_city' => $this->shippingCity,
                    'shipping_state' => $this->shippingState,
                    'shipping_zip' => $this->shippingZip,
                    'shipping_country' => $this->shippingCountry,
                    'payment_method' => $this->paymentMethod
                ];
            }

            return $this->db->query($sql)->bind($params)->execute();

        } catch (Exception $e) {
            error_log("Order update error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete order
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }

        $sql = "DELETE FROM orders WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }

    /**
     * Add item to order
     *
     * @param OrderItemModel $item
     */
    public function addItem(OrderItemModel $item)
    {
        $this->items[] = $item;
        $this->recalculateTotal();
    }

    /**
     * Recalculate order total
     */
    public function recalculateTotal()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getPrice() * $item->getQuantity();
        }
        $this->totalAmount = $total;
    }

    /**
     * Get order ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get user ID
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user ID
     *
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get total amount
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set total amount
     *
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status)
    {
        $oldStatus = $this->status;
        $this->status = $status;

        // Record status change in history if order exists
        if ($this->id && $oldStatus !== $status) {
            try {
                // Ensure OrderStatusHistoryModel is loaded
                if (!class_exists('OrderStatusHistoryModel')) {
                    require_once 'app/models/OrderStatusHistoryModel.php';
                }

                OrderStatusHistoryModel::createStatusChange($this->id, $oldStatus, $status, $_SESSION['user_id'] ?? null);

                // Send notification for status change
                if (file_exists('app/services/NotificationService.php')) {
                    require_once 'app/services/NotificationService.php';
                    $notificationService = new NotificationService();
                    $notificationService->sendOrderStatusUpdate($this, $oldStatus, $status);
                }
            } catch (Exception $e) {
                error_log("Error updating order status: " . $e->getMessage());
                // Continue execution even if history/notification fails
            }
        }
    }

    /**
     * Get shipping address
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Set shipping address
     *
     * @param string $shippingAddress
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * Get shipping city
     *
     * @return string
     */
    public function getShippingCity()
    {
        return $this->shippingCity;
    }

    /**
     * Set shipping city
     *
     * @param string $shippingCity
     */
    public function setShippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;
    }

    /**
     * Get shipping state
     *
     * @return string
     */
    public function getShippingState()
    {
        return $this->shippingState;
    }

    /**
     * Set shipping state
     *
     * @param string $shippingState
     */
    public function setShippingState($shippingState)
    {
        $this->shippingState = $shippingState;
    }

    /**
     * Get shipping zip
     *
     * @return string
     */
    public function getShippingZip()
    {
        return $this->shippingZip;
    }

    /**
     * Set shipping zip
     *
     * @param string $shippingZip
     */
    public function setShippingZip($shippingZip)
    {
        $this->shippingZip = $shippingZip;
    }

    /**
     * Get shipping country
     *
     * @return string
     */
    public function getShippingCountry()
    {
        return $this->shippingCountry;
    }

    /**
     * Set shipping country
     *
     * @param string $shippingCountry
     */
    public function setShippingCountry($shippingCountry)
    {
        $this->shippingCountry = $shippingCountry;
    }

    /**
     * Get payment method
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Get order items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Get created at timestamp
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updated at timestamp
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    // New getters for enhanced order tracking
    public function getOrderNumber() { return $this->orderNumber; }
    public function getTaxAmount() { return $this->taxAmount; }
    public function getShippingAmount() { return $this->shippingAmount; }
    public function getDiscountAmount() { return $this->discountAmount; }
    public function getPaymentStatus() { return $this->paymentStatus; }
    public function getBillingAddress() { return $this->billingAddress; }
    public function getBillingCity() { return $this->billingCity; }
    public function getBillingState() { return $this->billingState; }
    public function getBillingZip() { return $this->billingZip; }
    public function getBillingCountry() { return $this->billingCountry; }
    public function getTrackingNumber() { return $this->trackingNumber; }
    public function getCarrier() { return $this->carrier; }
    public function getEstimatedDeliveryDate() { return $this->estimatedDeliveryDate; }
    public function getActualDeliveryDate() { return $this->actualDeliveryDate; }
    public function getPriority() { return $this->priority; }
    public function getSource() { return $this->source; }
    public function getNotes() { return $this->notes; }
    public function getInternalNotes() { return $this->internalNotes; }
    public function getStatusHistory() { return $this->statusHistory; }
    public function getTrackingHistory() { return $this->trackingHistory; }
    public function getOrderNotes() { return $this->orderNotes; }

    // New setters for enhanced order tracking
    public function setOrderNumber($orderNumber) { $this->orderNumber = $orderNumber; }
    public function setTaxAmount($taxAmount) { $this->taxAmount = $taxAmount; }
    public function setShippingAmount($shippingAmount) { $this->shippingAmount = $shippingAmount; }
    public function setDiscountAmount($discountAmount) { $this->discountAmount = $discountAmount; }
    public function setPaymentStatus($paymentStatus) { $this->paymentStatus = $paymentStatus; }
    public function setBillingAddress($billingAddress) { $this->billingAddress = $billingAddress; }
    public function setBillingCity($billingCity) { $this->billingCity = $billingCity; }
    public function setBillingState($billingState) { $this->billingState = $billingState; }
    public function setBillingZip($billingZip) { $this->billingZip = $billingZip; }
    public function setBillingCountry($billingCountry) { $this->billingCountry = $billingCountry; }
    public function setTrackingNumber($trackingNumber) { $this->trackingNumber = $trackingNumber; }
    public function setCarrier($carrier) { $this->carrier = $carrier; }
    public function setEstimatedDeliveryDate($estimatedDeliveryDate) { $this->estimatedDeliveryDate = $estimatedDeliveryDate; }
    public function setActualDeliveryDate($actualDeliveryDate) { $this->actualDeliveryDate = $actualDeliveryDate; }
    public function setPriority($priority) { $this->priority = $priority; }
    public function setSource($source) { $this->source = $source; }
    public function setNotes($notes) { $this->notes = $notes; }
    public function setInternalNotes($internalNotes) { $this->internalNotes = $internalNotes; }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $orderNumber = 'ORD-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

            // Check if this order number already exists
            try {
                $checkSql = "SELECT id FROM orders WHERE order_number = :order_number";
                $existing = $this->db->query($checkSql)->fetch(['order_number' => $orderNumber]);

                if (!$existing) {
                    return $orderNumber;
                }
            } catch (Exception $e) {
                // If order_number column doesn't exist, just return the generated number
                return $orderNumber;
            }

            $attempts++;
        } while ($attempts < $maxAttempts);

        // Fallback: use timestamp if all attempts failed
        return 'ORD-' . date('Y') . '-' . time();
    }

    /**
     * Load status history
     */
    public function loadStatusHistory()
    {
        if (!$this->id) {
            return;
        }
        $this->statusHistory = OrderStatusHistoryModel::getByOrderId($this->id);
    }

    /**
     * Load tracking history
     */
    public function loadTrackingHistory()
    {
        if (!$this->id) {
            return;
        }
        $this->trackingHistory = OrderTrackingModel::getByOrderId($this->id);
    }

    /**
     * Load order notes
     */
    public function loadOrderNotes($visibleToCustomer = null)
    {
        if (!$this->id) {
            return;
        }
        $this->orderNotes = OrderNotesModel::getByOrderId($this->id, $visibleToCustomer);
    }

    /**
     * Add tracking update
     */
    public function addTrackingUpdate($status, $location = null, $description = null, $trackingDate = null)
    {
        if (!$this->id) {
            return false;
        }

        $tracking = new OrderTrackingModel(
            null,
            $this->id,
            $this->trackingNumber,
            $this->carrier,
            $status,
            $location,
            $description,
            $trackingDate ?: date('Y-m-d H:i:s')
        );

        return $tracking->save();
    }

    /**
     * Add order note
     */
    public function addNote($content, $userId = null, $noteType = 'internal', $title = null, $isVisibleToCustomer = false, $priority = 'normal')
    {
        if (!$this->id) {
            return false;
        }

        return OrderNotesModel::addNote($this->id, $content, $userId, $noteType, $title, $isVisibleToCustomer, $priority);
    }

    /**
     * Get order summary for display
     */
    public function getSummary()
    {
        return [
            'id' => $this->id,
            'order_number' => $this->orderNumber,
            'total_amount' => $this->totalAmount,
            'status' => $this->status,
            'payment_status' => $this->paymentStatus,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'estimated_delivery' => $this->estimatedDeliveryDate,
            'created_at' => $this->createdAt
        ];
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']);
    }

    /**
     * Check if order can be returned
     */
    public function canBeReturned()
    {
        return $this->status === 'delivered' &&
               $this->actualDeliveryDate &&
               strtotime($this->actualDeliveryDate) > strtotime('-30 days');
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayName()
    {
        $statusNames = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'returned' => 'Returned',
            'refunded' => 'Refunded'
        ];

        return $statusNames[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get payment status display name
     */
    public function getPaymentStatusDisplayName()
    {
        $statusNames = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially Refunded'
        ];

        return $statusNames[$this->paymentStatus] ?? ucfirst($this->paymentStatus);
    }
}
