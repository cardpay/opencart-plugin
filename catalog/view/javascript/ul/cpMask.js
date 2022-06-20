function ulProcessValue(o,f)
{
    const v_obj = o
    const value = f(v_obj.value)
    setTimeout(function(){
        v_obj.value = value;
    })

}
//Card mask date input
function ulCreditMask(o, f) {
    ulProcessValue(o,f);
    displayCardBrand();
}

//Card mask date input
function ulCheckField(o) {
    ul_check_input($(o).data('checkout'), $(o).val())
}

//Card mask date input
function ulCpfMask(o, f) {
    ulProcessValue(o,f);
}

function ulExpDate(value) {
    value = value.replace(/\D/g, "");
    value = value.replace(/^(\d{2})(\d)/g, "$1/$2");
    return value;
}
function ulMcc(value) {
    value = value.replace(/\D/g, "");
    value = value.replace(/^(\d{4})(\d)/g, "$1 $2");
    value = value.replace(/^(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3");
    value = value.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4");
    value = value.replace(/^(\d{4})\s(\d{4})\s(\d{4})\s(\d{4})(\d)/g, "$1 $2 $3 $4 $5");
    return value;
}

function ulDocNumber(value) {
    value = value.replace(/\D/g, "");
    value = value.replace(/^(\d{3})(\d)/g, "$1.$2");
    value = value.replace(/^(\d{3})\.(\d{3})(\d)/g, "$1.$2.$3");
    value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/g, "$1.$2.$3-$4");
    return value;
}

function ulDate(v) {
    v = v.replace(/\D/g, "");
    v = v.replace(/(\d{2})(\d)/, "$1/$2");
    v = v.replace(/(\d{2})(\d{2})$/, "$1$2");
    return v;
}

// Explode date to month and year
function ulValidateMonthYear() {
    const date = document.getElementById('ul-card-expiration-date').value.split('/');
    document.getElementById('cardExpirationMonth').value = date[0];
    document.getElementById('cardExpirationYear').value = date[1];
}

function ulInteger(v) {
    return v.replace(/\D/g, "");
}

const displayCardBrand = function () {
    const CARD_BRANDS = [
        {
            cbType: "visa",
            pattern: /^4/,
            cnLength: [13, 14, 15, 16, 19],
        },
        {
            cbType: "mir",
            pattern: /^220[0-4]\d+/,
            cnLength: [16, 17, 18, 19],
        },
        {
            cbType: "discover",
            pattern: /^(60110\d|6011[2-4]\d|601174|60117[7-9]|6011[8-9][4-9]|644\d\d\d|65\d\d\d\d|64[4-9]\d+|369989)/,
            cnLength: [16, 17, 18, 19],
        },
        {
            cbType: "dinersclub",
            pattern: /^(30[0-5]\d\d\d|3095\d\d|3[8-9]\d\d\d\d)/,
            cnLength: [16, 17, 18, 19],
        }, {
            cbType: "dinersclub",
            pattern: /^(36\d\d\d\d)/,
            cnLength: [14, 15, 16, 17, 18, 19],
        },
        {
            cbType: "amex",
            pattern: /^3[47]/,
            cnLength: [15],
        },
        {
            cbType: "jcb",
            pattern: /^(((352[8-9][0-9][0-9])|(35[3-8][0-9][0-9][0-9]))|((30[8-9][8-9][0-9][0-9])|309[0-4][0-9][0-9])|((309[6-9][0-9][0-9])|310[0-2][0-9][0-9])|(311[2-9][0-9][0-9])|(3120[0-9][0-9])|(315[8-9][0-9][0-9])|((333[7-9][0-9][0-9])|(334[0-9][0-9][0-9])))/,  // NOSONAR
            cnLength: [16, 17, 18, 19],
        },
        {
            cbType: "unionpay",
            pattern: /^(62|9558|81)/,
            cnLength: [13, 14, 15, 16, 17, 18, 19],
        },
        {
            cbType: "elo",
            pattern: /^(50(67(0[78]|1[5789]|2[012456789]|3[01234569]|4[0-7]|53|7[4-8])|9(0(0[0123478]|14|2[0-2]|3[359]|4[01235678]|5[1-9]|6[0-9]|7[0134789]|8[04789]|9[12349])|1(0[34568]|4[6-9]|83)|2(20|5[7-9]|6[0-6])|4(0[7-9]|1[0-2]|31)|7(22|6[5-9])))|4(0117[89]|3(1274|8935)|5(1416|7(393|63[12])))|6(27780|36368|5(0(0(3[12356789]|4[0-9]|5[01789]|6[01345678]|7[78])|4(0[6-9]|1[0-3]|2[2-6]|3[4-9]|8[5-9]|9[0-9])|5(0[012346789]|1[0-9]|2[0-9]|3[0-8]|7[7-9]|8[0-9]|9[0-8])|72[0-7]|9(0[1-9]|1[0-9]|2[0128]|3[89]|4[6-9]|5[045]|6[25678]|71))|16(5[2-9]|6[0-9]|7[01456789])|50(0[0-9]|1[0-9]|2[1-9]|3[0-6]|5[1-7]))))/, // NOSONAR
            cnLength: [13, 16, 19],
        },
        {
            cbType: "mastercard",
            pattern: /^5[1-5]|^2(?:2(?:2[1-9]|[3-9]\d)|[3-6]\d\d|7(?:[01]\d|20))/,
            cnLength: [16],
        },
        {
            cbType: "maestro",
            pattern: /^(0604|50|5[6789]|60|61|63|64|67|6660|6670|6818|6858|6890|6901|6907)/,
            cnLength: [12, 13, 14, 15, 16, 17, 18, 19],
        },
    ];

    const cardBrandSpan = $('#card-brand');
    cardBrandSpan.removeAttr('class');

    const cardNumberInputField = $('#cardNumber');
    if (cardNumberInputField === null || typeof cardNumberInputField === 'undefined') {
        return true;
    }

    const cardNumber = cardNumberInputField.val().replace(/[^\d]/gi, '');

    let isCardNumberValid = true;
    for (let cardBrandIndex = 0; cardBrandIndex <= CARD_BRANDS.length - 1; cardBrandIndex++) {
        const cardBrand = CARD_BRANDS[cardBrandIndex];
        if (cardBrand.pattern.test(cardNumber)) {
            if (!cardBrand.cnLength.includes(cardNumber.length) || !isUlLuhnAlgorithmPassed(cardNumber)) {
                isCardNumberValid = false;
            }

            cardBrandSpan.addClass('card-brand-' + cardBrand.cbType);
            break;
        }
    }
    // unknown card brand
    if (cardNumber.length < 13 || cardNumber.length > 19 || !isUlLuhnAlgorithmPassed(cardNumber)) {
        isCardNumberValid = false;
    }

    return isCardNumberValid;
}

const isUlLuhnAlgorithmPassed = function (cardNumber) {
    if (!cardNumber) {
        return false;
    }

    const cardNumberWithoutSpaces = (cardNumber + '').replace(/\s/g, '');
    let digit, odd, sum, _i, _len;
    odd = true;
    sum = 0;
    const digits = cardNumberWithoutSpaces.split('').reverse();

    for (_i = 0, _len = digits.length; _i < _len; _i++) {
        digit = digits[_i];
        digit = parseInt(digit, 10);
        odd = !odd
        if (odd) {
            digit *= 2;
        }
        if (digit > 9) {
            digit -= 9;
        }
        sum += digit;
    }

    return (sum % 10 === 0);
}