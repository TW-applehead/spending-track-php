<?php // 更新帳戶當月發薪前餘額
$config = include_once('../config.php');
require 'functions.php';

if (!checkUserIP($config)) {
    die($config['NOT_ALLOWED_TEXT']);
}

$account_id = $_POST['account_id'];
$time = $_POST['time'];
$balance = $_POST['balance'];

// 檢查參數
if (preg_match('/^\d+$/', $account_id) && preg_match('/^\d{6}$/', $time) && preg_match('/^\d+$/', $balance)) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    // 防止SQL注入的資料過濾
    $account_id = $conn->real_escape_string($account_id);
    $time = $conn->real_escape_string($time);
    $balance = $conn->real_escape_string($balance);
    $now = getNow();

    // 檢查是否已有該時間的餘額紀錄
    $check_sql = "SELECT * FROM account_balances WHERE account_id = ? AND time = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("is", $account_id, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 如果有紀錄，則進行更新操作
        $update_sql = "UPDATE account_balances SET balance = ?, updated_at = ? WHERE account_id = ? AND time = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dsis", $balance, $now, $account_id, $time);
        if ($update_stmt->execute()) {
            $log_sql = "UPDATE account_balances SET balance = '$balance' WHERE account_id = '$account_id' AND time = '$time'";
            $result = insertLog($conn, $_SERVER['REQUEST_URI'], $log_sql);
            if ($result === TRUE) {
                echo "帳戶餘額已更新為 " . $balance;
            } else {
                echo $result;
            }
        } else {
            echo "更新操作失敗，請再試一次";
        }
        $update_stmt->close();
    } else {
        // 如果沒有紀錄，則進行插入操作
        $insert_sql = "INSERT INTO account_balances (account_id, time, balance, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isd", $account_id, $time, $balance);
        if ($insert_stmt->execute()) {
            $log_sql = "INSERT INTO account_balances (account_id, time, balance) VALUES ('$account_id', '$time', '$balance')";
            $result = insertLog($conn, $_SERVER['REQUEST_URI'], $log_sql);
            if ($result === TRUE) {
                echo "帳戶餘額已新增";
            } else {
                echo $result;
            }
        } else {
            echo "新增操作失敗，請再試一次";
        }
        $insert_stmt->close();
    }
    $conn->close();
}

exit();
?>