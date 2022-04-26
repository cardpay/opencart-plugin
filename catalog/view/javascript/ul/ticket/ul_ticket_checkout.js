(function ($) {
    'use strict';

    $(function () {
        $('form.checkout').on('checkout_place_order_woo-unlimint-ticket', function () {
            return validateUlBoletoCpf();
        });
    });
}(jQuery));

function formatUlBoletoCpf(cpfFieldId) {
    const cpfField = jQuery(`#${cpfFieldId}`);
    if (!cpfField.length) {
        return cpfField.val();
    }

    const cpfFormatted = formatUlCpf(cpfField.val());
    cpfField.val(cpfFormatted);
    return cpfFormatted;
}

function validateUlBoletoCpf() {
    return validateUlCpf('docnumber', 'boleto-cpf-error');
}

const ulFormControlError = 'ul-form-control-error';

function validateUlCpf(cpfFieldId, errorField) {
    const cpfField = jQuery(`#${cpfFieldId}`);
    cpfField.removeClass(ulFormControlError);

    const cpfError = jQuery(`#${errorField}`);
    cpfError.hide();

    const cpfFormatted = formatUlBoletoCpf(cpfFieldId);
    const isCpfValid = isUlCpfValid(cpfFormatted);
    const validPostCode = validatePostCodeInput();
    const f = document.getElementById('docnumber').value;
    const cpfErrorPostCode = jQuery(`#boleto-cpf-error-1`);
    cpfErrorPostCode.hide();
    if (f === '') {
        cpfField.addClass(ulFormControlError);
        cpfErrorPostCode.focus();
        cpfErrorPostCode.show();
        return isCpfValid && validPostCode;
    }

    if (!isCpfValid) {
        cpfField.addClass(ulFormControlError);
        cpfError.focus();
        cpfError.show();
    }

    return isCpfValid && validPostCode;
}