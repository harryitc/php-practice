<?php

require_once 'app/core/Database.php';

class OrderTrackingModel
{
    private $id;
    private $orderId;
    private $trackingNumber;
    private $carrier;
    private $status;
    private $location;
    private $description;
    private $trackingDate;
    private $estimatedDelivery;
    private $isDelivered;
    private $proofOfDelivery;
    private $recipientName;
    private $signatureRequired;
    private $signatureObtained;
    private $createdAt;
    private $updatedAt;

    private $db;

    public function __construct($id = null, $orderId = null, $trackingNumber = null, $carrier = null, $status = null, $location = null, $description = null, $trackingDate = null)
    {
        $this->db = Database::getInstance();

        $this->id = $id;
        $this->orderId = $orderId;
        $this->trackingNumber = $trackingNumber;
        $this->carrier = $carrier;
        $this->status = $status;
        $this->location = $location;
        $this->description = $description;
        $this->trackingDate = $trackingDate;
        $this->isDelivered = false;
        $this->signatureRequired = false;
        $this->signatureObtained = false;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getOrderId() { return $this->orderId; }
    public function getTrackingNumber() { return $this->trackingNumber; }
    public function getCarrier() { return $this->carrier; }
    public function getStatus() { return $this->status; }
    public function getLocation() { return $this->location; }
    public function getDescription() { return $this->description; }
    public function getTrackingDate() { return $this->trackingDate; }
    public function getEstimatedDelivery() { return $this->estimatedDelivery; }
    public function getIsDelivered() { return $this->isDelivered; }
    public function getProofOfDelivery() { return $this->proofOfDelivery; }
    public function getRecipientName() { return $this->recipientName; }
    public function getSignatureRequired() { return $this->signatureRequired; }
    public function getSignatureObtained() { return $this->signatureObtained; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }

    // Setters
    public function setOrderId($orderId) { $this->orderId = $orderId; }
    public function setTrackingNumber($trackingNumber) { $this->trackingNumber = $trackingNumber; }
    public function setCarrier($carrier) { $this->carrier = $carrier; }
    public function setStatus($status) { $this->status = $status; }
    public function setLocation($location) { $this->location = $location; }
    public function setDescription($description) { $this->description = $description; }
    public function setTrackingDate($trackingDate) { $this->trackingDate = $trackingDate; }
    public function setEstimatedDelivery($estimatedDelivery) { $this->estimatedDelivery = $estimatedDelivery; }
    public function setIsDelivered($isDelivered) { $this->isDelivered = $isDelivered; }
    public function setProofOfDelivery($proofOfDelivery) { $this->proofOfDelivery = $proofOfDelivery; }
    public function setRecipientName($recipientName) { $this->recipientName = $recipientName; }
    public function setSignatureRequired($signatureRequired) { $this->signatureRequired = $signatureRequired; }
    public function setSignatureObtained($signatureObtained) { $this->signatureObtained = $signatureObtained; }

    /**
     * Save tracking record
     */
    public function save()
    {
        $result = false;

        if ($this->id) {
            $result = $this->update();
        } else {
            $result = $this->insert();
        }

        // Auto-update order status and send notifications if save was successful
        if ($result) {
            $this->updateOrderStatus();
            $this->sendNotification();
        }

        return $result;
    }

    /**
     * Insert new tracking record
     */
    private function insert()
    {
        $sql = "INSERT INTO order_tracking (order_id, tracking_number, carrier, status, location, description, tracking_date, estimated_delivery, is_delivered, proof_of_delivery, recipient_name, signature_required, signature_obtained)
                VALUES (:order_id, :tracking_number, :carrier, :status, :location, :description, :tracking_date, :estimated_delivery, :is_delivered, :proof_of_delivery, :recipient_name, :signature_required, :signature_obtained)";

        $result = $this->db->query($sql)->bind([
            'order_id' => $this->orderId,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'status' => $this->status,
            'location' => $this->location,
            'description' => $this->description,
            'tracking_date' => $this->trackingDate,
            'estimated_delivery' => $this->estimatedDelivery,
            'is_delivered' => $this->isDelivered ? 1 : 0,
            'proof_of_delivery' => $this->proofOfDelivery,
            'recipient_name' => $this->recipientName,
            'signature_required' => $this->signatureRequired ? 1 : 0,
            'signature_obtained' => $this->signatureObtained ? 1 : 0
        ])->execute();

        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Update existing tracking record
     */
    private function update()
    {
        $sql = "UPDATE order_tracking
                SET order_id = :order_id, tracking_number = :tracking_number, carrier = :carrier,
                    status = :status, location = :location, description = :description,
                    tracking_date = :tracking_date, estimated_delivery = :estimated_delivery,
                    is_delivered = :is_delivered, proof_of_delivery = :proof_of_delivery,
                    recipient_name = :recipient_name, signature_required = :signature_required,
                    signature_obtained = :signature_obtained
                WHERE id = :id";

        return $this->db->query($sql)->bind([
            'id' => $this->id,
            'order_id' => $this->orderId,
            'tracking_number' => $this->trackingNumber,
            'carrier' => $this->carrier,
            'status' => $this->status,
            'location' => $this->location,
            'description' => $this->description,
            'tracking_date' => $this->trackingDate,
            'estimated_delivery' => $this->estimatedDelivery,
            'is_delivered' => $this->isDelivered ? 1 : 0,
            'proof_of_delivery' => $this->proofOfDelivery,
            'recipient_name' => $this->recipientName,
            'signature_required' => $this->signatureRequired ? 1 : 0,
            'signature_obtained' => $this->signatureObtained ? 1 : 0
        ])->execute();
    }

    /**
     * Get tracking history for an order
     */
    public static function getByOrderId($orderId)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY tracking_date DESC";

        $results = $db->query($sql)->fetchAll(['order_id' => $orderId]);

        $tracking = [];
        foreach ($results as $row) {
            $item = new self(
                $row['id'],
                $row['order_id'],
                $row['tracking_number'],
                $row['carrier'],
                $row['status'],
                $row['location'],
                $row['description'],
                $row['tracking_date']
            );
            $item->estimatedDelivery = $row['estimated_delivery'];
            $item->isDelivered = $row['is_delivered'];
            $item->proofOfDelivery = $row['proof_of_delivery'];
            $item->recipientName = $row['recipient_name'];
            $item->signatureRequired = $row['signature_required'];
            $item->signatureObtained = $row['signature_obtained'];
            $item->createdAt = $row['created_at'];
            $item->updatedAt = $row['updated_at'];
            $tracking[] = $item;
        }

        return $tracking;
    }

    /**
     * Get latest tracking status for an order
     */
    public static function getLatestByOrderId($orderId)
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY tracking_date DESC LIMIT 1";

        $result = $db->query($sql)->fetch(['order_id' => $orderId]);

        if ($result) {
            $tracking = new self(
                $result['id'],
                $result['order_id'],
                $result['tracking_number'],
                $result['carrier'],
                $result['status'],
                $result['location'],
                $result['description'],
                $result['tracking_date']
            );
            $tracking->estimatedDelivery = $result['estimated_delivery'];
            $tracking->isDelivered = $result['is_delivered'];
            $tracking->proofOfDelivery = $result['proof_of_delivery'];
            $tracking->recipientName = $result['recipient_name'];
            $tracking->signatureRequired = $result['signature_required'];
            $tracking->signatureObtained = $result['signature_obtained'];
            $tracking->createdAt = $result['created_at'];
            $tracking->updatedAt = $result['updated_at'];
            return $tracking;
        }

        return null;
    }

    /**
     * Get tracking status display name
     */
    public function getStatusDisplayName()
    {
        $statusNames = [
            'picked_up' => 'Picked Up',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'exception' => 'Exception',
            'returned' => 'Returned',
            'delayed' => 'Delayed',
            'customs_clearance' => 'Customs Clearance',
            'sorting_facility' => 'At Sorting Facility',
            'departed_facility' => 'Departed Facility'
        ];

        return $statusNames[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Check if tracking status indicates delivery
     */
    public function isDeliveryStatus()
    {
        return in_array($this->status, ['delivered', 'out_for_delivery']);
    }

    /**
     * Check if tracking status indicates exception
     */
    public function isExceptionStatus()
    {
        return in_array($this->status, ['exception', 'delayed', 'returned']);
    }

    /**
     * Get estimated delivery based on tracking status and carrier
     */
    public function calculateEstimatedDelivery()
    {
        if ($this->estimatedDelivery) {
            return $this->estimatedDelivery;
        }

        // Default estimation based on status and carrier
        $daysToAdd = 0;

        switch ($this->status) {
            case 'picked_up':
                $daysToAdd = $this->getCarrierDeliveryDays();
                break;
            case 'in_transit':
                $daysToAdd = max(1, $this->getCarrierDeliveryDays() - 1);
                break;
            case 'out_for_delivery':
                $daysToAdd = 0; // Same day
                break;
            case 'delivered':
                return $this->trackingDate;
            default:
                $daysToAdd = $this->getCarrierDeliveryDays();
        }

        $estimatedDate = new DateTime($this->trackingDate);
        $estimatedDate->add(new DateInterval("P{$daysToAdd}D"));

        return $estimatedDate->format('Y-m-d H:i:s');
    }

    /**
     * Get typical delivery days for carrier
     */
    private function getCarrierDeliveryDays()
    {
        $carrierDays = [
            'FedEx' => 2,
            'UPS' => 3,
            'DHL' => 2,
            'USPS' => 5,
            'Local Delivery' => 1
        ];

        return $carrierDays[$this->carrier] ?? 3;
    }

    /**
     * Auto-update order status based on tracking status
     */
    public function updateOrderStatus()
    {
        if (!$this->orderId) {
            return false;
        }

        require_once 'app/models/OrderModel.php';
        $order = OrderModel::findById($this->orderId);

        if (!$order) {
            return false;
        }

        $newOrderStatus = null;

        switch ($this->status) {
            case 'picked_up':
                if ($order->getStatus() === 'packed') {
                    $newOrderStatus = 'shipped';
                }
                break;
            case 'in_transit':
                if (in_array($order->getStatus(), ['packed', 'confirmed', 'processing'])) {
                    $newOrderStatus = 'shipped';
                }
                break;
            case 'out_for_delivery':
                if ($order->getStatus() !== 'out_for_delivery') {
                    $newOrderStatus = 'out_for_delivery';
                }
                break;
            case 'delivered':
                if ($order->getStatus() !== 'delivered') {
                    $newOrderStatus = 'delivered';
                    $order->setActualDeliveryDate($this->trackingDate);
                }
                break;
        }

        if ($newOrderStatus && $newOrderStatus !== $order->getStatus()) {
            $order->setStatus($newOrderStatus);
            return $order->save();
        }

        return true;
    }

    /**
     * Send notification for tracking update
     */
    public function sendNotification()
    {
        if (file_exists('app/services/NotificationService.php')) {
            require_once 'app/services/NotificationService.php';
            $notificationService = new NotificationService();
            return $notificationService->sendTrackingUpdate($this);
        }

        return true;
    }
}
