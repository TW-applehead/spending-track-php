<?php
$config = include_once('config.php');
require 'modules/functions.php';

// 創建連接
$conn = connectDB($config);
if ($conn === false) {
    die("資料庫連接失敗");
}

$time = $_GET['time'] ?? date("Ym");
if (!preg_match('/^\d{6}$/', $time)) {
    exit();
}
$monthlySettle = $_GET['monthlySettle'] ?? false;

// 執行 SQL 查詢，取得帳戶及其費用和收入的資料
$sql = "SELECT accounts.*, 
        SUM(CASE WHEN expenses.is_expense = 1 AND expenses.other_account = 0 THEN expenses.amount ELSE 0 END) AS expense_sum, 
        SUM(CASE WHEN expenses.is_expense = 0 AND expenses.other_account = 0 THEN expenses.amount ELSE 0 END) AS income_sum
        FROM accounts
        LEFT JOIN expenses ON accounts.id = expenses.account_id AND expenses.expense_time = ?
        GROUP BY accounts.id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $time);
$stmt->execute();
$result = $stmt->get_result();

$accounts = [];
$settle_amount = [];
while ($account = $result->fetch_assoc()) {
    $account_id = $account['id'];

    // 取得代收付總額
    $food_behalf_income = getBehalfSum($conn, 0, 1, $time);
    $entertain_behalf_income = getBehalfSum($conn, 0, 2, $time);
    $food_behalf_expense = getBehalfSum($conn, 1, 1, $time);
    $entertain_behalf_expense = getBehalfSum($conn, 1, 2, $time);

    $food_behalf_sum = $food_behalf_expense - $food_behalf_income;
    $entertain_behalf_sum = $entertain_behalf_expense - $entertain_behalf_income;

    // 計算餘額差異
    $account_balance = getBalance($conn, $account_id, $time);
    $next_month = DateTime::createFromFormat('Ym', $time)->modify('+1 month')->format('Ym');
    $next_account_balance = getBalance($conn, $account_id, $next_month);
    if ($next_account_balance && $account_balance) {
        $balance_difference = $next_account_balance - $account_balance;
        $account['balance_difference'] = true;
    } else {
        $balance_difference = 0;
        $account['balance_difference'] = false;
    }

    // 計算月總花費
    if ($account_id == 1) {
        $account['quota'] = $balance_difference + $account['income_sum'] - $account['expense_sum'] + $food_behalf_sum - $entertain_behalf_sum;
    } else {
        $account['quota'] = $balance_difference + $account['income_sum'] - $account['expense_sum'] - $food_behalf_sum + $entertain_behalf_sum;
    }
    $account['expenses'] = getExpenses($conn, $account_id, $time);
    $account['account_balance'] = $account_balance;

    $accounts[] = $account;

    if ($monthlySettle) {
        $monthly_allowance = getAllowance($conn, $account_id);
        $settle_amount[$account_id] = $monthly_allowance + $account['quota'];
    }
}

if ($monthlySettle) {
    $settle_sql = "UPDATE account_balances SET settle_amount = CASE " .
                  "WHEN account_id = 1 THEN ? " .
                  "WHEN account_id = 2 THEN ? " .
                  "ELSE settle_amount END " .
                  "WHERE account_id IN (1, 2)" .
                  "AND time = ?";
    $settle_stmt = $conn->prepare($settle_sql);
    $settle_stmt->bind_param("iis", $settle_amount[1], $settle_amount[2], $time);
    if ($settle_stmt->execute()) {
        $log_sql = "UPDATE account_balances SET settle_amount = CASE " .
                   "WHEN account_id = 1 THEN '$settle_amount[1]' " .
                   "WHEN account_id = 2 THEN '$settle_amount[2]' " .
                   "ELSE settle_amount END " .
                   "WHERE account_id IN (1, 2)" .
                   "AND time = '$time'";
        $result = insertLog($conn, "/only_i_can_see_la.php", $log_sql);
        if ($result === TRUE) {
            echo $time . "月盈餘已更新";
        } else {
            echo $result;
        }
    } else {
        echo "更新操作失敗，請再試一次";
    }
    $settle_stmt->close();
}

$conn->close();
return $accounts;
