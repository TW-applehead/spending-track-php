<?php $accounts = include('only_i_can_see_la.php'); ?>
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
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <div class="container">
            <div class="text-center mt-3">※刷卡 (當月不會扣 所以要記)</div>
            <div class="text-center">※台新代付 (直接算在該帳戶)</div>
            <div class="text-center text-danger mb-3">※直接扣該帳戶的不要記</div>
            <div class="shadow p-3 form-container">
                <form id="insertForm" class="row">
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
                        <label for="expense_time">時間</label>
                        <input type="text" class="form-control" id="time" name="expense_time" value="<?php echo $time; ?>" required>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="description">說明:</label>
                        <input type="text" class="form-control" id="description" name="description" value="">
                    </div>
                </form>
                <div class="text-center">
                    <button type="button" class="btn btn-primary" data-target="insert" id="saveInsert">儲存</button>
                </div>
            </div>
            <div class="row mb-5">
                <?php foreach ($accounts as $account): ?>
                    <div class="col-md-6 text-center mt-5">
                        <div class="table-title">
                            <div class="font-weight-bold align-self-center mb-2"><?php echo htmlspecialchars($account['name']); ?></div>
                            <div class="d-flex align-items-center mb-3 float-right">
                                <label for="balance_<?php echo htmlspecialchars($account['id']); ?>" class="mb-0 mr-2 small">發薪前餘額</label>
                                <input type="number" style="width: 120px; height: 32px;"
                                id="balance_<?php echo htmlspecialchars($account['id']); ?>" 
                                name="balance_<?php echo htmlspecialchars($account['id']); ?>" 
                                value="<?php echo htmlspecialchars($account['account_balance'] ?? ''); ?>" 
                                class="form-control text-center">
                                <button type="submit" class="btn btn-dark btn-sm updateBalance"
                                        data-account-id="<?php echo htmlspecialchars($account['id']); ?>"
                                        data-time="<?php echo $time; ?>">
                                    更新
                                </button>
                            </div>
                        </div>
                        <table class="table shadow">
                            <thead>
                                <tr>
                                    <th>金額</th>
                                    <th>說明</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($account['expenses']) > 0): ?>
                                    <?php foreach ($account['expenses'] as $expense): ?>
                                        <tr>
                                            <td style="color: <?php echo $expense['is_expense'] ? 'red' : 'green'; ?>">
                                                <?php echo ($expense['amount']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($expense['notes']) . ' '; ?>
                                                <?php echo $expense['other_account'] == 0 ? '' : '(代收付)'; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-dark btn-sm btn-edit-record"
                                                        data-target="#record-modal" data-toggle="modal"
                                                        data-account-id="<?php echo htmlspecialchars($account['id']); ?>"
                                                        data-id="<?php echo htmlspecialchars($expense['id']); ?>"
                                                        data-amount="<?php echo htmlspecialchars($expense['amount']); ?>"
                                                        data-other-account="<?php echo htmlspecialchars($expense['other_account']); ?>"
                                                        data-is-expense="<?php echo htmlspecialchars($expense['is_expense']); ?>"
                                                        data-notes="<?php echo htmlspecialchars($expense['notes']); ?>"
                                                        data-expense-time="<?php echo htmlspecialchars($expense['expense_time']); ?>">
                                                    編輯
                                                </button>
                                                <button class="btn btn-danger btn-sm btn-del-record"
                                                        data-id="<?php echo htmlspecialchars($expense['id']); ?>">
                                                    刪除
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
                            <tfoot>
                                <tr>
                                    <td colspan="2"><?php echo $account['balance_difference'] ? '' : '(尚無下個月餘額)'; ?></td>
                                    <td><span style="color: <?php echo $account['quota'] >= 0 ? 'green' : 'red'; ?>;"><?php echo abs($account['quota']) ?></span></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

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