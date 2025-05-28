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
                // Thử kết nối từng service riêng biệt
                $services = [
                    'user' => ['host' => 'user-db', 'required' => false],
                    'product' => ['host' => 'product-db', 'required' => false],
                    'order' => ['host' => 'order-db', 'required' => false],
                    'content' => ['host' => 'content-db', 'required' => false],
                    'reservation' => ['host' => 'reservation-db', 'required' => false]
                ];

                $hasConnection = false;
                foreach ($services as $service => $config) {
                    try {
                        $this->connections[$service] = new PDO(
                            "mysql:host={$config['host']};dbname={$service}_service;charset=utf8mb4",
                            'root',
                            'root',
                            $options
                        );
                        $this->connections[$service]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        if ($service === 'user') {
                            self::$conn = $this->connections[$service];
                        }
                        $hasConnection = true;
                    } catch (PDOException $e) {
                        error_log("Service $service không khả dụng: " . $e->getMessage());
                        $this->connections[$service] = null;
                    }
                }

                if ($hasConnection) {
                    return;
                }

                sleep(5);
                $retryCount++;
            } catch (Exception $e) {
                sleep(5);
                $retryCount++;
            }
        }
    }

    private function verifyTables()
    {
        // Verify order tables
        try {
            if ($this->isServiceAvailable('order')) {
                $orderDb = $this->getConnection('order');
                $orderTables = ['orders', 'tables', 'reservations'];
                foreach ($orderTables as $table) {
                    $stmt = $orderDb->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() == 0) {
                        $method = 'create' . ucfirst($table);
                        $this->$method($orderDb);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Không thể kiểm tra bảng của Order service: " . $e->getMessage());
        }

        // Verify product tables
        if ($this->isServiceAvailable('product')) {
            try {
                $productDb = $this->getConnection('product');
                $tables = ['products', 'cart'];
                foreach ($tables as $table) {
                    $stmt = $productDb->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() == 0) {
                        $method = 'create' . ucfirst($table);
                        $this->$method($productDb);
                    }
                }
            } catch (PDOException $e) {
                error_log("Product tables verification failed: " . $e->getMessage());
            }
        }

        // Verify content tables
        try {
            if ($this->isServiceAvailable('content')) {
                $contentDb = $this->getConnection('content');
                $stmt = $contentDb->query("SHOW TABLES LIKE 'messages'");
                if ($stmt->rowCount() == 0) {
                    $this->createContentTables($contentDb);
                }
            }
        } catch (PDOException $e) {
            error_log("Không thể kiểm tra bảng của Content service: " . $e->getMessage());
        }

        // Verify reservation tables
        try {
            if ($this->isServiceAvailable('reservation')) {
                $reservationDb = $this->getConnection('reservation');
                $tables = ['tables', 'reservations'];
                foreach ($tables as $table) {
                    $stmt = $reservationDb->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() == 0) {
                        $method = 'create' . ucfirst($table);
                        $this->$method($reservationDb);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Reservation service error: " . $e->getMessage());
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

    private function createCart($productDb)
    {
        $productDb->exec("CREATE TABLE IF NOT EXISTS `cart` (
            `id` int(100) NOT NULL AUTO_INCREMENT,
            `user_id` int(100) NOT NULL,
            `pid` int(100) NOT NULL,
            `name` varchar(100) NOT NULL,
            `price` int(10) NOT NULL,
            `quantity` int(10) NOT NULL,
            `image` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function getConnection($service)
    {
        return $this->connections[$service] ?? null;
    }

    public function isServiceAvailable($service)
    {
        return isset($this->connections[$service]) && $this->connections[$service] !== null;
    }
}

$db = new DatabaseConnections();
$conn = $db->getConnection('user'); // Default connection for backward compatibility
?>