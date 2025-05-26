<?php

/**
 * System Settings Migration
 */
class M0020SystemSettings
{
    /**
     * Create system settings tables
     */
    public function up()
    {
        $db = Database::getInstance();
        
        // Create system_settings table
        $sql = "CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            is_public BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_setting_key (setting_key),
            INDEX idx_is_public (is_public)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create email_templates table
        $sql = "CREATE TABLE IF NOT EXISTS email_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            template_key VARCHAR(100) NOT NULL UNIQUE,
            name VARCHAR(255) NOT NULL,
            subject VARCHAR(500) NOT NULL,
            body_html TEXT NOT NULL,
            body_text TEXT,
            variables TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_template_key (template_key),
            INDEX idx_is_active (is_active)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Create activity_logs table
        $sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100) NOT NULL,
            entity_type VARCHAR(50),
            entity_id INT,
            description TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB";
        
        $db->query($sql)->execute();
        
        // Insert sample data
        $this->insertSampleSettings($db);
        $this->insertEmailTemplates($db);
    }
    
    /**
     * Drop system settings tables
     */
    public function down()
    {
        $db = Database::getInstance();
        $db->query("DROP TABLE IF EXISTS activity_logs")->execute();
        $db->query("DROP TABLE IF EXISTS email_templates")->execute();
        $db->query("DROP TABLE IF EXISTS system_settings")->execute();
    }
    
    /**
     * Insert sample system settings
     */
    private function insertSampleSettings($db)
    {
        $settings = [
            [
                'setting_key' => 'site_name',
                'setting_value' => 'Cửa hàng mỹ phẩm Việt Nam',
                'setting_type' => 'string',
                'description' => 'Tên website',
                'is_public' => true
            ],
            [
                'setting_key' => 'site_description',
                'setting_value' => 'Cửa hàng mỹ phẩm chính hãng với giá tốt nhất',
                'setting_type' => 'string',
                'description' => 'Mô tả website',
                'is_public' => true
            ],
            [
                'setting_key' => 'contact_email',
                'setting_value' => 'contact@myshop.vn',
                'setting_type' => 'string',
                'description' => 'Email liên hệ',
                'is_public' => true
            ],
            [
                'setting_key' => 'contact_phone',
                'setting_value' => '1900-1234',
                'setting_type' => 'string',
                'description' => 'Số điện thoại liên hệ',
                'is_public' => true
            ],
            [
                'setting_key' => 'default_currency',
                'setting_value' => 'VND',
                'setting_type' => 'string',
                'description' => 'Đơn vị tiền tệ mặc định',
                'is_public' => true
            ],
            [
                'setting_key' => 'tax_rate',
                'setting_value' => '10.0',
                'setting_type' => 'decimal',
                'description' => 'Thuế VAT (%)',
                'is_public' => false
            ],
            [
                'setting_key' => 'free_shipping_threshold',
                'setting_value' => '500000',
                'setting_type' => 'decimal',
                'description' => 'Ngưỡng miễn phí vận chuyển (VND)',
                'is_public' => true
            ],
            [
                'setting_key' => 'max_cart_items',
                'setting_value' => '50',
                'setting_type' => 'integer',
                'description' => 'Số lượng sản phẩm tối đa trong giỏ hàng',
                'is_public' => false
            ],
            [
                'setting_key' => 'enable_reviews',
                'setting_value' => '1',
                'setting_type' => 'boolean',
                'description' => 'Cho phép đánh giá sản phẩm',
                'is_public' => true
            ],
            [
                'setting_key' => 'enable_wishlist',
                'setting_value' => '1',
                'setting_type' => 'boolean',
                'description' => 'Cho phép danh sách yêu thích',
                'is_public' => true
            ],
            [
                'setting_key' => 'order_number_prefix',
                'setting_value' => 'VN',
                'setting_type' => 'string',
                'description' => 'Tiền tố mã đơn hàng',
                'is_public' => false
            ],
            [
                'setting_key' => 'business_hours',
                'setting_value' => '{"monday": "8:00-18:00", "tuesday": "8:00-18:00", "wednesday": "8:00-18:00", "thursday": "8:00-18:00", "friday": "8:00-18:00", "saturday": "8:00-17:00", "sunday": "9:00-16:00"}',
                'setting_type' => 'json',
                'description' => 'Giờ làm việc',
                'is_public' => true
            ]
        ];
        
        $sql = "INSERT INTO system_settings (setting_key, setting_value, setting_type, description, is_public) 
                VALUES (:setting_key, :setting_value, :setting_type, :description, :is_public)";
        
        foreach ($settings as $setting) {
            $db->query($sql)->bind([
                'setting_key' => $setting['setting_key'],
                'setting_value' => $setting['setting_value'],
                'setting_type' => $setting['setting_type'],
                'description' => $setting['description'],
                'is_public' => $setting['is_public']
            ])->execute();
        }
    }
    
    /**
     * Insert email templates
     */
    private function insertEmailTemplates($db)
    {
        $templates = [
            [
                'template_key' => 'order_confirmation',
                'name' => 'Xác nhận đơn hàng',
                'subject' => 'Xác nhận đơn hàng #{order_number}',
                'body_html' => '<h2>Cảm ơn bạn đã đặt hàng!</h2><p>Đơn hàng #{order_number} của bạn đã được xác nhận.</p><p>Tổng tiền: {total_amount} VND</p><p>Chúng tôi sẽ liên hệ với bạn sớm nhất.</p>',
                'body_text' => 'Cảm ơn bạn đã đặt hàng! Đơn hàng #{order_number} của bạn đã được xác nhận. Tổng tiền: {total_amount} VND. Chúng tôi sẽ liên hệ với bạn sớm nhất.',
                'variables' => 'order_number, customer_name, total_amount, order_items'
            ],
            [
                'template_key' => 'order_shipped',
                'name' => 'Đơn hàng đã được giao',
                'subject' => 'Đơn hàng #{order_number} đã được giao cho đơn vị vận chuyển',
                'body_html' => '<h2>Đơn hàng của bạn đang trên đường giao!</h2><p>Đơn hàng #{order_number} đã được giao cho {carrier}.</p><p>Mã vận đơn: {tracking_number}</p><p>Dự kiến giao hàng: {estimated_delivery}</p>',
                'body_text' => 'Đơn hàng #{order_number} đã được giao cho {carrier}. Mã vận đơn: {tracking_number}. Dự kiến giao hàng: {estimated_delivery}',
                'variables' => 'order_number, customer_name, carrier, tracking_number, estimated_delivery'
            ],
            [
                'template_key' => 'welcome_customer',
                'name' => 'Chào mừng khách hàng mới',
                'subject' => 'Chào mừng bạn đến với {site_name}!',
                'body_html' => '<h2>Chào mừng {customer_name}!</h2><p>Cảm ơn bạn đã đăng ký tài khoản tại {site_name}.</p><p>Sử dụng mã giảm giá WELCOME10 để được giảm 10% cho đơn hàng đầu tiên.</p>',
                'body_text' => 'Chào mừng {customer_name}! Cảm ơn bạn đã đăng ký tài khoản tại {site_name}. Sử dụng mã giảm giá WELCOME10 để được giảm 10% cho đơn hàng đầu tiên.',
                'variables' => 'customer_name, site_name'
            ]
        ];
        
        $sql = "INSERT INTO email_templates (template_key, name, subject, body_html, body_text, variables) 
                VALUES (:template_key, :name, :subject, :body_html, :body_text, :variables)";
        
        foreach ($templates as $template) {
            $db->query($sql)->bind([
                'template_key' => $template['template_key'],
                'name' => $template['name'],
                'subject' => $template['subject'],
                'body_html' => $template['body_html'],
                'body_text' => $template['body_text'],
                'variables' => $template['variables']
            ])->execute();
        }
    }
}
