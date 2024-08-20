<?php
$config = include_once('config.php');
require 'modules/functions.php';

// 創建連接
$conn = connectDB($config);
if ($conn === false) {
    die("資料庫連接失敗");
}

// 檢查連接
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    // 防止SQL注入的資料過濾
    $id = $conn->real_escape_string($id);

    // 構建 SQL 插入語句
    $sql = "DELETE FROM expenses WHERE id = " . $id;

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        $result = insertLog($conn, "/delete.php", $sql);
        if ($result === TRUE) {
            echo "記錄已成功刪除";
        } else {
            echo $result;
        }
    } else {
        echo "刪除失敗! " . $conn->error;
    }
}

$conn->close();
exit();
?>