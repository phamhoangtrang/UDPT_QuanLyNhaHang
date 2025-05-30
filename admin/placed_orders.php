<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_POST['update_payment'])) {
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $order_db = $db->getConnection('order');
   $update_status = $order_db->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_status->execute([$payment_status, $order_id]);
   $message[] = 'Payment status updated!';
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $order_db = $db->getConnection('order');
   $delete_order = $order_db->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

   <?php include '../components/admin_header.php'; ?>

   <!-- Placed orders section starts -->
   <section class="placed-orders">
      <h1 class="heading">Đơn hàng</h1>

      <?php if ($db->isServiceAvailable('order')): ?>
         <div class="box-container">
            <?php
            try {
               $select_orders = $db->getConnection('order')->prepare("SELECT * FROM `orders` ORDER BY placed_on DESC");
               $select_orders->execute();
               if ($select_orders->rowCount() > 0) {
                  while ($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                     ?>
                     <div class="box">
                        <p> User ID: <span><?= $fetch_orders['user_id']; ?></span> </p>
                        <p> Placed On: <span><?= $fetch_orders['placed_on']; ?></span> </p>
                        <p> Name: <span><?= $fetch_orders['name']; ?></span> </p>
                        <p> Payment Method: <span><?= $fetch_orders['method']; ?></span> </p>
                        <p> Total Products: <span><?= $fetch_orders['total_products']; ?></span> </p>
                        <p> Total Price: <span>$<?= $fetch_orders['total_price']; ?>/-</span> </p>
                        <p> Payment Status: <span
                              style="color: <?= ($fetch_orders['payment_status'] == 'pending') ? 'red' : 'green'; ?>"><?= htmlspecialchars($fetch_orders['payment_status']); ?></span>
                        </p>

                        <?php if ($fetch_orders['dining_option'] == 'dine_in'): ?>
                           <?php
                           $order_db = $db->getConnection('order');
                           $select_table = $order_db->prepare("
                              SELECT t.table_number 
                              FROM `reservations` r 
                              JOIN `tables` t ON r.table_id = t.id 
                              WHERE r.order_id = ?
                           ");
                           $select_table->execute([$fetch_orders['id']]);
                           $fetch_table = $select_table->fetchAll(PDO::FETCH_ASSOC);
                           if (count($fetch_table) > 0): // Kiểm tra xem có bàn nào không
                              ?>
                              <p> Table Number: <span><?= implode(', ', array_column($fetch_table, 'table_number')); ?></span> </p>
                           <?php else: ?>
                              <p> Table Number: <span>Not assigned</span> </p>
                           <?php endif; ?>
                        <?php endif; ?>

                        <form action="" method="POST">
                           <input type="hidden" name="order_id" value="<?= $fetch_orders['id']; ?>">
                           <select name="payment_status" class="drop-down">
                              <option value="" selected disabled><?= $fetch_orders['payment_status']; ?></option>
                              <option value="pending">Pending</option>
                              <option value="completed">Completed</option>
                           </select>
                           <div class="flex-btn">
                              <input type="submit" value="Update" class="btn" name="update_payment">
                              <a href="placed_orders.php?delete=<?= $fetch_orders['id']; ?>" class="delete-btn"
                                 onclick="return confirm('Delete this order?');">Delete</a>
                           </div>
                        </form>
                     </div>
                     <?php
                  }
               } else {
                  echo '<p class="empty">Chưa có đơn hàng nào!</p>';
               }
            } catch (PDOException $e) {
               error_log("Order service error: " . $e->getMessage());
               echo '<p class="empty">Không thể truy cập đơn hàng</p>';
            }
            ?>
         </div>
      <?php else: ?>
         <div class="notice">
            <p>Dịch vụ đơn hàng tạm thời không khả dụng</p>
            <p>Vui lòng thử lại sau</p>
         </div>
      <?php endif; ?>
   </section>
   <!-- Placed orders section ends -->

   <!-- Custom JS file link -->
   <script src="../js/admin_script.js"></script>

</body>

</html>