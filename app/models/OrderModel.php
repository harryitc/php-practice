<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderItemModel.php';

class OrderModel
{
    private $id;
    private $userId;
    private $totalAmount;
    private $status;
    private $shippingAddress;
    private $shippingCity;
    private $shippingState;
    private $shippingZip;
    private $shippingCountry;
    private $paymentMethod;
    private $createdAt;
    private $updatedAt;

    private $items = [];
    private $db;

    public function __construct($id = null, $userId = null, $totalAmount = 0, $status = 'pending', $shippingAddress = '', $shippingCity = '', $shippingState = '', $shippingZip = '', $shippingCountry = '', $paymentMethod = '', $createdAt = null, $updatedAt = null)
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->userId = $userId;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->shippingAddress = $shippingAddress;
        $this->shippingCity = $shippingCity;
        $this->shippingState = $shippingState;
        $this->shippingZip = $shippingZip;
        $this->shippingCountry = $shippingCountry;
        $this->paymentMethod = $paymentMethod;
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
            $sql = "INSERT INTO orders (user_id, total_amount, status, shipping_address, shipping_city, shipping_state, shipping_zip, shipping_country, payment_method)
                    VALUES (:user_id, :total_amount, :status, :shipping_address, :shipping_city, :shipping_state, :shipping_zip, :shipping_country, :payment_method)";

            $result = $this->db->query($sql)->bind([
                'user_id' => $this->userId,
                'total_amount' => $this->totalAmount,
                'status' => $this->status,
                'shipping_address' => $this->shippingAddress,
                'shipping_city' => $this->shippingCity,
                'shipping_state' => $this->shippingState,
                'shipping_zip' => $this->shippingZip,
                'shipping_country' => $this->shippingCountry,
                'payment_method' => $this->paymentMethod
            ])->execute();

            if (!$result) {
                throw new Exception("Failed to insert order");
            }

            $this->id = $this->db->lastInsertId();

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

        return $this->db->query($sql)->bind([
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
        ])->execute();
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
        $this->status = $status;
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
}
