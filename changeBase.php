<?php // 不納入收入的收入 直接把每個月的發薪前餘額全部提高
$config = include_once('config.php');
require 'modules/functions.php';

// 創建連接
$conn = connectDB($config);
if ($conn === false) {
    die("資料庫連接失敗");
}

if ($_GET['amount']) {
    $amount = $_GET['amount'];
} else {
    echo "參數錯誤";
    exit();
}
$account_id = $_GET['account_id'] ?? 2;
$time = $_GET['time'] ?? date("Ym");

// 防止SQL注入的資料過濾
$amount = $conn->real_escape_string($amount);
$account_id = $conn->real_escape_string($account_id);
$time = $conn->real_escape_string($time);
$now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
$now = $now->format('Y-m-d H:i:s');

$sql = "UPDATE account_balances SET balance = balance + '$amount', updated_at = '$now'
        WHERE account_id = '$account_id' AND time <= '$time'";
if ($conn->query($sql) === TRUE) {
    $result = insertLog($conn, "/changeBase.php", $sql);
    if ($result === TRUE) {
        echo "帳戶餘額已加 " . $amount;
    } else {
        echo $result;
    }
} else {
    echo "更新操作失敗: " . $sql . "<br>" . $conn->error;
}

$conn->close();
exit();
