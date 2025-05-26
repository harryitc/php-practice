<?php

require_once 'app/models/CartModel.php';
require_once 'app/models/ProductModel.php';

class CartController
{
    private $cart;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->cart = new CartModel();
    }

    /**
     * Display cart page
     */
    public function index()
    {
        $cartItems = $this->cart->getCartItemsWithDetails();
        $totalAmount = $this->cart->getTotalAmount();
        $itemCount = $this->cart->getItemCount();

        include 'app/views/cart/index.php';
    }

    /**
     * Add product to cart (AJAX endpoint)
     */
    public function add()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $productId = $_POST['product_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (!$productId || $quantity <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
            return;
        }

        // Check if user is logged in (optional, depending on your requirements)
        // if (!isset($_SESSION['user_id'])) {
        //     echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
        //     return;
        // }

        $success = $this->cart->addToCart($productId, $quantity);

        if ($success) {
            $itemCount = $this->cart->getItemCount();
            $totalAmount = $this->cart->getTotalAmount();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Product added to cart successfully',
                'cart_count' => $itemCount,
                'cart_total' => number_format($totalAmount, 2)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add product to cart. Check inventory.']);
        }
    }

    /**
     * Update quantity in cart (AJAX endpoint)
     */
    public function update()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $productId = $_POST['product_id'] ?? null;
        $quantity = (int)($_POST['quantity'] ?? 0);

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }

        $success = $this->cart->updateQuantity($productId, $quantity);

        if ($success) {
            $itemCount = $this->cart->getItemCount();
            $totalAmount = $this->cart->getTotalAmount();
            
            // Calculate new subtotal for this item
            $cartItems = $this->cart->getCartItemsWithDetails();
            $subtotal = 0;
            foreach ($cartItems as $item) {
                if ($item['product_id'] == $productId) {
                    $subtotal = $item['subtotal'];
                    break;
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Cart updated successfully',
                'cart_count' => $itemCount,
                'cart_total' => number_format($totalAmount, 2),
                'item_subtotal' => number_format($subtotal, 2)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart. Check inventory.']);
        }
    }

    /**
     * Remove product from cart (AJAX endpoint)
     */
    public function remove()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $productId = $_POST['product_id'] ?? null;

        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
            return;
        }

        $success = $this->cart->removeFromCart($productId);

        if ($success) {
            $itemCount = $this->cart->getItemCount();
            $totalAmount = $this->cart->getTotalAmount();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Product removed from cart',
                'cart_count' => $itemCount,
                'cart_total' => number_format($totalAmount, 2)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove product from cart']);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        $this->cart->clearCart();
        $_SESSION['success_message'] = 'Cart cleared successfully';
        header('Location: /Cart');
        exit();
    }

    /**
     * Get cart count (AJAX endpoint)
     */
    public function count()
    {
        header('Content-Type: application/json');
        echo json_encode(['count' => $this->cart->getItemCount()]);
    }

    /**
     * Get cart summary (AJAX endpoint)
     */
    public function summary()
    {
        header('Content-Type: application/json');
        
        $cartItems = $this->cart->getCartItemsWithDetails();
        $totalAmount = $this->cart->getTotalAmount();
        $itemCount = $this->cart->getItemCount();

        $items = [];
        foreach ($cartItems as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'name' => $item['product']->getName(),
                'price' => number_format($item['price'], 2),
                'quantity' => $item['quantity'],
                'subtotal' => number_format($item['subtotal'], 2),
                'image' => $item['product']->getImage()
            ];
        }

        echo json_encode([
            'items' => $items,
            'total_amount' => number_format($totalAmount, 2),
            'item_count' => $itemCount
        ]);
    }
}
