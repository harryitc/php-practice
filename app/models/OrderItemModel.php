<?php

require_once 'app/core/Database.php';
require_once 'app/models/ProductModel.php';

class OrderItemModel
{
    private $id;
    private $orderId;
    private $productId;
    private $quantity;
    private $price;
    private $createdAt;
    private $updatedAt;
    
    private $product;
    private $db;
    
    public function __construct($id = null, $orderId = null, $productId = null, $quantity = 1, $price = 0)
    {
        $this->db = Database::getInstance();
        
        $this->id = $id;
        $this->orderId = $orderId;
        $this->productId = $productId;
        $this->quantity = $quantity;
        $this->price = $price;
    }
    
    /**
     * Find all order items for an order
     * 
     * @param int $orderId
     * @return array
     */
    public function findByOrderId($orderId)
    {
        $sql = "SELECT * FROM order_items WHERE order_id = :order_id";
        $results = $this->db->query($sql)->fetchAll(['order_id' => $orderId]);
        
        $items = [];
        foreach ($results as $row) {
            $item = new OrderItemModel(
                $row['id'],
                $row['order_id'],
                $row['product_id'],
                $row['quantity'],
                $row['price']
            );
            $items[] = $item;
        }
        
        return $items;
    }
    
    /**
     * Find order item by ID
     * 
     * @param int $id
     * @return OrderItemModel|null
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM order_items WHERE id = :id";
        $result = $this->db->query($sql)->fetch(['id' => $id]);
        
        if (!$result) {
            return null;
        }
        
        return new OrderItemModel(
            $result['id'],
            $result['order_id'],
            $result['product_id'],
            $result['quantity'],
            $result['price']
        );
    }
    
    /**
     * Save order item (insert or update)
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
     * Insert new order item
     * 
     * @return bool
     */
    private function insert()
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (:order_id, :product_id, :quantity, :price)";
        
        $result = $this->db->query($sql)->bind([
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price
        ])->execute();
        
        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Update existing order item
     * 
     * @return bool
     */
    private function update()
    {
        $sql = "UPDATE order_items 
                SET order_id = :order_id, 
                    product_id = :product_id, 
                    quantity = :quantity, 
                    price = :price 
                WHERE id = :id";
        
        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'order_id' => $this->orderId,
            'product_id' => $this->productId,
            'quantity' => $this->quantity,
            'price' => $this->price
        ])->execute();
    }
    
    /**
     * Delete order item
     * 
     * @return bool
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }
        
        $sql = "DELETE FROM order_items WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }
    
    /**
     * Load product data
     */
    public function loadProduct()
    {
        if (!$this->productId) {
            return;
        }
        
        $productModel = new ProductModel();
        $this->product = $productModel->findById($this->productId);
    }
    
    /**
     * Get order item ID
     * 
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get order ID
     * 
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
    
    /**
     * Set order ID
     * 
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }
    
    /**
     * Get product ID
     * 
     * @return int|null
     */
    public function getProductId()
    {
        return $this->productId;
    }
    
    /**
     * Set product ID
     * 
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }
    
    /**
     * Get quantity
     * 
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }
    
    /**
     * Set quantity
     * 
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }
    
    /**
     * Get price
     * 
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }
    
    /**
     * Set price
     * 
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
    
    /**
     * Get product
     * 
     * @return ProductModel|null
     */
    public function getProduct()
    {
        if (!$this->product && $this->productId) {
            $this->loadProduct();
        }
        
        return $this->product;
    }
    
    /**
     * Get subtotal
     * 
     * @return float
     */
    public function getSubtotal()
    {
        return $this->price * $this->quantity;
    }
}
