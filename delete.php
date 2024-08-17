<?php
// 資料庫連接設置
$servername = "sql110.infinityfree.com";
$username = "if0_37119399";
$password = "9RvhVROq9LsgFp";
$dbname = "if0_37119399_spending_track";

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 檢查連接
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    // 防止SQL注入的資料過濾
    $id = $conn->real_escape_string($id);

    // 構建 SQL 插入語句
    $sql = "DELETE FROM expenses WHERE id = " . $id;

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        echo "記錄已成功刪除";
    } else {
        echo "刪除失敗! " . $conn->error;
    }
}

$conn->close();
exit();
?>