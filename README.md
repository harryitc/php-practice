# E-commerce Website with MySQL Database

This is a simple e-commerce website with a MySQL database and migration system.

## Setup Instructions

1. Make sure you have PHP and MySQL installed on your system.
2. Configure your database connection in `app/config/database.php`.
3. Run the migrations to set up the database:

```bash
php migrate.php
```

4. Start the application using Laragon or any other PHP server.

## Database Structure

The database consists of the following tables:

### Users
- `id`: Primary key
- `name`: User's name
- `email`: User's email (unique)
- `password`: Hashed password
- `role`: User role (admin or customer)
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

### Categories
- `id`: Primary key
- `name`: Category name
- `description`: Category description
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

### Products
- `id`: Primary key
- `category_id`: Foreign key to categories table
- `name`: Product name
- `description`: Product description
- `price`: Product price
- `status`: Product status (Scoping, Quoting, Production, Shipped)
- `inventory_count`: Number of items in inventory
- `incoming_count`: Number of items incoming
- `out_of_stock`: Number of items out of stock
- `grade`: Product grade (A, B, C)
- `image`: URL to product image
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

### Orders
- `id`: Primary key
- `user_id`: Foreign key to users table
- `total_amount`: Total order amount
- `status`: Order status (pending, processing, shipped, delivered, cancelled)
- `shipping_address`: Shipping address
- `shipping_city`: Shipping city
- `shipping_state`: Shipping state
- `shipping_zip`: Shipping ZIP code
- `shipping_country`: Shipping country
- `payment_method`: Payment method
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

### Order Items
- `id`: Primary key
- `order_id`: Foreign key to orders table
- `product_id`: Foreign key to products table
- `quantity`: Quantity of product
- `price`: Price of product at time of order
- `created_at`: Timestamp of creation
- `updated_at`: Timestamp of last update

## Migration System

The migration system allows you to create and apply database migrations. Migrations are stored in the `app/migrations` directory and are applied in order based on their filenames.

To create a new migration, create a new file in the `app/migrations` directory with a name like `m0006_your_migration.php`. The file should contain a class with the same name (in CamelCase) and implement `up()` and `down()` methods.

Example:

```php
<?php

class M0006YourMigration
{
    public function up()
    {
        $db = Database::getInstance();

        $sql = "CREATE TABLE IF NOT EXISTS your_table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB";

        $db->query($sql)->execute();
    }

    public function down()
    {
        $db = Database::getInstance();
        $sql = "DROP TABLE IF EXISTS your_table";
        $db->query($sql)->execute();
    }
}
```

To apply migrations, run:

```bash
php migrate.php
```

## Default Admin User

The migration system creates a default admin user with the following credentials:

- Email: admin@example.com
- Password: admin123

You can use these credentials to log in to the admin panel (once implemented).