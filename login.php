<?php
ob_start(); // Add output buffering
include 'components/connect.php';

session_start();

if (isset($_POST['submit'])) {
   if (!$db->isServiceAvailable('user')) {
      $message[] = 'Login temporarily unavailable - Service is down';
   } else {
      try {
         $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
         $pass = sha1(htmlspecialchars($_POST['pass'], ENT_QUOTES, 'UTF-8'));

         $select_user = $db->getConnection('user')->prepare("SELECT * FROM `users` WHERE email = ? AND password = ?");
         $select_user->execute([$email, $pass]);

         if ($select_user->rowCount() > 0) {
            $row = $select_user->fetch(PDO::FETCH_ASSOC);
            $_SESSION['user_id'] = $row['id'];
            header('location:home.php');
            exit(); // Add exit after redirect
         } else {
            $message[] = 'incorrect username or password!';
         }
      } catch (PDOException $e) {
         error_log("Login error: " . $e->getMessage());
         $message[] = 'Login service error, please try again later';
      }
   }
}
ob_end_flush(); // End output buffering
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <!-- header section starts  -->
   <?php include 'components/user_header.php'; ?>
   <!-- header section ends -->

   <section class="form-container">

      <form action="" method="post">
         <h3>login now</h3>
         <input type="email" name="email" required placeholder="enter your email" class="box" maxlength="50"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="password" name="pass" required placeholder="enter your password" class="box" maxlength="50"
            oninput="this.value = this.value.replace(/\s/g, '')">
         <input type="submit" value="login now" name="submit" class="btn">
         <p>don't have an account? <a href="register.php">register now</a></p>
      </form>

   </section>
   <?php include 'components/footer.php'; ?>
   <!-- custom js file link  -->
   <script src="js/script.js"></script>

</body>

</html>