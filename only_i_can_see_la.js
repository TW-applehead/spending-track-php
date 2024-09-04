$(document).ready(function() {
    let auth = $('#top').data('auth');
    let finger_print = generateFingerprint();

    // 刪除紀錄
    $('.btn-del-record').on('click', function() {
        if (confirm("確定刪除?")) {
            $.ajax({
            url: "delete.php",
            type: 'POST',
            data: {
                id: $(this).data('id'),
                auth: auth,
                finger_print: finger_print,
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
            alert("金額是負的是怎樣? 重寫");
            return false;
        } else if ($('#' + target + 'Form input[name="amount"]').val() == 0) {
            alert("金額是0還紀錄幹嘛? 重寫");
            return false;
        }

        let formData = $('#' + target + 'Form').serializeArray();
        formData.push({name: "auth", value: auth});
        formData.push({name: "finger_print", value: finger_print});
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
    $('.update-balance').on('click', function() {
        $.ajax({
            url: "modules/updateBalance.php",
            type: 'POST',
            data: {
                account_id: $(this).data('account-id'),
                time: $(this).data('time'),
                balance: $('input[name="balance_' + $(this).data('account-id') + '"]').val(),
                auth: auth,
                finger_print: finger_print,
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
                auth: auth,
                finger_print: finger_print,
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

    // 新增不納入收入的收入
    $('.change-base').on('click', function() {
        $.ajax({
            url: "modules/changeBase.php",
            type: 'POST',
            data: {
                amount: $('#collapseChangeBase input[name="change_base_amount"]').val(),
                account_id: $('#collapseChangeBase select[name="account_id"]').val(),
                auth: auth,
                finger_print: finger_print,
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

    // 計算該月盈餘
    $('.monthly-settle').on('click', function() {
        if($('#monthly-expense-1').data('balance-difference') && $('#monthly-expense-2').data('balance-difference')) {
            $.ajax({
                url: "modules/monthlySettle.php",
                type: 'POST',
                data: {
                    time: $('#collapseMonthlySettle input[name="settle_time"]').val(),
                    food_expense: $('#monthly-expense-1').data('value'),
                    entertain_expense: $('#monthly-expense-2').data('value'),
                    auth: auth,
                    finger_print: finger_print,
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
            alert("有帳戶尚未填入隔月發薪前餘額");
            return false;
        }
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

    // 自動帶入備註
    $('input[name="is_piao"]').on('change', function() {
        let value = $(this).val();
        $('input[name="description"]').val(value);
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
        $('#record-modal input[name="account_id"][value="' + accountId + '"]').prop('checked', true);
        $('#record-modal input[name="other_account"][value="' + otherAccount + '"]').prop('checked', true);
        $('#record-modal input[name="is_expense"][value="' + isExpense + '"]').prop('checked', true);
        $('#record-modal div[class*="otherAccount"]').show();
        $('#record-modal .otherAccountYes' + accountId).hide();
    });

    window.onscroll = function() {
        if (document.body.scrollTop > 360 || document.documentElement.scrollTop > 360) {
            $('.back-to-top').show();
        } else {
            $('.back-to-top').hide();
        }
    };

    // 雙擊移至畫面最下方
    let lastClickTime = 0;
    document.addEventListener('click', function () {
        const currentTime = new Date().getTime();
        const timeDifference = currentTime - lastClickTime;

        if (timeDifference < 300) {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        }
        lastClickTime = currentTime;
    });

    function generateFingerprint() {
        const canvas = document.createElement('canvas');
        const canvasHash = canvas.toDataURL();

        const userAgent = navigator.userAgent;
        const screenResolution = screen.width + 'x' + screen.height;
        const timezone = new Date().getTimezoneOffset();
        const languages = navigator.languages;
        return userAgent + screenResolution + timezone + languages + canvasHash;
    }
});