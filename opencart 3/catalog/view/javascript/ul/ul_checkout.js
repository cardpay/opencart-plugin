function formatUlBoletoCpf(cpfFieldId) {
    const cpfField = jQuery(`#${cpfFieldId}`);
    if (!cpfField.length) {
        return cpfField.val();
    }

    const cpfFormatted = formatUlCpf(cpfField.val());
    cpfField.val(cpfFormatted);
    return cpfFormatted;
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
    const valueDocNumber = document.getElementById('docnumber').value;
    const cpfErrorPostCode = jQuery(`#cpf-error-1`);
    cpfErrorPostCode.hide();

    if (valueDocNumber === '') {
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

function validatePostCodeInput() {
    const postCodeObject = document.getElementById('input_payment_postcode');
    if (postCodeObject) {
        const postCode = postCodeObject.value;
        const cpfField = jQuery('#input_payment_postcode');
        cpfField.removeClass(ulFormControlError);

        const cpfError = jQuery('#post-code');
        cpfError.hide();

        if (postCode === '' || postCode.length !== 8) {
            cpfField.addClass(ulFormControlError);
            cpfError.focus();
            cpfError.show();
            return false;
        }

        return true;
    }

    return true;
}
