<?php
require_once 'components/connect.php';

function kiemTraService($db, $serviceName)
{
    if ($db->isServiceAvailable($serviceName)) {
        echo "<div style='color:green'>✓ Service $serviceName đang hoạt động</div>";
    } else {
        echo "<div style='color:orange'>⚠ Service $serviceName không khả dụng</div>";
    }
}

echo "<h2>Trạng thái các Service:</h2>";

// Kiểm tra từng service
kiemTraService($db, 'user');
kiemTraService($db, 'product');
kiemTraService($db, 'order');
kiemTraService($db, 'content');

?>