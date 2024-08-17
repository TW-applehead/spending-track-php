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
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $account_id = $_POST['account_id'];
    $is_expense = $_POST['is_expense'];
    $other_account = $_POST['other_account'];
    $expense_time = $_POST['expense_time'];
    $notes = $_POST['notes'];

    // 防止SQL注入的資料過濾
    $id = $conn->real_escape_string($id);
    $amount = $conn->real_escape_string($amount);
    $account_id = $conn->real_escape_string($account_id);
    $is_expense = $conn->real_escape_string($is_expense);
    $is_cost = $conn->real_escape_string($other_account);
    $expense_time = $conn->real_escape_string($expense_time);
    $notes = $conn->real_escape_string($notes);

    // 構建 SQL 插入語句
    $sql = "UPDATE expenses
            SET amount = '$amount', account_id = '$account_id', is_expense = '$is_expense', other_account = '$other_account', expense_time = '$expense_time', notes = '$notes'
            WHERE id = '$id'";

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        echo "記錄修改成功";
    } else {
        echo "錯誤: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
exit();
?>