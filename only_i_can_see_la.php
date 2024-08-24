<?php
$config = include_once('config.php');
require 'modules/functions.php';

$time = $_GET['time'] ?? date("Ym");
if (!preg_match('/^\d{6}$/', $time)) {
    exit();
}

// 創建連接
$conn = connectDB($config);
if ($conn === false) {
    die("資料庫連接失敗");
}

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
    $account['retained_amount'] = getRetainedAmount($conn, $account_id, $account['retained_start']);

    $accounts[] = $account;
}

// 查詢piao紀錄
$piao_sql = "SELECT 
            SUM(CASE WHEN notes LIKE '代付漂%' THEN amount ELSE 0 END) AS paid_piao,
            SUM(CASE WHEN notes LIKE '漂代付%' THEN amount ELSE 0 END) AS piao_paid
            FROM expenses
            WHERE expense_time = ?";
$stmt = $conn->prepare($piao_sql);
$stmt->bind_param("s", $time);
$stmt->execute();
$result = $stmt->get_result();
$piao_records = $result->fetch_assoc();
$stmt->close();

$conn->close();
return ['accounts' => $accounts, 'piao_records' => $piao_records];
