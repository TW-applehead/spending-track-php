<?php // 不納入收入的收入 直接把每個月的發薪前餘額全部提高
$config = include_once('../config.php');
require 'functions.php';

if (!checkAuth($config, $_REQUEST['finger_print'], $_REQUEST['auth'])) {
    die($config['NOT_ALLOWED_TEXT']);
}

$amount = $_POST['amount'];
$account_id = $_POST['account_id'];

if (preg_match('/^-?\d+$/', $amount) && preg_match('/^[12]$/', $account_id)) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }
    $time = getMaxBalanceDate($conn, $account_id);

    // 防止SQL注入的資料過濾
    $amount = $conn->real_escape_string($amount);
    $account_id = $conn->real_escape_string($account_id);
    $now = getNow();

    $sql = "UPDATE account_balances SET balance = balance + '$amount', updated_at = '$now'
            WHERE account_id = '$account_id' AND time <= '$time'";
    if ($conn->query($sql) === TRUE) {
        $result = insertLog($conn, $_SERVER['REQUEST_URI'], $sql);
        if ($result === TRUE) {
            echo "帳戶餘額已加 " . $amount;
        } else {
            echo $result;
        }
    } else {
        echo "更新操作失敗: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
} else {
    echo "我不知道你做了什麼，反正你不能亂來";
}

exit();
