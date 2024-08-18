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
        let expenseTime = $(this).data('expense-time');
        let otherAccount = $(this).data('other-account');
        let notes = $(this).data('notes');

        // 填充表單欄位
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

    $('#saveChanges').on('click', function() {
        var formData = $('#editForm').serialize();

        $.ajax({
            url: "edit.php",
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
});