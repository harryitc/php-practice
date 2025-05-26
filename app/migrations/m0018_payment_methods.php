<?php

/**
 * Payment Methods Migration
 */
class M0018PaymentMethods
{
    /**
     * Create payment_methods table
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create payment_methods table
        $sql = "CREATE TABLE IF NOT EXISTS payment_methods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            method_code VARCHAR(50) NOT NULL UNIQUE,
            provider VARCHAR(50) NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            sort_order INT DEFAULT 0,
            processing_fee_percentage DECIMAL(5, 2) DEFAULT 0.00,
            processing_fee_fixed DECIMAL(10, 2) DEFAULT 0.00,
            minimum_amount DECIMAL(10, 2) DEFAULT 0.00,
            maximum_amount DECIMAL(10, 2),
            supported_currencies TEXT DEFAULT 'VND',
            requires_redirect BOOLEAN DEFAULT FALSE,
            logo_url VARCHAR(500),
            instructions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_method_code (method_code),
            INDEX idx_is_active (is_active),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample payment methods
        $this->insertSamplePaymentMethods($db);
    }
    
    /**
     * Drop payment_methods table
     */
    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS payment_methods";
        $db->query($sql)->execute();
    }
    
    /**
     * Insert sample payment methods
     */
    private function insertSamplePaymentMethods($db)
    {
        $paymentMethods = [
            [
                'name' => 'Thanh toán khi nhận hàng (COD)',
                'description' => 'Thanh toán bằng tiền mặt khi nhận hàng',
                'method_code' => 'COD',
                'provider' => 'Cash',
                'sort_order' => 1,
                'processing_fee_percentage' => 0.00,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 0,
                'maximum_amount' => 5000000,
                'requires_redirect' => false,
                'instructions' => 'Quý khách vui lòng chuẩn bị đủ tiền mặt khi nhận hàng. Shipper sẽ kiểm tra hàng trước khi thanh toán.'
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'description' => 'Chuyển khoản qua tài khoản ngân hàng',
                'method_code' => 'BANK_TRANSFER',
                'provider' => 'Bank',
                'sort_order' => 2,
                'processing_fee_percentage' => 0.00,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 50000,
                'maximum_amount' => null,
                'requires_redirect' => false,
                'instructions' => 'Vui lòng chuyển khoản theo thông tin: Ngân hàng Vietcombank - STK: 1234567890 - Chủ TK: CONG TY ABC. Nội dung: Mã đơn hàng + Số điện thoại.'
            ],
            [
                'name' => 'Ví MoMo',
                'description' => 'Thanh toán qua ví điện tử MoMo',
                'method_code' => 'MOMO',
                'provider' => 'MoMo',
                'sort_order' => 3,
                'processing_fee_percentage' => 0.00,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 10000,
                'maximum_amount' => 50000000,
                'requires_redirect' => true,
                'logo_url' => 'https://developers.momo.vn/v3/assets/images/square-logo.svg',
                'instructions' => 'Bạn sẽ được chuyển đến trang MoMo để hoàn tất thanh toán.'
            ],
            [
                'name' => 'ZaloPay',
                'description' => 'Thanh toán qua ví điện tử ZaloPay',
                'method_code' => 'ZALOPAY',
                'provider' => 'ZaloPay',
                'sort_order' => 4,
                'processing_fee_percentage' => 0.00,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 1000,
                'maximum_amount' => 50000000,
                'requires_redirect' => true,
                'logo_url' => 'https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png',
                'instructions' => 'Bạn sẽ được chuyển đến ứng dụng ZaloPay để hoàn tất thanh toán.'
            ],
            [
                'name' => 'VNPay',
                'description' => 'Thanh toán qua cổng VNPay',
                'method_code' => 'VNPAY',
                'provider' => 'VNPay',
                'sort_order' => 5,
                'processing_fee_percentage' => 1.50,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 5000,
                'maximum_amount' => 500000000,
                'requires_redirect' => true,
                'logo_url' => 'https://vnpay.vn/s1/statics.vnpay.vn/2023/6/0oxhzjmxbksr1686814746087.png',
                'instructions' => 'Hỗ trợ thanh toán qua thẻ ATM, Internet Banking, Visa/MasterCard.'
            ],
            [
                'name' => 'Thẻ tín dụng/ghi nợ',
                'description' => 'Thanh toán bằng thẻ Visa, MasterCard',
                'method_code' => 'CREDIT_CARD',
                'provider' => 'Stripe',
                'sort_order' => 6,
                'processing_fee_percentage' => 2.90,
                'processing_fee_fixed' => 5000.00,
                'minimum_amount' => 10000,
                'maximum_amount' => 100000000,
                'requires_redirect' => false,
                'instructions' => 'Chấp nhận thẻ Visa, MasterCard, JCB. Thông tin thẻ được bảo mật tuyệt đối.'
            ],
            [
                'name' => 'ShopeePay',
                'description' => 'Thanh toán qua ví ShopeePay',
                'method_code' => 'SHOPEEPAY',
                'provider' => 'ShopeePay',
                'sort_order' => 7,
                'processing_fee_percentage' => 0.00,
                'processing_fee_fixed' => 0.00,
                'minimum_amount' => 1000,
                'maximum_amount' => 20000000,
                'requires_redirect' => true,
                'logo_url' => 'https://down-vn.img.susercontent.com/file/7e8e7b8b-1e0e-4b8e-9b0e-1b8e7b8b1e0e',
                'instructions' => 'Thanh toán nhanh chóng và an toàn với ShopeePay.'
            ]
        ];
        
        $sql = "INSERT INTO payment_methods (name, description, method_code, provider, sort_order, processing_fee_percentage, processing_fee_fixed, minimum_amount, maximum_amount, requires_redirect, logo_url, instructions) 
                VALUES (:name, :description, :method_code, :provider, :sort_order, :processing_fee_percentage, :processing_fee_fixed, :minimum_amount, :maximum_amount, :requires_redirect, :logo_url, :instructions)";
        
        foreach ($paymentMethods as $method) {
            $db->query($sql)->bind([
                'name' => $method['name'],
                'description' => $method['description'],
                'method_code' => $method['method_code'],
                'provider' => $method['provider'],
                'sort_order' => $method['sort_order'],
                'processing_fee_percentage' => $method['processing_fee_percentage'],
                'processing_fee_fixed' => $method['processing_fee_fixed'],
                'minimum_amount' => $method['minimum_amount'],
                'maximum_amount' => $method['maximum_amount'],
                'requires_redirect' => $method['requires_redirect'],
                'logo_url' => $method['logo_url'] ?? null,
                'instructions' => $method['instructions']
            ])->execute();
        }
    }
}
