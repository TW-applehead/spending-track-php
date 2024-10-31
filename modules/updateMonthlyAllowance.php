<?php // 更新帳戶當月發薪前餘額
$config = include_once('../config.php');
require 'functions.php';

if (!checkAuth($config, $_REQUEST['finger_print'], $_REQUEST['auth'])) {
    die($config['NOT_ALLOWED_TEXT']);
}

if (preg_match('/^-?\d+$/', $_POST['food_allowance']) && preg_match('/^-?\d+$/', $_POST['entertain_allowance'])) {
    // 創建連接
    $conn = connectDB($config);
    if ($conn === false) {
        die("資料庫連接失敗");
    }

    $allowances = [$_POST['food_allowance'], $_POST['entertain_allowance']];
    foreach ($allowances as $key => $allowance) {
        $account_id = $key + 1;
        $allowance = $conn->real_escape_string($allowance);
        $original_allowance = getAllowance($conn, $account_id);

        if ($allowance != $original_allowance) {
            $allowance_sql = "UPDATE accounts SET monthly_allowance = ? " .
                            " WHERE id = ?";
            $allowance_stmt = $conn->prepare($allowance_sql);
            $allowance_stmt->bind_param("ii", $allowance, $account_id);
            if ($allowance_stmt->execute()) {
                $log_sql = "UPDATE accounts SET monthly_allowance = '$allowance'" .
                            " WHERE id = '$account_id'";
                $result = insertLog($conn, $_SERVER['REQUEST_URI'], $log_sql);
                if ($result === TRUE) {
                    echo "帳戶" . $account_id . "的扣打已更新為" . $allowance . "\n";
                } else {
                    echo $result;
                }
            } else {
                echo "更新操作失敗，請再試一次";
            }
        }
    }

    $allowance_stmt->close();
    $conn->close();
}

exit();
?>