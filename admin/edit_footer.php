<?php
ob_start();
include '../components/connect.php';

session_start();
$_SESSION['admin_id'] = $_SESSION['admin_id'] ?? 1;
$admin_id = $_SESSION['admin_id'];

try {
    $db_content = $db->getConnection('content');

    if (isset($_POST['submit'])) {
        $email1 = $_POST['email1'];
        $email2 = $_POST['email2'];
        $opening_hours = $_POST['opening_hours'];
        $address = $_POST['address'];
        $phone1 = $_POST['phone1'];
        $phone2 = $_POST['phone2'];

        $update_footer = $db_content->prepare("UPDATE `footer` SET email1 = ?, email2 = ?, opening_hours = ?, address = ?, phone1 = ?, phone2 = ? WHERE id = 1");
        if ($update_footer->execute([$email1, $email2, $opening_hours, $address, $phone1, $phone2])) {
            $message[] = 'Footer information updated!';
        }
    }

    $select_footer = $db_content->prepare("SELECT * FROM `footer` WHERE id = 1");
    $select_footer->execute();
    $footer = $select_footer->fetch(PDO::FETCH_ASSOC);

    if (!$footer) {
        throw new Exception("No footer data found");
    }

} catch (Exception $e) {
    error_log("Edit Footer Error: " . $e->getMessage());
    $message[] = 'Database error occurred!';
    $footer = [
        'email1' => '',
        'email2' => '',
        'opening_hours' => '',
        'address' => '',
        'phone1' => '',
        'phone2' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Footer</title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="../css/style.css"> <!-- Thêm CSS chính của bạn -->
</head>

<body>

    <?php include '../components/admin_header.php'; ?>

    <div class="container">
        <h1>Edit Footer</h1>

        <?php if (isset($message)): ?>
            <p class="message"><?= $message; ?></p>
        <?php endif; ?>

        <form action="" method="POST" class="footer-edit-form">
            <div class="form-group">
                <label for="email1">Email 1:</label>
                <input type="email" name="email1" id="email1"
                    value="<?= $footer ? htmlspecialchars($footer['email1']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email2">Email 2:</label>
                <input type="email" name="email2" id="email2"
                    value="<?= $footer ? htmlspecialchars($footer['email2']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="opening_hours">Opening Hours:</label>
                <input type="text" name="opening_hours" id="opening_hours"
                    value="<?= $footer ? htmlspecialchars($footer['opening_hours']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" name="address" id="address"
                    value="<?= $footer ? htmlspecialchars($footer['address']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone1">Phone 1:</label>
                <input type="text" name="phone1" id="phone1"
                    value="<?= $footer ? htmlspecialchars($footer['phone1']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="phone2">Phone 2:</label>
                <input type="text" name="phone2" id="phone2"
                    value="<?= $footer ? htmlspecialchars($footer['phone2']) : ''; ?>" required>
            </div>
            <input type="submit" name="submit" value="Update Footer" class="btn">
        </form>
    </div>

    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #218838;
        }

        .message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }

        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>

</body>

</html>

<?php ob_end_flush(); ?>