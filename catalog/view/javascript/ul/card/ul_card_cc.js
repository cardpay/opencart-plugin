(function () {
    $("#cardData").hide('slow');
    document.getElementById('cc_num_cc').addEventListener('change', customersAndCardsSelectHandler);
    document.getElementById('button_pay_cc').addEventListener('click', doPay);
    setTimeout(function () {
        customersAndCardsGetInstallments();
    }, 2500);
})();

function doPay(event) {
    event.preventDefault();
    const $form = document.querySelector('#divCustomersAndCards');
    console.log('form');
    console.log($form);
    Unlimint.createToken($form, customersAndCardsPay);
    return false;
}

function buildAmount(amount) {
    return finalAmount(amount);
}


function cardsHandler() {
    const card = document.querySelector('select[data-checkout="cardId"]');
    if (card[card.options.selectedIndex].getAttribute('security_code_length') === 0) {
        document.querySelector("#cvv_cc").style.display = "none";
    } else if (document.querySelector("#cvv_cc").style.display !== "block") {
        document.querySelector("#cvv_cc").style.display = "block";
    }
}


function customersAndCardsPay(status, response) {
    let tries = localStorage.getItem('payment') ? parseInt(localStorage.getItem('payment')) : 0;
    if (tries) {
        Unlimint.clearSession();
    }
    tries += 1;
    localStorage.setItem('payment', tries);
    const spinner = new Spinner().spin(document.getElementById('spinner'));
    const lbls = document.getElementsByClassName('text-right');
    let url_site = window.location.href.split('index.php')[0];
    let url_backend = url_site.slice(-1) === '/' ? url_site : url_site + '/';
    url_backend += 'index.php?route=extension/payment/ul_card/paymentCustomersAndCards/';
    const page_amount = lbls[lbls.length - 1].textContent.split('$')[1];
    const payment = {
        token: response.id,
        transaction_amount: buildAmount(page_amount),
        installments: document.getElementById('installments_cc').value
    };

    $.ajax({
        type: "POST",
        url: url_backend,
        data: payment,
        success: function success(data) {
            const response_payment = JSON.parse(data);
            const acceptable_status = ["approved", "in_process"];
            if (acceptable_status.indexOf(response_payment.status) > -1) {
                console.info("====ModuleAnalytics enviar=====");
                ModuleAnalytics.setToken(response_payment.token);
                ModuleAnalytics.setPaymentId(response_payment.paymentId);
                ModuleAnalytics.setPaymentType(response_payment.paymentType);
                ModuleAnalytics.setCheckoutType(response_payment.checkoutType);
                console.info("====ModuleAnalytics=====");
                ModuleAnalytics.put();

                url_site = window.location.href.split('index.php')[0];
                let location = url_site.slice(-1) === '/' ? url_site : url_site + '/';
                location += 'index.php?route=checkout/success';
                localStorage.removeItem('payment');
                window.location.href = location;
            } else {
                delete response_payment.request_type;
                getMessage(response_payment);
            }
            spinner.stop();
        }
    });
}

function customersAndCardsGetInstallments() {
    const public_key = document.getElementById("public_key").value;
    const cc_num = document.getElementById('cc_num_cc');
    const bin = cc_num.options[cc_num.selectedIndex].getAttribute('first_six_digits');
    const lbls = document.getElementsByClassName('text-right');
    const string_amount = lbls[lbls.length - 1].textContent.split('$')[1];
    const amount = buildAmount(string_amount);
    const config = {"bin": bin, "amount": amount};

    Unlimint.setPublishableKey(public_key);

    Unlimint.getInstallments(config, function (httpStatus, data) {
        if (httpStatus === 200) {
            const installments = data[0].payer_costs;
            let i;
            const select = document.getElementById('installments_cc');
            select.options.length = 0;
            select.appendChild(new Option('Selecione'));
            for (i = 0; i < installments.length; i++) {
                const opt = document.createElement('option');
                opt.appendChild(document.createTextNode(installments[i].recommended_message));
                opt.value = installments[i].installments;
                select.appendChild(opt);
            }
        }
    });
}

function customersAndCardsSelectHandler() {
    console.log('this.value = ' + this.value);
    if (this.value === "-1") {
        $("#cc_inputs").hide('slow');
        $("#cardData").show('slow');
    } else {
        $("#cardData").hide('slow');
        $("#cc_inputs").show('slow');
        customersAndCardsGetInstallments();
    }
}