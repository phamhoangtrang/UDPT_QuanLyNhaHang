<?php

include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:admin_login.php');
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_users = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_users->execute([$delete_id]);
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_order->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users accounts</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

   <?php include '../components/admin_header.php' ?>

   <!-- user accounts section starts  -->

   <section class="accounts">

      <h1 class="heading">users account</h1>

      <div class="box-container">

         <?php
         if ($db->isServiceAvailable('user')) {
            try {
               $select_users = $db->getConnection('user')->prepare("SELECT * FROM `users`");
               $select_users->execute();
               if ($select_users->rowCount() > 0) {
                  while ($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)) {
                     ?>
                     <div class="box">
                        <p> user id : <span><?= $fetch_users['id']; ?></span> </p>
                        <p> username : <span><?= $fetch_users['name']; ?></span> </p>
                        <a href="users_accounts.php?delete=<?= $fetch_users['id']; ?>" class="delete-btn"
                           onclick="return confirm('delete this account?');">delete</a>
                     </div>
                     <?php
                  }
               } else {
                  echo '<p class="empty">Chưa có tài khoản người dùng nào!</p>';
               }
            } catch (PDOException $e) {
               error_log("User service error: " . $e->getMessage());
               echo '<p class="empty">Không thể truy cập danh sách người dùng</p>';
            }
         } else {
            echo '<p class="empty">Dịch vụ quản lý người dùng tạm thời không khả dụng</p>';
         }
         ?>

      </div>

   </section>

   <!-- user accounts section ends -->
   <!-- custom js file link  -->
   <script src="../js/admin_script.js"></script>

</body>

</html>