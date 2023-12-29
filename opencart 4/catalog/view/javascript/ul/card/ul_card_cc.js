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
    Unlimit.createToken($form, customersAndCardsPay);
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
        Unlimit.clearSession();
    }
    tries += 1;
    localStorage.setItem('payment', tries);
    const spinner = new Spinner().spin(document.getElementById('spinner'));
    const lbls = document.getElementsByClassName('text-right');
    let urlSite = window.location.href.split('index.php')[0];
    let urlBackend = urlSite.slice(-1) === '/' ? urlSite : urlSite + '/';
    urlBackend += 'index.php?route=extension/payment/ul_card/paymentCustomersAndCards/';
    const pageAmount = lbls[lbls.length - 1].textContent.split('$')[1];
    const payment = {
        token: response.id,
        transaction_amount: buildAmount(pageAmount),
        installments: document.getElementById('installments_cc').value
    };

    $.ajax({
        type: "POST",
        url: urlBackend,
        data: payment,
        success: function success(data) {
            const responsePayment = JSON.parse(data);
            const acceptableStatus = ["approved", "in_process"];
            if (acceptableStatus.indexOf(responsePayment.status) > -1) {
                console.info("====ModuleAnalytics enviar=====");
                ModuleAnalytics.setToken(responsePayment.token);
                ModuleAnalytics.setPaymentId(responsePayment.paymentId);
                ModuleAnalytics.setPaymentType(responsePayment.paymentType);
                ModuleAnalytics.setCheckoutType(responsePayment.checkoutType);
                console.info("====ModuleAnalytics=====");
                ModuleAnalytics.put();

                urlSite = window.location.href.split('index.php')[0];
                let location = urlSite.slice(-1) === '/' ? urlSite : urlSite + '/';
                location += 'index.php?route=checkout/success';
                localStorage.removeItem('payment');
                window.location.href = location;
            } else {
                delete responsePayment.request_type;
                getMessage(responsePayment);
            }
            spinner.stop();
        }
    });
}

function customersAndCardsGetInstallments() {
    const publicKey = document.getElementById("public_key").value;
    const ccNum = document.getElementById('cc_num_cc');
    const bin = ccNum.options[ccNum.selectedIndex].getAttribute('first_six_digits');
    const lbls = document.getElementsByClassName('text-right');
    const stringAmount = lbls[lbls.length - 1].textContent.split('$')[1];
    const amount = buildAmount(stringAmount);
    const config = {"bin": bin, "amount": amount};

    Unlimit.setPublishableKey(publicKey);

    Unlimit.getInstallments(config, function (httpStatus, data) {
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
