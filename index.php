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

$now = date("Ym");

// 從資料庫讀取所有expenses記錄
$sql = "SELECT * FROM expenses WHERE account_id = 1 AND expense_time = '" . $now . "'";
$food_expenses = $conn->query($sql);
$sql = "SELECT * FROM expenses WHERE account_id = 2 AND expense_time = '" . $now . "'";
$entertain_expenses = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Spending Track</title>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="  crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    </head>
<body>
<div class="container">
    <div class="text-center mt-3">刷卡 (當月不會扣 所以要記)</div>
    <div class="text-center mb-3">台新代付 (直接算在該帳戶)</div>
    <form action="insert.php" method="POST" class="row">
        <div class="form-group col-md-6">
            <label for="amount">金額:</label>
            <input class="form-control" type="number" id="amount" name="amount" required>
        </div>

        <div class="form-group col-md-6">
            <label for="account">選擇帳戶</label>
            <select class="form-control" id="account" name="account_id" required>
                <option value="1">飲食</option>
                <option value="2">娛樂</option>
            </select>
        </div>

        <!-- 收入或支出選擇 -->
        <div class="form-group col-md-6">
            <label>類型</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_expense" id="expense" value="1" checked>
                <label class="form-check-label" for="expense">花費</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="is_expense" id="income" value="0">
                <label class="form-check-label" for="income">收入</label>
            </div>
        </div>

        <!-- 其他帳戶代付 -->
        <div class="form-group col-md-6">
            <label>是否為其他帳戶代付？</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="other_account" id="is-not-other-account" value="0" checked>
                <label class="form-check-label" for="is_not_other_account">否</label>
            </div>
            <div class="form-check form-check-inline is-food-account" style="display: none">
                <input class="form-check-input" type="radio" name="other_account" id="is-food-account" value="1">
                <label class="form-check-label" for="is_food_account">是，飲食代付</label>
            </div>
            <div class="form-check form-check-inline is-entertain-account">
                <input class="form-check-input" type="radio" name="other_account" id="is-entertain-account" value="2">
                <label class="form-check-label" for="is_entertain_account">是，娛樂代付</label>
            </div>
        </div>

        <!-- 時間 -->
        <div class="form-group col-md-6">
            <label for="expense_time">時間</label>
            <input type="text" class="form-control" id="time" name="expense_time" value="<?php echo $now; ?>" required>
        </div>

        <div class="form-group col-md-6">
            <label for="description">說明:</label>
            <input type="text" class="form-control" id="description" name="description" value="">
        </div>

        <button type="submit" class="btn btn-primary mx-auto">提交</button>
    </form>
    <div class="row">
        <div class="col-md-6 text-center mt-5">
            <table class="table">
                <thead>
                    <tr>
                        <th>金額</th>
                        <th>是否為代付</th>
                        <th>說明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($food_expenses->num_rows > 0) {
                        // 輸出每一筆記錄
                        while($row = $food_expenses->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='color: " . ($row['is_expense'] ? 'red' : 'green') . "'>" . htmlspecialchars($row['amount']) . "</td>";
                            echo "<td>" . ($row['other_account'] == 0 ? '否' : '是') . "</td>";
                            echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                            echo '<td><button class="btn btn-dark btn-sm btn-edit-record" data-target="#record-modal" data-toggle="modal"
                                            data-account-id="1" data-id="' . htmlspecialchars($row['id']) . '" data-amount="' . htmlspecialchars($row['amount']) . '"
                                            data-other-account="' . htmlspecialchars($row['other_account']) . '" data-is-expense="' . htmlspecialchars($row['is_expense']) . '"
                                            data-notes="' . htmlspecialchars($row['notes']) . '">
                                            編輯</button>
                                    <button class="btn btn-danger btn-sm btn-del-record" data-id="' . htmlspecialchars($row['id']) . '">刪除</button>
                                    </td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>無記錄</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6 text-center mt-5">
            <table class="table">
                <thead>
                    <tr>
                        <th>金額</th>
                        <th>是否為代付</th>
                        <th>說明</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($entertain_expenses->num_rows > 0) {
                        // 輸出每一筆記錄
                        while($row = $entertain_expenses->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='color: " . ($row['is_expense'] ? 'red' : 'green') . "'>" . htmlspecialchars($row['amount']) . "</td>";
                            echo "<td>" . ($row['other_account'] == 0 ? '否' : '是') . "</td>";
                            echo "<td>" . htmlspecialchars($row['notes']) . "</td>";
                            echo '<td><button class="btn btn-dark btn-sm btn-edit-record" data-target="#record-modal" data-toggle="modal"
                                            data-account-id="2" data-id="' . htmlspecialchars($row['id']) . '" data-amount="' . htmlspecialchars($row['amount']) . '"
                                            data-other-account="' . htmlspecialchars($row['other_account']) . '" data-is-expense="' . htmlspecialchars($row['is_expense']) . '"
                                            data-notes="' . htmlspecialchars($row['notes']) . '">
                                            編輯</button>
                                    <button class="btn btn-danger btn-sm btn-del-record" data-id="' . htmlspecialchars($row['id']) . '">刪除</button>
                                    </td>';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>無記錄</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $conn->close(); ?>
</div>
</body>
<script>
    $(document).ready(function() {
        $('#account').on('change', function() {
            let account = $(this).val();
            if (account == 1) {
                $('.is-food-account').hide();
                $('.is-entertain-account').show();
            } else if (account == 2)  {
                $('.is-food-account').show();
                $('.is-entertain-account').hide();
            }
        });

        $('.btn-edit-record').on('click', function() {
            let id = $(this).data('id');
            let accountId = $(this).data('account-id');
            let amount = $(this).data('amount');
            let isExpense = $(this).data('is-expense');
            let otherAccount = $(this).data('other-account');
            let notes = $(this).data('notes');

            // 填充表單欄位
            $('#expenseId').val(id);
            $('#editAmount').val(amount);
            $('#editNotes').val(notes);
            $('input[name="other_account"][value="' + otherAccount + '"]').prop('checked', true);
            $('input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);
            $('div[class*="otherAccount"]').show();
            $('.otherAccountYes' + accountId).hide();

            $('#editModal').modal('show');
        });

        $('.btn-del-record').on('click', function() {
            if (confirm("確定刪除?")) {
                $.ajax({
                url: "delete.php",
                type: 'POST',
                data: {
                    id: $(this).data('id'),
                    _token: $('input[name="_token"]').val(),
                },
                success: function(response) {
                    alert(response);
                    location.reload();
                },
                error: function(errors) {
                    console.error(errors);
                }
            });
            } else {
                return;
            }
        });
    });
</script>
</html>