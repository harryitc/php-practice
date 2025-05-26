# Enhanced Order Tracking System - User Guide

## Overview

Hệ thống theo dõi đơn hàng đã được hoàn thiện với các tính năng nâng cao để cung cấp trải nghiệm theo dõi toàn diện cho cả khách hàng và admin.

## Tính năng chính

### 1. Tự động cập nhật trạng thái đơn hàng
- Tự động cập nhật trạng thái đơn hàng dựa trên tracking updates
- Mapping thông minh giữa tracking status và order status
- Lưu lịch sử thay đổi trạng thái

### 2. Hệ thống thông báo tự động
- Gửi email thông báo khi có cập nhật tracking
- Thông báo thay đổi trạng thái đơn hàng
- Template email responsive và professional
- Ghi chú tự động cho khách hàng

### 3. Tracking timeline nâng cao
- Hiển thị timeline chi tiết với icons
- Progress bar thông minh
- Thông tin vị trí và mô tả chi tiết
- Hiển thị thời gian cập nhật tiếp theo

### 4. Ước tính thời gian giao hàng
- Tính toán tự động dựa trên carrier và status
- Phát hiện đơn hàng bị delay
- Cập nhật estimated delivery động

### 5. Admin dashboard và thống kê
- Thống kê tracking toàn diện
- Danh sách đơn hàng cần cập nhật
- Bulk operations
- Export dữ liệu tracking

## Cách sử dụng

### Cho khách hàng

#### Xem tracking đơn hàng
```
URL: /Order/tracking/{orderId}
```

Khách hàng có thể:
- Xem progress bar trực quan
- Theo dõi timeline chi tiết
- Nhận thông báo về estimated delivery
- Xem cảnh báo nếu đơn hàng bị delay
- Đọc ghi chú từ admin

### Cho Admin

#### Quản lý tracking
```
URL: /Order/adminTracking/{orderId}
```

Admin có thể:
- Thêm tracking updates
- Cập nhật tracking number và carrier
- Simulate tracking updates để test
- Xem timeline đầy đủ

#### Xem thống kê tracking
```
URL: /Order/trackingStats
```

Thống kê bao gồm:
- Tổng số shipments
- Số lượng delivered
- Tỷ lệ giao hàng thành công
- Thời gian giao hàng trung bình
- Danh sách đơn hàng cần cập nhật

#### Export dữ liệu
```
URL: /Order/exportTracking?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
```

#### Simulate tracking (để test)
```
URL: /Order/simulateTracking/{orderId}
```

## Tracking Statuses

### Các trạng thái tracking được hỗ trợ:
- `picked_up`: Đã lấy hàng
- `in_transit`: Đang vận chuyển
- `sorting_facility`: Tại trung tâm phân loại
- `departed_facility`: Đã rời trung tâm
- `customs_clearance`: Thông quan hải quan
- `out_for_delivery`: Đang giao hàng
- `delivered`: Đã giao hàng
- `exception`: Có vấn đề
- `delayed`: Bị trễ
- `returned`: Trả lại

### Mapping với Order Status:
- `picked_up`, `in_transit` → `shipped`
- `out_for_delivery` → `out_for_delivery`
- `delivered` → `delivered`

## API Endpoints

### Customer Endpoints
- `GET /Order/tracking/{orderId}` - Xem tracking
- `GET /Order/view/{orderId}` - Xem chi tiết đơn hàng

### Admin Endpoints
- `GET /Order/adminTracking/{orderId}` - Quản lý tracking
- `POST /Order/addTracking/{orderId}` - Thêm tracking update
- `GET /Order/simulateTracking/{orderId}` - Simulate update
- `GET /Order/trackingStats` - Xem thống kê
- `GET /Order/exportTracking` - Export dữ liệu

## Database Schema

### order_tracking table
```sql
- id: Primary key
- order_id: Foreign key to orders
- tracking_number: Mã tracking
- carrier: Nhà vận chuyển
- status: Trạng thái tracking
- location: Vị trí hiện tại
- description: Mô tả chi tiết
- tracking_date: Thời gian cập nhật
- estimated_delivery: Ước tính giao hàng
- is_delivered: Đã giao hàng
- recipient_name: Người nhận
- signature_obtained: Đã ký nhận
```

### order_notifications table
```sql
- id: Primary key
- order_id: Foreign key to orders
- user_id: Foreign key to users
- notification_type: Loại thông báo (email, sms, push)
- event_type: Loại sự kiện
- recipient: Người nhận
- subject: Tiêu đề
- message: Nội dung
- status: Trạng thái gửi
- sent_at: Thời gian gửi
```

## Services

### TrackingService
- `createTrackingUpdate()`: Tạo tracking update
- `getOrderTrackingInfo()`: Lấy thông tin tracking đầy đủ
- `simulateCarrierUpdate()`: Simulate update từ carrier
- `getTrackingStatistics()`: Lấy thống kê
- `getOrdersNeedingUpdates()`: Lấy đơn hàng cần cập nhật

### NotificationService
- `sendTrackingUpdate()`: Gửi thông báo tracking
- `sendOrderStatusUpdate()`: Gửi thông báo thay đổi status
- `sendEmailNotification()`: Gửi email

## Testing

Chạy script test:
```bash
php test_tracking.php
```

Script sẽ test:
- TrackingService functionality
- OrderTrackingModel enhancements
- Estimated delivery calculation
- Notification system
- Database schema
- Status progression

## Customization

### Thêm carrier mới
Cập nhật trong `TrackingService::getCarrierDeliveryDays()`:
```php
$carrierDays = [
    'FedEx' => 2,
    'UPS' => 3,
    'DHL' => 2,
    'USPS' => 5,
    'Local Delivery' => 1,
    'New Carrier' => 4  // Thêm carrier mới
];
```

### Thêm tracking status mới
1. Cập nhật `OrderTrackingModel::getStatusDisplayName()`
2. Cập nhật `getTrackingIcon()` functions
3. Cập nhật admin form options

### Customize email templates
Chỉnh sửa trong `NotificationService::getEmailTemplate()`

## Troubleshooting

### Thông báo không được gửi
- Kiểm tra PHP mail configuration
- Kiểm tra order_notifications table
- Xem error logs

### Tracking không tự động cập nhật
- Kiểm tra OrderTrackingModel::save() method
- Kiểm tra TrackingService::createTrackingUpdate()
- Verify database permissions

### Performance issues
- Thêm indexes cho tracking queries
- Optimize notification sending
- Consider queue system cho bulk operations

## Future Enhancements

- Tích hợp API thực từ carriers (FedEx, UPS, DHL)
- Real-time notifications với WebSockets
- Mobile app integration
- SMS notifications
- Advanced analytics và reporting
- Machine learning cho delivery predictions
