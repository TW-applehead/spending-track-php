<?php
$config = include_once('config.php');
require 'modules/functions.php';

if (!checkUserIP($config)) {
    die($config['NOT_ALLOWED_TEXT']);
}

$id = $_POST['id'];

// 檢查參數
if (preg_match('/^\d+$/', $id)) {
    // 創建DB連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    // 防止SQL注入的資料過濾
    $id = $conn->real_escape_string($id);

    $sql = "DELETE FROM expenses WHERE id = " . $id;
    if ($conn->query($sql) === TRUE) {
        $result = insertLog($conn, $_SERVER['REQUEST_URI'], $sql);
        if ($result === TRUE) {
            echo "記錄已成功刪除";
        } else {
            echo $result;
        }
    } else {
        echo "刪除失敗! " . $conn->error;
    }
    $conn->close();
}

exit();
?>