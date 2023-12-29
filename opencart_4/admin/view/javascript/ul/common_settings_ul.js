const validateUlAdminField = function (fieldName, maxLength, errorField, positiveInteger) {
    const errorMessageId = fieldName + '-error';
    const errorMessageField = $(`[id=${errorMessageId}]`);
    if (errorMessageField) {
        errorMessageField.remove();
    }

    const adminField = $(`#${fieldName}`);
    if (adminField) {
        const fieldValue = adminField.val();
        if (!fieldValue || fieldValue.trim().length === 0) {
            showUlAdminError(errorMessageId, `Empty ${errorField}`);
            return false;
        }

        if (fieldValue.length > maxLength || (positiveInteger && (isNaN(fieldValue) || parseInt(fieldValue) < 0))) {
            showUlAdminError(errorMessageId, `Invalid ${errorField}`);
            return false;
        }
    }

    return true;
}

const showUlAdminError = function (errorMessageId, errorMessage) {
    $(`<div class='form-group alert alert-danger alert-dismissible' id='${errorMessageId}'><label class="col-sm-2 control-label">${errorMessage}</label></div>`).insertBefore('#form-table');
}

const validateAltMethodForm = function (prefix, e) {
    const isValidTerminalCode = validateUlAdminField(prefix + 'terminal_code', 128, 'terminal code', true);
    const isValidTerminalPassword = validateUlAdminField(prefix + 'terminal_password', 128, 'terminal password', false);
    const isValidCallbackSecret = validateUlAdminField(prefix + 'callback_secret', 128, 'callback secret', false);
    const isValidPaymentTitle = validateUlAdminField(prefix + 'payment_title', 128, 'payment title', false);

    if (!isValidTerminalCode || !isValidTerminalPassword || !isValidCallbackSecret || !isValidPaymentTitle) {
        e.preventDefault(e);
    }
}

function buttonSave() {
    const btnSave = document.getElementById('btn_save');

    $('a[data-toggle="tab"]').on('click', function (e) {
        e.preventDefault();
        $('#form-table .tab-pane').hide();
        $('#tabs .nav-link').each(function(){$(this).removeClass('active');})
        $($(e.target).attr('href')).show();
        $(e.target).addClass('active');
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });

    btnSave.onclick = saveConfigs;

    const activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $('#form-table .tab-pane').hide();
        $('#tabs .nav-link').each(function(){$(this).removeClass('active');})
        $('#tabs a[href="' + activeTab + '"]').addClass('active');
        $(activeTab).show();
    }
}

$(document).on('change', '.ul_access_mode', function (){
    $('#alert').prepend('<div id="api_mode_changed_warning" class="alert alert-warning alert-dismissible"><i class="fa-solid fa-circle-exclamation"></i> '+ api_mode_changed_warning +'<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
    $('#api_mode_changed_warning .btn-close').click(function () {$('#api_mode_changed_warning').fadeOut()});
    setTimeout(function (){
        $('#api_mode_changed_warning').fadeOut()
    }, 20000);
});