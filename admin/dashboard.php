<?php
ob_start(); // Thêm buffer output
include '../components/connect.php'; // Connect to the database

session_start();
// Set admin_id mặc định
$_SESSION['admin_id'] = 1; // Giả định ID admin mặc định là 1
$admin_id = $_SESSION['admin_id'];

// Initialize default values
$number_of_products = 0;
$select_products = null;
$number_of_orders = 0;
$total_pendings = 0;
$total_completed = 0;

// Check product service
if ($db->isServiceAvailable('product')) {
   try {
      $select_products = $db->getConnection('product')->prepare("SELECT * FROM `products`");
      $select_products->execute();
      $number_of_products = $select_products->rowCount();
   } catch (PDOException $e) {
      error_log("Product service error: " . $e->getMessage());
   }
}

// Check order service
if ($db->isServiceAvailable('order')) {
   try {
      $select_orders = $db->getConnection('order')->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      $number_of_orders = $select_orders->rowCount();

      $total_pendings = $db->getConnection('order')->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
      $total_pendings->execute(['pending']);
      $total_pendings = $total_pendings->rowCount();

      $total_completed = $db->getConnection('order')->prepare("SELECT * FROM `orders` WHERE payment_status = ?");
      $total_completed->execute(['completed']);
      $total_completed = $total_completed->rowCount();
   } catch (PDOException $e) {
      error_log("Order service error: " . $e->getMessage());
   }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Dashboard</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

   <?php include '../components/admin_header.php'; ?> <!-- Header for admin -->

   <!-- Admin dashboard section starts -->

   <section class="dashboard">

      <h1 class="heading">Dashboard</h1>

      <div class="box-container">

         <div class="box">
            <h3>Welcome!</h3>
            <p><?= $fetch_profile['name']; ?></p>
            <a href="update_profile.php" class="btn">Update Profile</a>
         </div>

         <div class="box">
            <h3><span>$</span><?= number_format($total_pendings, 2); ?><span>/-</span></h3>
            <p>Total Pendings</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
         </div>

         <div class="box">
            <h3><span>$</span><?= number_format($total_completed, 2); ?><span>/-</span></h3>
            <p>Total Completes</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
         </div>

         <div class="box">
            <h3><?= $number_of_orders; ?></h3>
            <p>Total Orders</p>
            <a href="placed_orders.php" class="btn">See Orders</a>
         </div>

         <div class="box">
            <?php if ($select_products): ?>
               <h3><?= $number_of_products; ?></h3>
               <p>Products Added</p>
               <a href="products.php" class="btn">See Products</a>
            <?php else: ?>
               <h3>0</h3>
               <p>Products Added</p>
               <a href="products.php" class="btn">See Products</a>
            <?php endif; ?>
         </div>

         <div class="box">
            <?php
            $select_users = $conn->prepare("SELECT * FROM `users`");
            $select_users->execute();
            $numbers_of_users = $select_users->rowCount();
            ?>
            <h3><?= $numbers_of_users; ?></h3>
            <p>User Accounts</p>
            <a href="users_accounts.php" class="btn">See Users</a>
         </div>

         <div class="box">
            <?php
            $select_admins = $conn->prepare("SELECT * FROM `admin`");
            $select_admins->execute();
            $numbers_of_admins = $select_admins->rowCount();
            ?>
            <h3><?= $numbers_of_admins; ?></h3>
            <p>Admins</p>
            <a href="admin_accounts.php" class="btn">See Admin </a>
         </div>

         <div class="box">
            <?php
            // Kiểm tra service trước khi truy vấn
            if ($db->isServiceAvailable('content')) {
               try {
                  $select_messages = $db->getConnection('content')->prepare("SELECT * FROM `messages`");
                  $select_messages->execute();
                  $number_of_messages = $select_messages->rowCount();
               } catch (PDOException $e) {
                  $number_of_messages = 0;
                  error_log("Dashboard Messages Error: " . $e->getMessage());
               }
            }
            ?>
            <h3><?= $number_of_messages; ?></h3>
            <p>New Messages</p>
            <a href="messages.php" class="btn">See Messages</a>
         </div>

         <!-- Add table management section -->
         <div class="box">
            <?php
            $select_tables = $db->getConnection('order')->prepare("SELECT * FROM `tables`");
            $select_tables->execute();
            $numbers_of_tables = $select_tables->rowCount();
            ?>
            <h3><?= $numbers_of_tables; ?></h3>
            <p>Table Management</p>
            <a href="table.php" class="btn">View Tables</a>
         </div>

         <!-- Add reservation management section -->
         <div class="box">
            <?php
            $select_reservations = $db->getConnection('order')->prepare("SELECT * FROM `reservations`");
            $select_reservations->execute();
            $numbers_of_reservations = $select_reservations->rowCount();
            ?>
            <h3><?= $numbers_of_reservations; ?></h3>
            <p>Reservation Management</p>
            <a href="manage_reservations.php" class="btn">View Reservations</a>
         </div>

      </div>

   </section>

   <!-- Admin dashboard section ends -->

   <!-- Custom JS file link -->
   <script src="../js/admin_script.js"></script>

</body>

</html>

<?php
ob_end_flush(); // Kết thúc buffer
?>