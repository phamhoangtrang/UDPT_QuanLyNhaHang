<?php
include 'components/connect.php';

session_start();

// Initialize message array at the very start
$message = [];
$reviews = [];

if (!isset($_SESSION['user_id'])) {
    header('location:home.php');
}

if (isset($_POST['submit_review'])) {
    $user_id = $_SESSION['user_id'];
    $rating = filter_var($_POST['rating'], FILTER_SANITIZE_NUMBER_INT);
    $comment = htmlspecialchars(trim($_POST['comment']));

    try {
        $content_db = $db->getConnection('content');
        $content_db->beginTransaction();

        $insert_review = $content_db->prepare("INSERT INTO `reviews` (user_id, rating, comment) VALUES (?, ?, ?)");
        $insert_review->execute([$user_id, $rating, $comment]);

        $content_db->commit();
        $message[] = 'Review submitted successfully!';
    } catch (PDOException $e) {
        $content_db->rollBack();
        error_log('Review error: ' . $e->getMessage());
        $message[] = 'Error submitting review. Please try again.';
    }
}

// Get reviews with user information
try {
    $content_db = $db->getConnection('content');
    $user_db = $db->getConnection('user');
    
    // Get reviews
    $select_reviews = $content_db->prepare("
        SELECT * FROM reviews 
        ORDER BY created_at DESC");
    $select_reviews->execute();
    $reviews = $select_reviews->fetchAll(PDO::FETCH_ASSOC);

    // Get user data for reviews
    $user_ids = array_column($reviews, 'user_id');
    if (!empty($user_ids)) {
        $placeholders = str_repeat('?,', count($user_ids) - 1) . '?';
        $select_users = $user_db->prepare("
            SELECT id, name FROM users 
            WHERE id IN ($placeholders)");
        $select_users->execute($user_ids);
        $users = $select_users->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Merge user data into reviews
        foreach ($reviews as &$review) {
            $review['user_name'] = $users[$review['user_id']] ?? 'Unknown User';
            $review['random_image_index'] = rand(1, 5);
        }
    }

    // Define review images
    $review_images = [
        1 => 'images/pic-1.png',
        2 => 'images/pic-2.png',
        3 => 'images/pic-3.png',
        4 => 'images/pic-4.png',
        5 => 'images/pic-5.png'
    ];

} catch (PDOException $e) {
    error_log('Review load error: ' . $e->getMessage());
    $message[] = 'Error loading reviews. Please try again later.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write Review</title>
    <link rel="stylesheet" href="https://unpkg.com/swiper@8/swiper-bundle.min.css" />

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">

    <style>

    </style>
</head>

<body>

    <?php include 'components/user_header.php'; ?>

    <div class="container">
        <h1>Write a Review</h1>

        <?php if (!empty($message)): ?>
            <div class="message">
                <?php foreach ((array) $message as $msg): ?>
                    <p><?= htmlspecialchars($msg); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="rating">Rating:</label>
                <select name="rating" id="rating" required>
                    <option value="" disabled selected>Select Rating</option>
                    <option value="1">1 Star</option>
                    <option value="2">2 Stars</option>
                    <option value="3">3 Stars</option>
                    <option value="4">4 Stars</option>
                    <option value="5">5 Stars</option>
                </select>
            </div>
            <div class="form-group">
                <label for="comment">Comment:</label>
                <textarea name="comment" id="comment" rows="5" required></textarea>
            </div>
            <input type="submit" name="submit_review" value="Submit Review" class="btn">
        </form>
    </div>

    <!-- Reviews section starts -->
    <?php
    // Mảng chứa đường dẫn đến các ảnh
    $images = [
        'images/pic-1.png',
        'images/pic-2.png',
        'images/pic-3.png',
        'images/pic-4.png',
        'images/pic-5.png',
    ];
    ?>

    <section class="reviews">
        <h2>User Reviews</h2>
        <div class="swiper reviews-slider">
            <div class="swiper-wrapper">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="swiper-slide">
                            <div class="review-box">
                                <img src="<?= $review_images[$review['random_image_index']]; ?>" alt="Review Image"
                                    style="width: 50%; height: auto; border-radius: 5px; margin-bottom: 10px;">
                                <p><strong><?= htmlspecialchars($review['user_name']); ?></strong></p>
                                <p>Rating: <?= str_repeat('⭐', $review['rating']); ?></p>
                                <p><?= htmlspecialchars($review['comment']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No reviews available.</p>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
    <!-- Reviews section ends -->

    <script src="https://unpkg.com/swiper@8/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".reviews-slider", {
            loop: true,
            grabCursor: true,
            spaceBetween: 20,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            breakpoints: {
                0: {
                    slidesPerView: 1,
                },
                700: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });
    </script>

</body>

</html>