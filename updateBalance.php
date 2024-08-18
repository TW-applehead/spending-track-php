<?php
$config = include_once('config.php');

// 資料庫連接設置
$servername = $config['DB_SERVERNAME'];
$username = $config['DB_USERNAME'];
$password = $config['DB_PASSWORD'];
$dbname = $config['DB_NAME'];

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 檢查連接
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_id = $_POST['account_id'];
    $time = $_POST['time'];
    $balance = $_POST['balance'];

    // 防止SQL注入的資料過濾
    $account_id = $conn->real_escape_string($account_id);
    $time = $conn->real_escape_string($time);
    $balance = $conn->real_escape_string($balance);
    $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
    $now = $now->format('Y-m-d H:i:s');

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
            echo "帳戶餘額已更新";
        } else {
            echo "更新操作失敗，請再試一次";
        }
    } else {
        // 如果沒有紀錄，則進行插入操作
        $insert_sql = "INSERT INTO account_balances (account_id, time, balance, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isd", $account_id, $time, $balance);
        if ($insert_stmt->execute()) {
            echo "帳戶餘額已新增";
        } else {
            echo "新增操作失敗，請再試一次";
        }
    }
}

$conn->close();
exit();
?>