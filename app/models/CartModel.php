<?php

require_once 'app/core/Database.php';
require_once 'app/models/ProductModel.php';

class CartModel
{
    private $db;
    private $sessionKey = 'shopping_cart';

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Initialize session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize cart in session if not exists
        if (!isset($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = [];
        }
    }

    /**
     * Add product to cart
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function addToCart($productId, $quantity = 1)
    {
        // Validate product exists and has inventory
        $product = new ProductModel();
        $product = $product->findById($productId);
        
        if (!$product || $product->getInventoryCount() <= 0) {
            return false;
        }

        // Check if product already in cart
        if (isset($_SESSION[$this->sessionKey][$productId])) {
            $newQuantity = $_SESSION[$this->sessionKey][$productId]['quantity'] + $quantity;
            
            // Check if new quantity exceeds inventory
            if ($newQuantity > $product->getInventoryCount()) {
                return false;
            }
            
            $_SESSION[$this->sessionKey][$productId]['quantity'] = $newQuantity;
        } else {
            // Check if quantity exceeds inventory
            if ($quantity > $product->getInventoryCount()) {
                return false;
            }
            
            $_SESSION[$this->sessionKey][$productId] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product->getPrice(),
                'name' => $product->getName(),
                'image' => $product->getImage()
            ];
        }

        return true;
    }

    /**
     * Update quantity of product in cart
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity($productId, $quantity)
    {
        if (!isset($_SESSION[$this->sessionKey][$productId])) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->removeFromCart($productId);
        }

        // Validate inventory
        $product = new ProductModel();
        $product = $product->findById($productId);
        
        if (!$product || $quantity > $product->getInventoryCount()) {
            return false;
        }

        $_SESSION[$this->sessionKey][$productId]['quantity'] = $quantity;
        $_SESSION[$this->sessionKey][$productId]['price'] = $product->getPrice(); // Update price in case it changed

        return true;
    }

    /**
     * Remove product from cart
     *
     * @param int $productId
     * @return bool
     */
    public function removeFromCart($productId)
    {
        if (isset($_SESSION[$this->sessionKey][$productId])) {
            unset($_SESSION[$this->sessionKey][$productId]);
            return true;
        }
        return false;
    }

    /**
     * Get all cart items
     *
     * @return array
     */
    public function getCartItems()
    {
        return $_SESSION[$this->sessionKey] ?? [];
    }

    /**
     * Get cart items with full product details
     *
     * @return array
     */
    public function getCartItemsWithDetails()
    {
        $items = [];
        $cartItems = $this->getCartItems();

        foreach ($cartItems as $productId => $item) {
            $product = new ProductModel();
            $product = $product->findById($productId);
            
            if ($product) {
                $items[] = [
                    'product_id' => $productId,
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'price' => $product->getPrice(), // Get current price
                    'subtotal' => $product->getPrice() * $item['quantity']
                ];
            }
        }

        return $items;
    }

    /**
     * Get total number of items in cart
     *
     * @return int
     */
    public function getItemCount()
    {
        $count = 0;
        foreach ($_SESSION[$this->sessionKey] as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    /**
     * Get total amount of cart
     *
     * @return float
     */
    public function getTotalAmount()
    {
        $total = 0;
        $items = $this->getCartItemsWithDetails();
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($_SESSION[$this->sessionKey]);
    }

    /**
     * Clear all items from cart
     *
     * @return bool
     */
    public function clearCart()
    {
        $_SESSION[$this->sessionKey] = [];
        return true;
    }

    /**
     * Get quantity of specific product in cart
     *
     * @param int $productId
     * @return int
     */
    public function getProductQuantity($productId)
    {
        return $_SESSION[$this->sessionKey][$productId]['quantity'] ?? 0;
    }

    /**
     * Check if product is in cart
     *
     * @param int $productId
     * @return bool
     */
    public function hasProduct($productId)
    {
        return isset($_SESSION[$this->sessionKey][$productId]);
    }
}
