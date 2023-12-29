function extracted(data, elementId) {
    const divMain = document.getElementById(elementId);
    divMain.innerHTML = '';
    const divError = document.createElement('div');
    divError.setAttribute('class', "alert alert-danger");
    divError.setAttribute('id', "div_error");
    const btnDismiss = document.createElement('button');
    btnDismiss.setAttribute('class', "close");
    btnDismiss.setAttribute('id', "btn_dismiss");
    btnDismiss.innerHTML = "x";

    btnDismiss.onclick = function () {
        divMain.removeChild(document.getElementById('div_error'));
    };

    const responsePayment = typeof (data) == "string" ? JSON.parse(data) : data;
    let status;

    if (responsePayment.status === 400) {
        status = responsePayment.message.split('-')[1].split(':')[0].trim();
        responsePayment.request_type = "status";
    } else {
        status = responsePayment.status;
    }

    const urlSite = window.location.href.split('index.php')[0];
    let urlMessage = urlSite.slice(-1) === '/' ? urlSite : urlSite + '/';
    urlMessage += 'index.php?route=extension/payment/ul_general/getPaymentStatus&status=' + status;
    if (responsePayment.request_type) {
        urlMessage += '&request_type=' + responsePayment.request_type;
    }

    return {divError, btnDismiss, url_message: urlMessage};
}

function finalAmount(amount) {
    const stringAmount = amount.toString();
    const splittedAmount = stringAmount.split("");
    const comma = amount.indexOf(',');
    const dot = amount.indexOf('.');

    if (comma < dot) {
        splittedAmount[comma] = "";
    } else {
        splittedAmount[comma] = ".";
        splittedAmount[dot] = "";
    }

    const final_amount = splittedAmount.join("");
    return Number(final_amount);
}
