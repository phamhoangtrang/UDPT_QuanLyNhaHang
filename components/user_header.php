<?php
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

if (!isset($connections)) {
   require_once __DIR__ . '/connect.php';
}

// Initialize variables
$user_id = '';
$cartCount = 0;
$isGuestMode = !$db->isServiceAvailable('user');

// Handle user session
if (!$isGuestMode && isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
}

// Handle cart only if needed
if ($user_id && $db->isServiceAvailable('product')) {
   try {
      $select_cart = $db->getConnection('product')->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart->execute([$user_id]);
      $cartCount = $select_cart->rowCount();
   } catch (PDOException $e) {
      error_log("Cart error: " . $e->getMessage());
   }
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
         <a href="search.php"><i class="fas fa-search"></i></a>
         <?php if ($user_id): ?>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i>
               <?php if ($db->isServiceAvailable('product')): ?>
                  <span>(<?= $cartCount ?>)</span>
               <?php endif; ?>
            </a>
         <?php endif; ?>
         <div id="user-btn" class="fas fa-user"></div>
      </div>

      <div class="profile">
         <?php if ($isGuestMode): ?>
            <p class="name">Đang xem ở chế độ khách</p>
            <a href="login.php" class="btn">Đăng nhập</a>
         <?php else: ?>
            <?php if ($user_id): ?>
               <?php
               try {
                  $select_profile = $db->getConnection('user')->prepare("SELECT * FROM `users` WHERE id = ?");
                  $select_profile->execute([$user_id]);
                  if ($fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC)):
                     ?>
                     <p class="name"><?= htmlspecialchars($fetch_profile['name']); ?></p>
                     <div class="flex-btn">
                        <a href="profile.php" class="btn">Tài khoản</a>
                        <a href="components/user_logout.php" class="delete-btn">Đăng xuất</a>
                     </div>
                  <?php endif; ?>
               <?php } catch (PDOException $e) {
                  error_log("Profile error: " . $e->getMessage());
               } ?>
            <?php else: ?>
               <p class="name">Vui lòng đăng nhập!</p>
               <a href="login.php" class="btn">Đăng nhập</a>
            <?php endif; ?>
         <?php endif; ?>
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

   .notice {
      padding: 1rem;
      background-color: #ffdddd;
      color: #d8000c;
      border: 1px solid #d8000c;
      border-radius: .5rem;
      margin: 1rem 0;
      text-align: center;
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