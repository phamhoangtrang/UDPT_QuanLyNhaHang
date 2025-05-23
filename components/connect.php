<?php
class DatabaseConnections
{
    private $connections = [];
    public static $conn; // For backward compatibility
    private $maxRetries = 15; // Increased retries

    public function __construct()
    {
        $this->initializeConnections();
        $this->verifyTables();
    }

    private function initializeConnections()
    {
        $retryCount = 0;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 60, // Increased timeout
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_PERSISTENT => true
        ];

        while ($retryCount < $this->maxRetries) {
            try {
                // Test DNS resolution first
                if (!gethostbyname('content-db')) {
                    sleep(5);
                    $retryCount++;
                    continue;
                }

                // User service (users, admin)
                $this->connections['user'] = new PDO(
                    'mysql:host=user-db;dbname=user_service;charset=utf8mb4',
                    'root',
                    'root',
                    $options
                );
                self::$conn = $this->connections['user'];

                // Product service (products)
                $this->connections['product'] = new PDO(
                    'mysql:host=product-db;dbname=product_service;charset=utf8mb4',
                    'root',
                    'root',
                    $options
                );

                // Order service (orders, reservations, tables)
                $this->connections['order'] = new PDO(
                    'mysql:host=order-db;dbname=order_service;charset=utf8mb4',
                    'root',
                    'root',
                    $options
                );

                // Content service (messages, footer)
                $this->connections['content'] = new PDO(
                    'mysql:host=content-db;dbname=content_service;charset=utf8mb4',
                    'root',
                    'root',
                    $options
                );

                // Set error mode cho tất cả connections
                foreach ($this->connections as $conn) {
                    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                return;
            } catch (PDOException $e) {
                sleep(5);
                $retryCount++;
                if ($retryCount == $this->maxRetries) {
                    die('Failed to connect after ' . $this->maxRetries . ' attempts: ' . $e->getMessage());
                }
            }
        }
    }

    private function verifyTables()
    {
        try {
            // Verify order tables
            $orderDb = $this->getConnection('order');
            $orderTables = ['orders', 'tables', 'reservations'];
            foreach ($orderTables as $table) {
                $stmt = $orderDb->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() == 0) {
                    $method = 'create' . ucfirst($table);
                    $this->$method($orderDb);
                }
            }

            // Verify product tables
            $productDb = $this->getConnection('product');
            $stmt = $productDb->query("SHOW TABLES LIKE 'products'");
            if ($stmt->rowCount() == 0) {
                $this->createProducts($productDb);
            }

            // Kiểm tra bảng messages
            $contentDb = $this->getConnection('content');
            $stmt = $contentDb->query("SHOW TABLES LIKE 'messages'");
            if ($stmt->rowCount() == 0) {
                $this->createContentTables($contentDb);
            }
        } catch (PDOException $e) {
            die('Table verification failed: ' . $e->getMessage());
        }
    }

    private function createOrders($orderDb)
    {
        $orderDb->exec("CREATE TABLE IF NOT EXISTS `orders` (
            `id` int(100) NOT NULL AUTO_INCREMENT,
            `user_id` int(100) NOT NULL,
            `name` varchar(20) NOT NULL,
            `number` varchar(10) NOT NULL,
            `email` varchar(50) NOT NULL,
            `method` varchar(50) NOT NULL,
            `total_products` varchar(1000) NOT NULL,
            `total_price` int(100) NOT NULL,
            `placed_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `payment_status` varchar(20) NOT NULL DEFAULT 'pending',
            `dining_option` enum('dine_in','delivery') NOT NULL DEFAULT 'delivery',
            `address` varchar(50) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function createTables($orderDb)
    {
        $orderDb->exec("CREATE TABLE IF NOT EXISTS `tables` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `table_number` int(11) NOT NULL,
            `capacity` int(11) NOT NULL,
            `status` enum('available','reserved') DEFAULT 'available',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function createReservations($orderDb)
    {
        $orderDb->exec("CREATE TABLE IF NOT EXISTS `reservations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `table_id` int(11) NOT NULL,
            `name` varchar(100) NOT NULL,
            `phone` varchar(15) NOT NULL,
            `reservation_time` datetime NOT NULL,
            `user_id` int(11) NOT NULL,
            `order_id` int(100) NOT NULL,
            PRIMARY KEY (`id`),
            FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function createContentTables($contentDb)
    {
        $contentDb->exec("CREATE TABLE IF NOT EXISTS `messages` (
            `id` int(100) NOT NULL AUTO_INCREMENT,
            `user_id` int(100) NOT NULL,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `number` varchar(12) NOT NULL,
            `message` varchar(500) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    private function createProducts($productDb)
    {
        $productDb->exec("CREATE TABLE IF NOT EXISTS `products` (
            `id` int(100) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `category` varchar(100) NOT NULL,
            `price` int(10) NOT NULL,
            `image` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function getConnection($service)
    {
        return $this->connections[$service] ?? null;
    }
}

$db = new DatabaseConnections();
$conn = $db->getConnection('user'); // Default connection for backward compatibility
?>