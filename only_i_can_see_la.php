<?php
$config = include_once('config.php');

// 資料庫連接設置
$servername = $config['DB_SERVERNAME'];
$username = $config['DB_USERNAME'];
$password = $config['DB_PASSWORD'];
$dbname = $config['DB_NAME'];

// 創建連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$time = $_GET['time'] ?? date("Ym");

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

    // 計算配額
    if ($account_id == 1) {
        $account['quota'] = $balance_difference + $account['income_sum'] - $account['expense_sum'] + $food_behalf_sum - $entertain_behalf_sum;
    } else {
        $account['quota'] = $balance_difference + $account['income_sum'] - $account['expense_sum'] - $food_behalf_sum + $entertain_behalf_sum;
    }
    $account['expenses'] = getExpenses($conn, $account_id, $time);
    $account['account_balance'] = $account_balance;

    $accounts[] = $account;
}

$conn->close();
return $accounts;

function getBehalfSum($conn, $is_expense, $other_account, $time) {
    $sql = "SELECT SUM(amount) AS sum FROM expenses WHERE is_expense = ? AND other_account = ? AND expense_time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $is_expense, $other_account, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['sum'] ?? 0;
}

function getBalance($conn, $account_id, $time) {
    $sql = "SELECT balance FROM account_balances WHERE account_id = ? AND time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $account_id, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['balance'] ?? 0;
}

function getExpenses($conn, $account_id, $time) {
    $sql = "SELECT * FROM expenses WHERE account_id = ? AND expense_time = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $account_id, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}
