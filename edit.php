<?php
$config = include_once('config.php');
require 'modules/functions.php';

if (!checkUserIP($config)) {
    die($config['NOT_ALLOWED_TEXT']);
}

$id = $_POST['id'];
$amount = $_POST['amount'];
$account_id = $_POST['account_id'];
$is_expense = $_POST['is_expense'];
$other_account = $_POST['other_account'];
$expense_time = $_POST['expense_time'];
$notes = $_POST['notes'];

// 檢查連接
if (preg_match('/^\d+$/', $id) && preg_match('/^\d+$/', $amount) && preg_match('/^\d+$/', $account_id) && preg_match('/^\d+$/', $is_expense) &&
    preg_match('/^\d+$/', $other_account) && preg_match('/^\d{6}$/', $expense_time) && preg_match('/^[^<>]*$/', $notes)) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    // 防止SQL注入的資料過濾
    $id = $conn->real_escape_string($id);
    $amount = $conn->real_escape_string($amount);
    $account_id = $conn->real_escape_string($account_id);
    $is_expense = $conn->real_escape_string($is_expense);
    $is_cost = $conn->real_escape_string($other_account);
    $expense_time = $conn->real_escape_string($expense_time);
    $notes = $conn->real_escape_string($notes);
    $now = getNow();

    // 構建 SQL 插入語句
    $sql = "UPDATE expenses SET amount = '$amount', account_id = '$account_id', is_expense = '$is_expense', " .
           "other_account = '$other_account', expense_time = '$expense_time', notes = '$notes', updated_at = '$now' " .
           "WHERE id = '$id'";

    // 執行 SQL 插入語句
    if ($conn->query($sql) === TRUE) {
        $result = insertLog($conn, $_SERVER['REQUEST_URI'], $sql);
        if ($result === TRUE) {
            echo "記錄修改成功";
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