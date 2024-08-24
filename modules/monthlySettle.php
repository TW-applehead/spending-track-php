<?php // 計算當月盈餘
$config = include_once('../config.php');
require 'functions.php';

if (!checkUserIP($config['ALLOWED_IP'])) {
    die($config['NOT_ALLOWED_TEXT']);
}

$time = $_POST['time'];
$food_expense = $_POST['food_expense'];
$entertain_expense = $_POST['entertain_expense'];

if (preg_match('/^\d{6}$/', $time) && preg_match('/^-?\d+$/', $food_expense) && preg_match('/^-?\d+$/', $entertain_expense)) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    // 防止SQL注入的資料過濾
    $account_id = $conn->real_escape_string($time);
    $food_expense = $conn->real_escape_string($food_expense);
    $entertain_expense = $conn->real_escape_string($entertain_expense);
    $now = getNow();

    $food_monthly_allowance = getAllowance($conn, 1);
    $food_settle_amount = $food_monthly_allowance + $food_expense;
    $entertain_monthly_allowance = getAllowance($conn, 2);
    $entertain_settle_amount = $entertain_monthly_allowance + $entertain_expense;

    $settle_sql = "UPDATE account_balances SET settle_amount = CASE " .
                  "WHEN account_id = 1 THEN ? " .
                  "WHEN account_id = 2 THEN ? " .
                  "ELSE settle_amount END " .
                  "WHERE account_id IN (1, 2)" .
                  "AND time = ?";
    $settle_stmt = $conn->prepare($settle_sql);
    $settle_stmt->bind_param("iis", $food_settle_amount, $entertain_settle_amount, $time);
    if ($settle_stmt->execute()) {
        $log_sql = "UPDATE account_balances SET settle_amount = CASE " .
                   "WHEN account_id = 1 THEN '$food_settle_amount' " .
                   "WHEN account_id = 2 THEN '$entertain_settle_amount' " .
                   "ELSE settle_amount END " .
                   "WHERE account_id IN (1, 2)" .
                   "AND time = '$time'";
        $result = insertLog($conn, "/monthlySettle.php", $log_sql);
        if ($result === TRUE) {
            echo $time . "月盈餘已更新";
        } else {
            echo $result;
        }
    } else {
        echo "更新操作失敗，請再試一次";
    }
    $settle_stmt->close();
    $conn->close();
}

exit();
?>