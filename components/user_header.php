<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

if (!isset($connections)) {
   require_once __DIR__ . '/connect.php';
}

// Đảm bảo rằng biến $user_id được khởi tạo
if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = ''; // Đặt giá trị mặc định nếu người dùng chưa đăng nhập
}
?>

<?php
if (isset($message)) {
   foreach ($message as $message) {
      echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}
?>

<header class="header">
   <section class="flex">
      <a href="home.php" class="logo">Homie_Restaurant</a>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="about.php">about</a>
         <a href="menu.php">menu</a>
         <a href="orders.php">orders</a>
         <a href="contact.php">contact</a>
         <a href="reserve_table.php">reserve table</a>
         <a href="write_review.php">review</a>
      </nav>

      <div class="icons">
         <?php
         $count_cart_items = $db->getConnection('product')->prepare("SELECT * FROM `cart` WHERE user_id = ?");
         $count_cart_items->execute([$user_id]);
         $total_cart_items = $count_cart_items->rowCount();
         ?>
         <a href="search.php"><i class="fas fa-search"></i></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_items; ?>)</span></a>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php
         $select_profile = $db->getConnection('user')->prepare("SELECT * FROM `users` WHERE id = ?");
         $select_profile->execute([$user_id]);
         if ($select_profile->rowCount() > 0) {
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
            $user_name = isset($fetch_profile['name']) ? $fetch_profile['name'] : '';
            ?>
            <p class="name"><?= htmlspecialchars($user_name); ?></p>
            <div class="flex-btn">
               <a href="profile.php" class="btn">profile</a>
               <a href="components/user_logout.php" onclick="return confirm('logout from this website?');" class="delete-btn">logout</a>
            </div>
         <?php } else { ?>
            <p class="name">please login first!</p>
            <a href="login.php" class="btn">login</a>
         <?php } ?>
      </div>

   </section>
</header>

<style>
   .profile {
      position: absolute;
      top: 120%;
      right: 2rem;
      background-color: white;
      border-radius: .5rem;
      box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .1);
      border: .1rem solid black;
      padding: 2rem;
      padding-top: 1.2rem;
      display: none;
      animation: fadeIn .2s linear;
      width: 30rem;
      text-align: center;
   }

   .profile.active {
      display: block;
   }
</style>

<script>
   document.querySelector('#user-btn').onclick = () => {
      let profileElement = document.querySelector('.profile');
      profileElement.classList.toggle('active');
   }

   document.addEventListener('click', function (e) {
      let profileElement = document.querySelector('.profile');
      let userBtn = document.querySelector('#user-btn');
      if (!profileElement.contains(e.target) && !userBtn.contains(e.target)) {
         profileElement.classList.remove('active');
      }
   });
</script>