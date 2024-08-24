<?php // 更新帳戶當月發薪前餘額
$config = include_once('../config.php');
require 'functions.php';

// 創建連接
$conn = connectDB($config);
if ($conn === false) {
    die("資料庫連接失敗");
}

// 檢查連接
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_id = $_POST['account_id'];
    $retained_start = $_POST['retained_start'];

    // 防止SQL注入的資料過濾
    $account_id = $conn->real_escape_string($account_id);
    $retained_start = $conn->real_escape_string($retained_start);
    $now = getNow();

    $update_sql = "UPDATE accounts SET retained_start = ?, updated_at = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssi", $retained_start, $now, $account_id);
    if ($update_stmt->execute()) {
        $log_sql = "UPDATE accounts SET retained_start = '$retained_start', updated_at = '$now' WHERE id = '$account_id'";
        $result = insertLog($conn, "/updateRetainedStart.php", $log_sql);
        if ($result === TRUE) {
            echo "帳戶開始累積盈餘日期已更新為 " . $retained_start;
        } else {
            echo $result;
        }
    } else {
        echo "更新操作失敗，請再試一次";
    }
    $update_stmt->close();
}

$conn->close();
exit();
?>