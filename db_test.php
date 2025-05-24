<?php
require_once 'components/connect.php';

try {
    // Test user service
    $userDb = $db->getConnection('user');
    $stmt = $userDb->query("SELECT * FROM users LIMIT 1");
    echo "User service connection OK!<br>";

    // Test product service
    $productDb = $db->getConnection('product');
    $stmt = $productDb->query("SELECT * FROM products LIMIT 1");
    echo "Product service connection OK!<br>";

    // Test order service
    $orderDb = $db->getConnection('order');
    $stmt = $orderDb->query("SELECT * FROM orders LIMIT 1");
    echo "Order service connection OK!<br>";

    // Test content service
    $contentDb = $db->getConnection('content');
    $stmt = $contentDb->query("SELECT * FROM messages LIMIT 1");
    echo "Content service connection OK!<br>";

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>