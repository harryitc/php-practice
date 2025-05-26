<?php

require_once 'app/core/Database.php';
require_once 'app/models/OrderNotificationModel.php';
require_once 'app/models/UserModel.php';

class NotificationService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Send tracking update notification
     */
    public function sendTrackingUpdate($tracking)
    {
        try {
            // Get order and customer information
            require_once 'app/models/OrderModel.php';
            $order = OrderModel::findById($tracking->getOrderId());
            
            if (!$order) {
                return false;
            }
            
            $userModel = new UserModel();
            $customer = $userModel->findById($order->getUserId());
            
            if (!$customer) {
                return false;
            }
            
            // Prepare notification data
            $eventType = 'tracking_update';
            $subject = $this->getTrackingSubject($tracking, $order);
            $message = $this->getTrackingMessage($tracking, $order);
            
            // Send email notification
            $emailSent = $this->sendEmailNotification(
                $order->getId(),
                $customer->getId(),
                $customer->getEmail(),
                $subject,
                $message,
                $eventType
            );
            
            // Add customer note if it's an important update
            if ($this->isImportantUpdate($tracking)) {
                $this->addCustomerNote($order, $tracking);
            }
            
            return $emailSent;
            
        } catch (Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send order status change notification
     */
    public function sendOrderStatusUpdate($order, $oldStatus, $newStatus)
    {
        try {
            $userModel = new UserModel();
            $customer = $userModel->findById($order->getUserId());
            
            if (!$customer) {
                return false;
            }
            
            $eventType = 'status_update';
            $subject = "Order Status Update - " . $order->getOrderNumber();
            $message = $this->getStatusUpdateMessage($order, $oldStatus, $newStatus);
            
            return $this->sendEmailNotification(
                $order->getId(),
                $customer->getId(),
                $customer->getEmail(),
                $subject,
                $message,
                $eventType
            );
            
        } catch (Exception $e) {
            error_log("Status notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email notification
     */
    private function sendEmailNotification($orderId, $userId, $email, $subject, $message, $eventType)
    {
        // Create notification record
        $notification = new OrderNotificationModel(
            null,
            $orderId,
            $userId,
            'email',
            $eventType,
            $email,
            $subject,
            $message
        );
        
        // Save notification record
        if (!$notification->save()) {
            return false;
        }
        
        // Send actual email (simplified version)
        $emailSent = $this->sendEmail($email, $subject, $message);
        
        // Update notification status
        if ($emailSent) {
            $notification->setStatus('sent');
            $notification->setSentAt(date('Y-m-d H:i:s'));
        } else {
            $notification->setStatus('failed');
            $notification->setErrorMessage('Failed to send email');
        }
        
        $notification->save();
        
        return $emailSent;
    }
    
    /**
     * Simple email sending (can be replaced with proper email service)
     */
    private function sendEmail($to, $subject, $message)
    {
        // Basic email headers
        $headers = [
            'From: noreply@yourstore.com',
            'Reply-To: support@yourstore.com',
            'Content-Type: text/html; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // HTML email template
        $htmlMessage = $this->getEmailTemplate($subject, $message);
        
        // Send email using PHP mail function (replace with proper email service in production)
        return mail($to, $subject, $htmlMessage, implode("\r\n", $headers));
    }
    
    /**
     * Get tracking update subject
     */
    private function getTrackingSubject($tracking, $order)
    {
        $statusName = $tracking->getStatusDisplayName();
        return "Tracking Update: Your order {$order->getOrderNumber()} is {$statusName}";
    }
    
    /**
     * Get tracking update message
     */
    private function getTrackingMessage($tracking, $order)
    {
        $statusName = $tracking->getStatusDisplayName();
        $orderNumber = $order->getOrderNumber();
        $trackingNumber = $tracking->getTrackingNumber();
        $carrier = $tracking->getCarrier();
        $location = $tracking->getLocation();
        $description = $tracking->getDescription();
        $trackingDate = date('M d, Y g:i A', strtotime($tracking->getTrackingDate()));
        
        $message = "<h3>Tracking Update for Order {$orderNumber}</h3>";
        $message .= "<p><strong>Status:</strong> {$statusName}</p>";
        $message .= "<p><strong>Date:</strong> {$trackingDate}</p>";
        
        if ($trackingNumber) {
            $message .= "<p><strong>Tracking Number:</strong> {$trackingNumber}</p>";
        }
        
        if ($carrier) {
            $message .= "<p><strong>Carrier:</strong> {$carrier}</p>";
        }
        
        if ($location) {
            $message .= "<p><strong>Location:</strong> {$location}</p>";
        }
        
        if ($description) {
            $message .= "<p><strong>Details:</strong> {$description}</p>";
        }
        
        if ($tracking->isDeliveryStatus()) {
            $message .= "<p style='color: green;'><strong>🎉 Great news! Your order is on its way to you!</strong></p>";
        }
        
        $message .= "<p><a href='/Order/tracking/{$order->getId()}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Track Your Order</a></p>";
        
        return $message;
    }
    
    /**
     * Get status update message
     */
    private function getStatusUpdateMessage($order, $oldStatus, $newStatus)
    {
        $orderNumber = $order->getOrderNumber();
        $newStatusName = $order->getStatusDisplayName();
        
        $message = "<h3>Order Status Update</h3>";
        $message .= "<p>Your order <strong>{$orderNumber}</strong> status has been updated to: <strong>{$newStatusName}</strong></p>";
        
        // Add specific messages based on status
        switch ($newStatus) {
            case 'confirmed':
                $message .= "<p>We've confirmed your order and it's being prepared for shipment.</p>";
                break;
            case 'processing':
                $message .= "<p>Your order is currently being processed and will be shipped soon.</p>";
                break;
            case 'shipped':
                $message .= "<p>Great news! Your order has been shipped and is on its way to you.</p>";
                break;
            case 'delivered':
                $message .= "<p>🎉 Your order has been delivered! We hope you enjoy your purchase.</p>";
                break;
            case 'cancelled':
                $message .= "<p>Your order has been cancelled. If you have any questions, please contact our support team.</p>";
                break;
        }
        
        $message .= "<p><a href='/Order/view/{$order->getId()}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Order Details</a></p>";
        
        return $message;
    }
    
    /**
     * Check if tracking update is important enough for customer note
     */
    private function isImportantUpdate($tracking)
    {
        return in_array($tracking->getStatus(), [
            'picked_up',
            'out_for_delivery', 
            'delivered',
            'exception',
            'delayed'
        ]);
    }
    
    /**
     * Add customer note for important tracking updates
     */
    private function addCustomerNote($order, $tracking)
    {
        $statusName = $tracking->getStatusDisplayName();
        $title = "Tracking Update: {$statusName}";
        $content = "Your package status has been updated to: {$statusName}";
        
        if ($tracking->getLocation()) {
            $content .= "\nLocation: " . $tracking->getLocation();
        }
        
        if ($tracking->getDescription()) {
            $content .= "\nDetails: " . $tracking->getDescription();
        }
        
        $content .= "\nUpdated: " . date('M d, Y g:i A', strtotime($tracking->getTrackingDate()));
        
        require_once 'app/models/OrderNotesModel.php';
        return OrderNotesModel::addNote(
            $order->getId(),
            $content,
            null, // System generated
            'system',
            $title,
            true, // Visible to customer
            'normal'
        );
    }
    
    /**
     * Get email template
     */
    private function getEmailTemplate($subject, $content)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$subject}</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Your Store</h2>
                </div>
                <div class='content'>
                    {$content}
                </div>
                <div class='footer'>
                    <p>Thank you for shopping with us!</p>
                    <p>If you have any questions, contact us at support@yourstore.com</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
