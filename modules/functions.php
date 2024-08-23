<?php

function connectDB($config) {
    $conn = new mysqli($config['DB_SERVERNAME'], $config['DB_USERNAME'], $config['DB_PASSWORD'], $config['DB_NAME']);
    if ($conn->connect_error) {
        return false;
    } else {
        $conn->set_charset("utf8mb4");
        return $conn;
    }
}

function insertLog($conn, $path, $sql) {
    $user_ip = getUserIP();
    $now = getNow();

    $log_sql = "INSERT INTO logs (ip, path, sql_record, created_at) VALUES (?, ?, ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    $log_stmt->bind_param("ssss", $user_ip, $path, $sql, $now);
    if ($log_stmt->execute()) {
        $log_stmt->close();
        return true;
    } else {
        $log_stmt->close();
        return "錯誤: " . $log_sql . "<br>" . $conn->error;
    }
}

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function getNow() {
    $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
    return $now->format('Y-m-d H:i:s');
}

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

function getAllowance($conn, $account_id) {
    $sql = "SELECT monthly_allowance FROM accounts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['monthly_allowance'] ?? 0;
}
