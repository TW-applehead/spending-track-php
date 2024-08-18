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
    $amount = $_POST['amount'];
    $account_id = $_POST['account_id'];
    $is_expense = $_POST['is_expense'];
    $other_account = $_POST['other_account'];
    $expense_time = $_POST['expense_time'];
    $description = $_POST['description'];

    // 防止SQL注入的資料過濾
    $amount = $conn->real_escape_string($amount);
    $account_id = $conn->real_escape_string($account_id);
    $is_expense = $conn->real_escape_string($is_expense);
    $is_cost = $conn->real_escape_string($other_account);
    $expense_time = $conn->real_escape_string($expense_time);
    $description = $conn->real_escape_string($description);

    // 構建 SQL 插入語句
    $sql = "INSERT INTO expenses (amount, account_id, is_expense, other_account, expense_time, notes) 
            VALUES ('$amount', '$account_id', '$is_expense', '$other_account', '$expense_time', '$description')";

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        echo "新增記錄成功";
    } else {
        echo "錯誤: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
exit();
?>