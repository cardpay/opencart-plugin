function extracted(data, elementId) {
    const div_main = document.getElementById(elementId);
    div_main.innerHTML = '';
    const div_error = document.createElement('div');
    div_error.setAttribute('class', "alert alert-danger");
    div_error.setAttribute('id', "div_error");
    const btn_dismiss = document.createElement('button');
    btn_dismiss.setAttribute('class', "close");
    btn_dismiss.setAttribute('id', "btn_dismiss");
    btn_dismiss.innerHTML = "x";

    btn_dismiss.onclick = function () {
        div_main.removeChild(document.getElementById('div_error'));
    };

    const response_payment = typeof (data) == "string" ? JSON.parse(data) : data;
    let status = "";

    if (response_payment.status === 400) {
        status = response_payment.message.split('-')[1].split(':')[0].trim();
        response_payment.request_type = "status";
    } else {
        status = response_payment.status;
    }

    const url_site = window.location.href.split('index.php')[0];
    let url_message = url_site.slice(-1) === '/' ? url_site : url_site + '/';
    url_message += 'index.php?route=extension/payment/ul_general/getPaymentStatus&status=' + status;
    if (response_payment.request_type) {
        url_message += '&request_type=' + response_payment.request_type;
    }

    return {div_error, btn_dismiss, url_message};
}

function finalAmount(amount) {
    const string_amount = amount.toString();
    const splitted_amount = string_amount.split("");
    const comma = amount.indexOf(',');
    const dot = amount.indexOf('.');

    if (comma < dot) {
        splitted_amount[comma] = "";
    } else {
        splitted_amount[comma] = ".";
        splitted_amount[dot] = "";
    }

    const final_amount = splitted_amount.join("");
    return Number(final_amount);
}