<?php

if (isset($_POST['add_to_cart'])) {

   if ($user_id == '') {
      header('location:login.php');
   } else {

      $pid = $_POST['pid'];
      $name = $_POST['name'];
      $price = $_POST['price'];
      $image = $_POST['image'];
      $qty = $_POST['qty'];

      $check_cart_numbers = $db->getConnection('product')->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
      $check_cart_numbers->execute([$name, $user_id]);

      if ($check_cart_numbers->rowCount() > 0) {
         $message[] = 'Already added to cart!';
      } else {
         $insert_cart = $db->getConnection('product')->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'Added to cart!';
      }

   }

}

?>