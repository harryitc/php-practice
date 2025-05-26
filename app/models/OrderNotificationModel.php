<?php

require_once 'app/core/Database.php';

class OrderNotificationModel
{
    private $id;
    private $orderId;
    private $userId;
    private $notificationType;
    private $eventType;
    private $recipient;
    private $subject;
    private $message;
    private $status;
    private $sentAt;
    private $deliveredAt;
    private $readAt;
    private $errorMessage;
    private $retryCount;
    private $createdAt;
    private $updatedAt;

    private $db;

    public function __construct($id = null, $orderId = null, $userId = null, $notificationType = 'email', $eventType = null, $recipient = null, $subject = null, $message = null)
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->userId = $userId;
        $this->notificationType = $notificationType;
        $this->eventType = $eventType;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->message = $message;
        $this->status = 'pending';
        $this->retryCount = 0;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getOrderId() { return $this->orderId; }
    public function getUserId() { return $this->userId; }
    public function getNotificationType() { return $this->notificationType; }
    public function getEventType() { return $this->eventType; }
    public function getRecipient() { return $this->recipient; }
    public function getSubject() { return $this->subject; }
    public function getMessage() { return $this->message; }
    public function getStatus() { return $this->status; }
    public function getSentAt() { return $this->sentAt; }
    public function getDeliveredAt() { return $this->deliveredAt; }
    public function getReadAt() { return $this->readAt; }
    public function getErrorMessage() { return $this->errorMessage; }
    public function getRetryCount() { return $this->retryCount; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setNotificationType($notificationType) { $this->notificationType = $notificationType; }
    public function setEventType($eventType) { $this->eventType = $eventType; }
    public function setRecipient($recipient) { $this->recipient = $recipient; }
    public function setSubject($subject) { $this->subject = $subject; }
    public function setMessage($message) { $this->message = $message; }
    public function setStatus($status) { $this->status = $status; }
    public function setSentAt($sentAt) { $this->sentAt = $sentAt; }
    public function setDeliveredAt($deliveredAt) { $this->deliveredAt = $deliveredAt; }
    public function setReadAt($readAt) { $this->readAt = $readAt; }
    public function setErrorMessage($errorMessage) { $this->errorMessage = $errorMessage; }
    public function setRetryCount($retryCount) { $this->retryCount = $retryCount; }

    /**
     * Save notification
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
     * Insert new notification
     */
    private function insert()
    {
        $sql = "INSERT INTO order_notifications (order_id, user_id, notification_type, event_type, recipient, subject, message, status, retry_count)
                VALUES (:order_id, :user_id, :notification_type, :event_type, :recipient, :subject, :message, :status, :retry_count)";

        $result = $this->db->query($sql)->bind([
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'notification_type' => $this->notificationType,
            'event_type' => $this->eventType,
            'recipient' => $this->recipient,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
            'retry_count' => $this->retryCount
        ])->execute();

        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing notification
     */
    private function update()
    {
        $sql = "UPDATE order_notifications
                SET order_id = :order_id, user_id = :user_id, notification_type = :notification_type,
                    event_type = :event_type, recipient = :recipient, subject = :subject,
                    message = :message, status = :status, sent_at = :sent_at,
                    delivered_at = :delivered_at, read_at = :read_at, error_message = :error_message,
                    retry_count = :retry_count
                WHERE id = :id";

        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'order_id' => $this->orderId,
            'user_id' => $this->userId,
            'notification_type' => $this->notificationType,
            'event_type' => $this->eventType,
            'recipient' => $this->recipient,
            'subject' => $this->subject,
            'message' => $this->message,
            'status' => $this->status,
            'sent_at' => $this->sentAt,
            'delivered_at' => $this->deliveredAt,
            'read_at' => $this->readAt,
            'error_message' => $this->errorMessage,
            'retry_count' => $this->retryCount
        ])->execute();
    }

    /**
     * Delete notification
     */
    public function delete()
    {
        if (!$this->id) {
            return false;
        }

        $sql = "DELETE FROM order_notifications WHERE id = :id";
        return $this->db->query($sql)->bind(['id' => $this->id])->execute();
    }

    /**
     * Find notification by ID
     */
    public static function findById($id)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM order_notifications WHERE id = :id";
        
        $result = $db->query($sql)->fetch(['id' => $id]);
        
        if (!$result) {
            return null;
        }
        
        $notification = new self(
            $result['id'],
            $result['order_id'],
            $result['user_id'],
            $result['notification_type'],
            $result['event_type'],
            $result['recipient'],
            $result['subject'],
            $result['message']
        );
        
        $notification->status = $result['status'];
        $notification->sentAt = $result['sent_at'];
        $notification->deliveredAt = $result['delivered_at'];
        $notification->readAt = $result['read_at'];
        $notification->errorMessage = $result['error_message'];
        $notification->retryCount = $result['retry_count'];
        $notification->createdAt = $result['created_at'];
        $notification->updatedAt = $result['updated_at'];
        
        return $notification;
    }

    /**
     * Get notifications for an order
     */
    public static function getByOrderId($orderId)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM order_notifications WHERE order_id = :order_id ORDER BY created_at DESC";
        
        $results = $db->query($sql)->fetchAll(['order_id' => $orderId]);
        
        $notifications = [];
        foreach ($results as $row) {
            $notification = new self(
                $row['id'],
                $row['order_id'],
                $row['user_id'],
                $row['notification_type'],
                $row['event_type'],
                $row['recipient'],
                $row['subject'],
                $row['message']
            );
            
            $notification->status = $row['status'];
            $notification->sentAt = $row['sent_at'];
            $notification->deliveredAt = $row['delivered_at'];
            $notification->readAt = $row['read_at'];
            $notification->errorMessage = $row['error_message'];
            $notification->retryCount = $row['retry_count'];
            $notification->createdAt = $row['created_at'];
            $notification->updatedAt = $row['updated_at'];
            
            $notifications[] = $notification;
        }
        
        return $notifications;
    }

    /**
     * Get failed notifications for retry
     */
    public static function getFailedNotifications($maxRetries = 3)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM order_notifications 
                WHERE status = 'failed' AND retry_count < :max_retries 
                ORDER BY created_at ASC";
        
        $results = $db->query($sql)->fetchAll(['max_retries' => $maxRetries]);
        
        $notifications = [];
        foreach ($results as $row) {
            $notification = new self(
                $row['id'],
                $row['order_id'],
                $row['user_id'],
                $row['notification_type'],
                $row['event_type'],
                $row['recipient'],
                $row['subject'],
                $row['message']
            );
            
            $notification->status = $row['status'];
            $notification->sentAt = $row['sent_at'];
            $notification->deliveredAt = $row['delivered_at'];
            $notification->readAt = $row['read_at'];
            $notification->errorMessage = $row['error_message'];
            $notification->retryCount = $row['retry_count'];
            $notification->createdAt = $row['created_at'];
            $notification->updatedAt = $row['updated_at'];
            
            $notifications[] = $notification;
        }
        
        return $notifications;
    }

    /**
     * Increment retry count
     */
    public function incrementRetryCount()
    {
        $this->retryCount++;
        return $this->save();
    }

    /**
     * Mark as delivered
     */
    public function markAsDelivered()
    {
        $this->status = 'delivered';
        $this->deliveredAt = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * Mark as read
     */
    public function markAsRead()
    {
        $this->readAt = date('Y-m-d H:i:s');
        return $this->save();
    }
}
