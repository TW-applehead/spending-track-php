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

function hashString($str) {
    $hash = 0;
    $length = strlen($str);
    
    for ($i = 0; $i < $length; $i++) {
        $hash = (($hash << 5) - $hash) + ord($str[$i]);
        $hash &= 0xFFFFFFFF; // 保持32位整數範圍
    }
    
    if ($hash < 0) {
        $hash = ~$hash + 1;
    }
    
    return $hash;
}

function checkAuth($config, $finger_print, $auth, $text = null) {
    $ip = getUserIP();
    $hash_string = hashString($finger_print);

    if (in_array($hash_string, $config['ALLOWED_FINGERPRINT']) && $auth == $config['ALLOWED_PASSWORD']) {
        return true;
    } else {
        if (is_null($text)) {
            $text = "入侵警報！[" . $ip . ", " . $hash_string . "] 嘗試更改你東西";
        } else {
            $text = "[" . $ip . "] " . $text;
        }
        $conn = connectDB($config);
        insertLog($conn, $_SERVER['REQUEST_URI'], $text);
        return false;
    }
}

function checkHasInvasion($conn) {
    $sql = "SELECT COUNT(ip) as times, ip FROM logs WHERE sql_record LIKE '入侵警報%' GROUP BY ip";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function getNow() {
    $now = new DateTime('now', new DateTimeZone('Asia/Taipei'));
    return $now->format('Y-m-d H:i:s');
}

function getNextmonth($time) {
    $date = DateTime::createFromFormat('Ymd', $time . '01');
    return $date->modify('+1 month')->format('Ym');
}

function getPrevmonth($time) {
    $date = DateTime::createFromFormat('Ymd', $time . '01');
    return $date->modify('-1 month')->format('Ym');
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

function getRetainedAmount($conn, $account_id, $time) {
    $sql = "SELECT SUM(settle_amount) AS retained_amount FROM account_balances
            WHERE account_id = ? AND time >= ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $account_id, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['retained_amount'] ?? 0;
}

function getMaxBalanceDate($conn, $account_id) {
    $sql = "SELECT MAX(time) as max_time FROM `account_balances` WHERE balance <> 0 AND account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['max_time'] ?? date("Ym");
}
