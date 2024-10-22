<?php $data = include('only_i_can_see_la.php'); ?>

<!DOCTYPE html>
<html lang="zh-Hant">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Spending Track</title>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"  integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="  crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
        <script src="only-i-can-see-la.js" type="text/javascript"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="container">
            <div id="top" data-auth="<?php echo isset($_REQUEST['auth']) ? $_REQUEST['auth'] : ""; ?>"></div>
            <?php if (!empty($data['has_invasion'])): ?>
            <a href="#headingZero" class="btn text-danger mt-3">
                <img src="images/warning.png" class="w-75" />
                <h2>請注意入侵訊息</h2>
            </a>
            <?php endif; ?>
            <div class="justify-content-between align-items-center lead d-flex my-3">
                <a href="?time=<?php echo $prev_month; ?><?php echo isset($_REQUEST['auth']) ? '&auth=' . $_REQUEST['auth'] : ""; ?>" class="btn page-button d-flex align-items-center">
                    <img src="images/up.svg" width="20" class="mr-1" style="transform: rotate(-90deg);" />Prev
                </a>
                <div class="font-weight-bold"><?php echo $time; ?>月帳單</div>
                <a href="?time=<?php echo $next_month; ?><?php echo isset($_REQUEST['auth']) ? '&auth=' . $_REQUEST['auth'] : ""; ?>" class="btn page-button d-flex align-items-center">
                    Next<img src="images/up.svg" width="20" class="ml-1" style="transform: rotate(90deg);" />
                </a>
            </div>
            <?php if ($time != $now_month): ?>
                <div class="card border-0">
                    <div class="card-header border-bottom-0 p-0" id="headingForm">
                        <h5 class="mb-0">
                            <button class="btn w-100 bg-white text-left py-3" data-toggle="collapse" data-target="#collapseForm" aria-expanded="true" aria-controls="collapseForm">
                                新增記錄
                            </button>
                        </h5>
                    </div>
                    <div id="collapseForm" class="collapse" aria-labelledby="headingForm" data-parent="#accordion">
                        <div class="card-body pt-0 px-0">
            <?php endif; ?>
            <div class="font-italic text-center mb-3">
                ※原則上不是直接扣該帳戶的都要記<br>
                ※帳戶代收的錢要記<br>
                ※固定花費、現金花費不要記
            </div>
            <div class="shadow p-3 form-container mb-5">
                <form id="insertForm" class="row">
                    <div class="form-group col-md-6">
                        <label for="amount">金額：</label>
                        <input class="form-control" type="number" id="amount" name="amount" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="account">帳戶：</label>
                        <select class="form-control" id="account" name="account_id" required>
                            <option value="1">飲食</option>
                            <option value="2">娛樂</option>
                        </select>
                    </div>

                    <!-- 收入或支出選擇 -->
                    <div class="form-group col-md-6">
                        <label>類型：</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_expense" id="expense" value="1" checked>
                            <label class="form-check-label" for="expense">花費</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="is_expense" id="income" value="0">
                            <label class="form-check-label" for="income">收入</label>
                        </div>
                    </div>

                    <!-- 其他帳戶代收付 -->
                    <div class="form-group col-md-6">
                        <label>是否為其他帳戶代收付？</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="other_account" id="is-not-other-account" value="0" checked>
                            <label class="form-check-label" for="is_not_other_account">否</label>
                        </div>
                        <div class="form-check form-check-inline is-food-account" style="display: none">
                            <input class="form-check-input" type="radio" name="other_account" id="is-food-account" value="1">
                            <label class="form-check-label" for="is_food_account">是，飲食代收付</label>
                        </div>
                        <div class="form-check form-check-inline is-entertain-account">
                            <input class="form-check-input" type="radio" name="other_account" id="is-entertain-account" value="2">
                            <label class="form-check-label" for="is_entertain_account">是，娛樂代收付</label>
                        </div>
                    </div>

                    <!-- 時間 -->
                    <div class="form-group col-md-6">
                        <label for="expense_time">時間：</label>
                        <input type="text" class="form-control" id="time" name="expense_time" value="<?php echo $time; ?>" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="description">說明：</label>
                        <div class="form-check form-check-inline float-right">
                            <input class="form-check-input" type="radio" name="is_piao" value="漂代付">
                            <label class="form-check-label mr-3">漂代付</label>
                            <input class="form-check-input" type="radio" name="is_piao" value="代付漂">
                            <label class="form-check-label mr-3">代付漂</label>
                            <input class="form-check-input" type="radio" name="is_piao" value="漂代收">
                            <label class="form-check-label">漂代收</label>
                        </div>
                        <input type="text" class="form-control" id="description" name="description" value="">
                    </div>
                </form>
                <div class="text-center">
                    <button type="button" class="btn btn-primary mb-1" data-target="insert" id="saveInsert">儲存</button>
                </div>
            </div>
            <?php if ($time != $now_month): ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <?php foreach ($data['accounts'] as $account): ?>
                    <div class="col-md-6 text-center mb-5">
                        <div class="table-title">
                            <p class="font-weight-bold align-self-center mb-2"><?php echo htmlspecialchars($account['name']); ?></p>
                            <div class="d-flex align-items-center mb-3 justify-content-end">
                                <label for="balance_<?php echo htmlspecialchars($account['id']); ?>" class="mb-0 mr-2 small">發薪前餘額</label>
                                <input type="number" style="width: 120px; height: 32px;"
                                id="balance_<?php echo htmlspecialchars($account['id']); ?>" 
                                name="balance_<?php echo htmlspecialchars($account['id']); ?>" 
                                value="<?php echo htmlspecialchars($account['account_balance'] ?? ''); ?>" 
                                class="form-control text-center">
                                <button type="submit" class="btn btn-dark btn-sm update-balance"
                                        data-account-id="<?php echo htmlspecialchars($account['id']); ?>"
                                        data-time="<?php echo $time; ?>">
                                    更新
                                </button>
                            </div>
                        </div>
                        <div class="table-content shadow">
                            <table class="table thead mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 16%;">金額</th>
                                        <th class="text-left">說明</th>
                                        <th style="width: 30%;">操作</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="tbody" <?php echo $time != $now_month ? 'style="display: none;"' : ''; ?>>
                                <table class="table table-striped mb-0">
                                    <tbody>
                                        <?php if (count($account['expenses']) > 0): ?>
                                            <?php foreach ($account['expenses'] as $expense): ?>
                                                <tr>
                                                    <td style="color: <?php echo $expense['is_expense'] ? 'red' : 'green'; ?>; width: 16%;">
                                                        <?php echo ($expense['amount']); ?>
                                                    </td>
                                                    <td class="text-left expense-note" tabindex="0">
                                                        <?php echo htmlspecialchars($expense['notes']) . ' '; ?>
                                                        <?php echo $expense['other_account'] == 0 ? '' : '(代收付)'; ?>
                                                        <div class="time-info">
                                                            <?php echo $expense['created_time']; ?>
                                                            <?php echo $expense['edited'] ? ' (' . $expense['updated_time'] . ' 編輯)' : ''; ?>
                                                        </div>
                                                    </td>
                                                    <td style="width: 30%;">
                                                        <button class="btn btn-sm btn-edit-record"
                                                                data-target="#record-modal" data-toggle="modal"
                                                                data-account-id="<?php echo htmlspecialchars($account['id']); ?>"
                                                                data-id="<?php echo htmlspecialchars($expense['id']); ?>"
                                                                data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
                                                                data-other-account="<?php echo htmlspecialchars($expense['other_account']); ?>"
                                                                data-is-expense="<?php echo htmlspecialchars($expense['is_expense']); ?>"
                                                                data-notes="<?php echo htmlspecialchars($expense['notes']); ?>"
                                                                data-expense-time="<?php echo htmlspecialchars($expense['expense_time']); ?>">
                                                            <img src="images/edit.svg" width="20" />
                                                        </button>
                                                        <button class="btn btn-sm btn-del-record"
                                                                data-id="<?php echo htmlspecialchars($expense['id']); ?>">
                                                            <img src="images/delete.svg" width="22" />
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3">無記錄</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <table class="table tfoot">
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="border-bottom-0 text-right"><?php echo $account['balance_difference'] ? '本月花費' : '(尚無下個月餘額)'; ?></td>
                                        <td style="width: 28%;" class="border-bottom-0 text-left">
                                            <span id="monthly-expense-<?php echo $account['id']; ?>" data-value="<?php echo $account['quota'] ?>"
                                                data-balance-difference="<?php echo $account['balance_difference']; ?>"
                                                style="color: <?php echo $account['quota'] >= 0 ? 'green' : 'red'; ?>;">
                                                <?php echo abs($account['quota']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" class="border-top-0 pt-0 pb-3">
                                            <div class="justify-content-end align-items-center d-flex">
                                                <input type="text" style="width: 90px; height: 26px; border-radius: unset; padding-bottom: 2px;"
                                                    id="retained-start<?php echo htmlspecialchars($account['id']); ?>" 
                                                    name="retained_start<?php echo htmlspecialchars($account['id']); ?>" 
                                                    value="<?php echo $account['retained_start'] ?>" 
                                                    class="form-control border-left-0 border-top-0 border-right-0 pt-0 pl-2 pr-3">
                                                <button class="btn p-0 update-retained-start" style="margin-left: -20px; margin-top: -4px;"
                                                        data-account-id="<?php echo htmlspecialchars($account['id']); ?>">
                                                    <img src="images/update.svg" width="20" />
                                                </button>
                                                <div class="ml-2">至今累積利差</div>
                                            </div>
                                        </td>
                                        <td style="width: 28%;" class="border-top-0 pt-0 pb-3 text-left">
                                            <span style="color: <?php echo $account['retained_amount'] >= 0 ? 'green' : 'red'; ?>;"><?php echo abs($account['retained_amount']) ?></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="accordion" class="mb-5">
                <?php if (!empty($data['has_invasion'])): ?>
                <div class="card">
                    <div class="card-header p-0" id="headingZero">
                        <h5 class="mb-0">
                            <button class="btn w-100 text-left text-danger py-3" data-toggle="collapse" data-target="#collapseZero" aria-expanded="true" aria-controls="collapseZero">
                                入侵訊息
                            </button>
                        </h5>
                    </div>
                    <div id="collapseZero" class="collapse" aria-labelledby="headingZero" data-parent="#accordion">
                        <div class="card-body">
                            <?php foreach ($data['has_invasion'] as $message): ?>
                               <span class="text-danger"><?php echo $message['ip']; ?></span> 嘗試使用您的系統 <?php echo $message['times']; ?> 次<br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header p-0" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn w-100 text-left py-3" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                漂
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 font-italic text-center mb-3">
                                    ※代付漂的現金要記，手動扣在領錢記錄裡
                                </div>
                                <div class="col-6">漂代付：<span style="color: red;"><?php echo $data['piao_records']['piao_paid'] ?? '0'; ?></span></div>
                                <div class="col-6">代付漂：<span style="color: red;"><?php echo $data['piao_records']['paid_piao'] ?? '0'; ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header p-0" id="headingTwo">
                        <h5 class="mb-0">
                            <button class="btn w-100 text-left py-3" data-toggle="collapse"
                                    data-target="#collapseMonthlySettle" aria-expanded="false" aria-controls="collapseMonthlySettle">
                                計算月盈餘
                            </button>
                        </h5>
                    </div>
                    <div id="collapseMonthlySettle" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center">
                                <input type="text" id="monthly-settle" name="settle_time" class="form-control w-auto" value="<?php echo $time; ?>">
                                <button class="btn btn-dark btn-sm monthly-settle">
                                    計算
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header p-0" id="headingThree">
                        <h5 class="mb-0">
                            <button class="btn w-100 text-left py-3" data-toggle="collapse" data-target="#collapseChangeBase" aria-expanded="false" aria-controls="collapseChangeBase">
                                新增不納入收入的收入
                            </button>
                        </h5>
                    </div>
                    <div id="collapseChangeBase" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body text-center">
                            <div class="d-flex justify-content-center">
                                <input type="number" id="change-base-amount" name="change_base_amount" class="form-control w-50 mr-1" value="10000" step="100">
                                <select class="form-control w-auto mr-1" id="change-account" name="account_id" required>
                                    <option value="1">飲食</option>
                                    <option value="2" selected="selected">娛樂</option>
                                </select>
                                <button class="btn btn-dark btn-sm change-base">更新</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="#top" class="back-to-top"><img src="images/up.svg" width="40" /></a>

            <!-- 編輯視窗 -->
            <div id="record-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" style="display: none;">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">編輯紀錄</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm">
                                <div class="mb-3">
                                    <label for="editAmount" class="form-label">金額</label>
                                    <input type="number" class="form-control" id="editAmount" name="amount">
                                </div>
                                <div class="mb-3 d-flex">
                                    <label for="account_id" class="form-label">帳戶</label>
                                    <div class="mx-3">
                                        <input type="radio" id="accountId1" name="account_id" value="1">
                                        <label for="accountId1">飲食</label>
                                    </div>
                                    <div class="mx-3">
                                        <input type="radio" id="accountId2" name="account_id" value="2">
                                        <label for="accountId2">娛樂</label>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex">
                                    <label class="form-label">是否為代收付</label>
                                    <div class="mx-3 otherAccountNo">
                                        <input type="radio" id="otherAccountNo" name="other_account" value="0">
                                        <label for="otherAccountNo">否</label>
                                    </div>
                                    <div class="mx-3 otherAccountYes1">
                                        <input type="radio" id="otherAccountYes1" name="other_account" value="1">
                                        <label for="otherAccountYes1">是 (飲食代收付)</label>
                                    </div>
                                    <div class="mx-3 otherAccountYes2">
                                        <input type="radio" id="otherAccountYes2" name="other_account" value="2">
                                        <label for="otherAccountYes2">是 (娛樂代收付)</label>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex">
                                    <label class="form-label">是否為費用</label>
                                    <div class="mx-3">
                                        <input type="radio" id="isExpenseYes" name="is_expense" value="1">
                                        <label for="isExpenseYes">是</label>
                                    </div>
                                    <div class="mx-3">
                                        <input type="radio" id="isExpenseNo" name="is_expense" value="0">
                                        <label for="isExpenseNo">否</label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="expenseTime" class="form-label">時間</label>
                                    <input type="text" class="form-control" id="expenseTime" name="expense_time"">
                                </div>
                                <div class="mb-3">
                                    <label for="editNotes" class="form-label">說明</label>
                                    <input type="text" class="form-control" id="editNotes" name="notes">
                                </div>
                                <input type="hidden" id="expenseId" name="id">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-target="edit" id="saveEdit">儲存</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>