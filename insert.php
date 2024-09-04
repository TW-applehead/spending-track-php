<?php
$config = include_once('config.php');
require 'modules/functions.php';

if (!checkAuth($config, $_REQUEST['finger_print'], $_REQUEST['auth'])) {
    die($config['NOT_ALLOWED_TEXT']);
}

$amount = $_POST['amount'];
$account_id = $_POST['account_id'];
$is_expense = $_POST['is_expense'];
$other_account = $_POST['other_account'];
$expense_time = $_POST['expense_time'];
$notes = $_POST['description'];

// 檢查參數
if (preg_match('/^\d+$/', $amount) && preg_match('/^\d+$/', $account_id) && preg_match('/^\d+$/', $is_expense) &&
    preg_match('/^\d+$/', $other_account) && preg_match('/^\d{6}$/', $expense_time) && preg_match('/^[^<>]*$/', $notes)) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    // 防止SQL注入的資料過濾
    $amount = $conn->real_escape_string($amount);
    $account_id = $conn->real_escape_string($account_id);
    $is_expense = $conn->real_escape_string($is_expense);
    $is_cost = $conn->real_escape_string($other_account);
    $expense_time = $conn->real_escape_string($expense_time);
    $notes = $conn->real_escape_string($notes);

    // 構建 SQL 插入語句
    $sql = "INSERT INTO expenses (amount, account_id, is_expense, other_account, expense_time, notes) 
            VALUES ('$amount', '$account_id', '$is_expense', '$other_account', '$expense_time', '$notes')";

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        $result = insertLog($conn, $_SERVER['REQUEST_URI'], $sql);
        if ($result === TRUE) {
            echo "新增記錄成功";
        } else {
            echo $result;
        }
    } else {
        echo "錯誤: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

exit();
?>