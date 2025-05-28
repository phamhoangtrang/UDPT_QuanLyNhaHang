<?php
ob_start();
include '../components/connect.php';

session_start();

// Set default admin ID if not logged in
$_SESSION['admin_id'] = $_SESSION['admin_id'] ?? 1;
$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}
;

if (isset($_POST['add_product'])) {
    if (!$db->isServiceAvailable('product')) {
        $message[] = 'Cannot add product - Service temporarily unavailable';
    } else {
        try {
            $name = $_POST['name'];
            $name = filter_var($name, FILTER_SANITIZE_SPECIAL_CHARS);
            $category = $_POST['category'];
            $category = filter_var($category, FILTER_SANITIZE_SPECIAL_CHARS);
            $price = $_POST['price'];
            $price = filter_var($price, FILTER_SANITIZE_SPECIAL_CHARS);

            $image = $_FILES['image']['name'];
            $image = filter_var($image, FILTER_SANITIZE_SPECIAL_CHARS);
            $image_size = $_FILES['image']['size'];
            $image_tmp_name = $_FILES['image']['tmp_name'];
            $image_folder = '../uploaded_img/' . $image;

            $select_products = $db->getConnection('product')->prepare("SELECT * FROM `products` WHERE name = ?");
            $select_products->execute([$name]);

            if ($select_products->rowCount() > 0) {
                $message[] = 'Product name already exists!';
            } else {
                if ($image_size > 2000000) {
                    $message[] = 'Image size is too large';
                } else {
                    $insert_product = $db->getConnection('product')->prepare("INSERT INTO `products`(name, category, price, image) VALUES(?, ?, ?, ?)");
                    $insert_product->execute([$name, $category, $price, $image]);
                    move_uploaded_file($image_tmp_name, $image_folder);
                    $message[] = 'New product added!';
                }
            }
        } catch (PDOException $e) {
            error_log("Product service error: " . $e->getMessage());
            $message[] = 'Cannot add product - System error';
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_product_image = $db->getConnection('product')->prepare("SELECT * FROM `products` WHERE id = ?");
    $delete_product_image->execute([$delete_id]);
    $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
    unlink('../uploaded_img/' . $fetch_delete_image['image']);

    $delete_product = $db->getConnection('product')->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_product->execute([$delete_id]);

    // Cart operations should use order service
    $delete_cart = $db->getConnection('order')->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);
    header('location:products.php');

}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>products</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="../css/admin_style.css">

</head>

<body>

    <?php include '../components/admin_header.php' ?>

    <!-- add products section starts  -->

    <section class="add-products">

        <form action="" method="POST" enctype="multipart/form-data">
            <h3>add product</h3>
            <input type="text" required placeholder="enter product name" name="name" maxlength="100" class="box">
            <input type="number" min="0" max="9999999999" required placeholder="enter product price" name="price"
                onkeypress="if(this.value.length == 10) return false;" class="box">
            <select name="category" class="box" required>
                <option value="" disabled selected>select category --</option>
                <option value="sushi">sushi</option>
                <option value="bento">bento</option>
                <option value="sake">sake</option>
                <option value="cake">cake</option>
            </select>
            <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png, image/webp" required>
            <input type="submit" value="add product" name="add_product" class="btn">
        </form>

    </section>

    <!-- add products section ends -->

    <!-- show products section starts  -->

    <section class="show-products">

        <div class="box-container">

            <?php
            if ($db->isServiceAvailable('product')) {
                try {
                    $select_products = $db->getConnection('product')->prepare("SELECT * FROM `products`");
                    $select_products->execute();
                    if ($select_products->rowCount() > 0) {
                        while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <div class="box">
                                <img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="">
                                <div class="flex">
                                    <div class="price"><span>$</span><?= $fetch_products['price']; ?><span>/-</span></div>
                                    <div class="category"><?= $fetch_products['category']; ?></div>
                                </div>
                                <div class="name"><?= $fetch_products['name']; ?></div>
                                <div class="flex-btn">
                                    <a href="update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
                                    <a href="products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn"
                                        onclick="return confirm('delete this product?');">delete</a>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="empty">no products added yet!</p>';
                    }
                } catch (PDOException $e) {
                    error_log("Product service error: " . $e->getMessage());
                    echo '<p class="empty">Cannot access products at the moment</p>';
                }
            } else {
                echo '<p class="empty">Product service is currently unavailable</p>';
            }
            ?>

        </div>

    </section>

    <!-- show products section ends -->

    <!-- reservations section starts  -->

    <section class="reservations">
        <div class="box-container">
            <?php
            if ($db->isServiceAvailable('reservation')) {
                try {
                    $select_reservations = $db->getConnection('reservation')->prepare("SELECT * FROM `reservations`");
                    $select_reservations->execute();
                    if ($select_reservations->rowCount() > 0) {
                        while ($fetch_reservations = $select_reservations->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <div class="box">
                                <div class="res-info">
                                    <div class="date"><?= $fetch_reservations['date']; ?></div>
                                    <div class="time"><?= $fetch_reservations['time']; ?></div>
                                </div>
                                <div class="name"><?= $fetch_reservations['name']; ?></div>
                                <div class="contact"><?= $fetch_reservations['contact']; ?></div>
                                <div class="flex-btn">
                                    <a href="update_reservation.php?update=<?= $fetch_reservations['id']; ?>"
                                        class="option-btn">update</a>
                                    <a href="products.php?delete=<?= $fetch_reservations['id']; ?>" class="delete-btn"
                                        onclick="return confirm('delete this reservation?');">delete</a>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<p class="empty">Chưa có đặt bàn nào!</p>';
                    }
                } catch (PDOException $e) {
                    error_log("Reservation service error: " . $e->getMessage());
                    echo '<p class="empty">Không thể truy cập danh sách đặt bàn</p>';
                }
            } else {
                echo '<p class="empty">Dịch vụ đặt bàn tạm thời không khả dụng</p>';
            }
            ?>
        </div>
    </section>

    <!-- reservations section ends -->
    <!-- custom js file link  -->
    <script src="../js/admin_script.js"></script>

</body>

</html>