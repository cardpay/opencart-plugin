function formatUlCpf(cpf) {
    return cpf.replace(/\D/g, '')
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d)/, "$1.$2")
        .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
}

function isUlCpfValid(cpf) {
    if (!cpf) {
        return false;
    }

    cpf = cpf.replace(/[\s.-]*/igm, '');
    if (
        !cpf ||
        cpf.length !== 11 ||
        cpf === '00000000000' ||
        cpf === '11111111111' ||
        cpf === '22222222222' ||
        cpf === '33333333333' ||
        cpf === '44444444444' ||
        cpf === '55555555555' ||
        cpf === '66666666666' ||
        cpf === '77777777777' ||
        cpf === '88888888888' ||
        cpf === '99999999999'
    ) {
        return false;
    }

    let sum = 0;
    let remainder;
    for (let i = 1; i <= 9; i++) {
        sum = sum + parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }

    remainder = parseInt(sum * 10) % 11;
    if ((remainder === 10) || (remainder === 11)) {
        remainder = 0;
    }

    if (remainder !== parseInt(cpf.substring(9, 10))) {
        return false;
    }

    sum = 0;
    for (let j = 1; j <= 10; j++) {
        sum = sum + parseInt(cpf.substring(j - 1, j)) * (12 - j);
    }

    remainder = parseInt((sum * 10) % 11);
    if ((remainder === 10) || (remainder === 11)) {
        remainder = 0;
    }

    return remainder === parseInt(cpf.substring(10, 11));
}
