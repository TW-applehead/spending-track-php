$(document).ready(function() {
    // 刪除紀錄
    $('.btn-del-record').on('click', function() {
        if (confirm("確定刪除?")) {
            $.ajax({
            url: "delete.php",
            type: 'POST',
            data: {
                id: $(this).data('id')
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

    // 儲存或編輯紀錄
    $('#saveInsert, #saveEdit').on('click', function() {
        let target = $(this).data('target');
        if ($('#' + target + 'Form input[name="amount"]').val() < 0) {
            alert("金額是負是怎樣? 重寫");
            return false;
        } else if ($('#' + target + 'Form input[name="amount"]').val() == 0) {
            alert("金額是0還紀錄幹嘛? 重寫");
            return false;
        }

        let formData = $('#' + target + 'Form').serialize();
        let url = target + ".php";

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(errors) {
                console.error(errors);
            }
        });
    });

    // 更新當月發薪前帳戶餘額
    $('.updateBalance').on('click', function() {
        $.ajax({
            url: "updateBalance.php",
            type: 'POST',
            data: {
                account_id: $(this).data('account-id'),
                time: $(this).data('time'),
                balance: $('input[name="balance_' + $(this).data('account-id') + '"]').val(),
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(errors) {
                console.error(errors);
            }
        });
    });

    // 更新累積盈餘開始日期
    $('.update-retained-start').on('click', function() {
        let account_id = $(this).data('account-id');
        $.ajax({
            url: "modules/updateRetainedStart.php",
            type: 'POST',
            data: {
                account_id: account_id,
                retained_start: $('input[name="retained_start' + account_id + '"]').val(),
            },
            success: function(response) {
                alert(response);
                location.reload();
            },
            error: function(errors) {
                console.error(errors);
            }
        });
    });

    // 動態修改新增紀錄表單的選項
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

    // 動態修改編輯紀錄表單的選項
    $('#editForm input[name="account_id"]').on('change', function() {
        let account = $(this).val();
        let another_account = 1;
        if (account == 1) {
            another_account = 2;
        }
        $('div[class*="otherAccountYes"]').show();
        $('.otherAccountYes' + account).hide();

        if (!$('#editForm #otherAccountNo').is(':checked')) {
            $('#otherAccountYes' + another_account).prop('checked', true);
        }
    });

    // 顯示編輯視窗
    $('.btn-edit-record').on('click', function() {
        let id = $(this).data('id');
        let accountId = $(this).data('account-id');
        let amount = $(this).data('amount');
        let isExpense = $(this).data('is-expense');
        let expenseTime = $(this).data('expense-time');
        let otherAccount = $(this).data('other-account');
        let notes = $(this).data('notes');

        $('#expenseId').val(id);
        $('#editAmount').val(amount);
        $('#editNotes').val(notes);
        $('#expenseTime').val(expenseTime);
        $('input[name="account_id"][value="' + accountId + '"]').prop('checked', true);
        $('input[name="other_account"][value="' + otherAccount + '"]').prop('checked', true);
        $('input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);
        $('div[class*="otherAccount"]').show();
        $('.otherAccountYes' + accountId).hide();

        $('#editModal').modal('show');
    });
});