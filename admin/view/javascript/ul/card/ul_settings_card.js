window.addEventListener('load', function () {
    if (localStorage.getItem('activeTab')) {
        localStorage.removeItem('activeTab');
    }

    $('#form_ul').submit(function (e) {
        const prefix = 'payment_ul_card_';
        if (!$(`#${prefix}terminal_code`).length) {
            return;
        }

        const isValidTerminalCode = validateUlAdminField(prefix + 'terminal_code', 128, 'terminal code', true);
        const isValidTerminalPassword = validateUlAdminField(prefix + 'terminal_password', 128, 'terminal password', false);
        const isValidCallbackSecret = validateUlAdminField(prefix + 'callback_secret', 128, 'callback secret', false);
        const isValidPaymentTitle = validateUlAdminField(prefix + 'payment_title', 128, 'payment title', false);
        const isValidDynamicDescriptor = validateUlAdminField(prefix + 'dynamic_descriptor', 22, 'dynamic descriptor', false);
        if (!isValidTerminalCode || !isValidTerminalPassword || !isValidCallbackSecret || !isValidPaymentTitle || !isValidDynamicDescriptor) {
            e.preventDefault(e);
        }
    });
});

(function () {
    buttonSave();
})();

function saveConfigs() {
    $('#form_ul').submit();
}