<?php
$config = include_once('../config.php');
require 'functions.php';

if (checkAuth($config, $_REQUEST['finger_print'], $_REQUEST['auth'], "登入了系統")) {
    echo json_encode(['status' => true]);
} else {
    echo json_encode(['status' => false, 'message' => $config['NOT_ALLOWED_TEXT']]);
}

exit();
