window.addEventListener('load', function () {
  if (localStorage.getItem('activeTab')) {
    localStorage.removeItem('activeTab')
  }

  $('#form_ul').submit(function (e) {
    const prefix = 'payment_ul_card_'
    if (!$(`#${prefix}terminal_code`).length) {
      return
    }

    const isValidTerminalCode = validateUlAdminField(
      prefix + 'terminal_code', 128, 'terminal code', true)
    const isValidTerminalPassword = validateUlAdminField(
      prefix + 'terminal_password', 128, 'terminal password', false)
    const isValidCallbackSecret = validateUlAdminField(
      prefix + 'callback_secret', 128, 'callback secret', false)
    const isValidPaymentTitle = validateUlAdminField(
      prefix + 'payment_title', 128, 'payment title', false)
    const isValidDynamicDescriptor = validateUlAdminField(
      prefix + 'dynamic_descriptor', 22, 'dynamic descriptor', false)
    if (!isValidTerminalCode || !isValidTerminalPassword ||
        !isValidCallbackSecret || !isValidPaymentTitle ||
        !isValidDynamicDescriptor) {
      e.preventDefault(e)
    }
  })
});

(
  function () {
    buttonSave()
  }
)()

function saveConfigs () {
  $('#form_ul').submit()
}

const unlimitSettingsEvent = {
  prefix: 'payment_ul_card_',
  selPaymentPage: null,
  selInstType: null,
  selInstEnabled: null,
  askCpf: null,
  maximumAcceptedInstallments: null,
  installmentsLimits: [],
  installmentsLimitsIF: [1, 3, 6, 9, 12, 18],
  installmentsLimitsMfHold: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
  installmentSettingsFields: [
    'minimum_installment_amount',
    'maximum_accepted_installments',
    'installment_type',
  ],
  init: function () {
    this.selPaymentPage = jQuery(`#${this.prefix}payment_page`)
    this.selInstType = jQuery(`#${this.prefix}installment_type`)
    this.selInstAmount = jQuery(`#${this.prefix}minimum_installment_amount`)
    this.selInstEnabled = jQuery(`#${this.prefix}installment_enabled`)
    this.maximumAcceptedInstallments = jQuery(`#${this.prefix}maximum_accepted_installments`)
    this.askCpf = jQuery(`#${this.prefix}ask_cpf`)
    this.installmentSettingsFields = [
      'minimum_installment_amount',
      'maximum_accepted_installments',
      'installment_type',
    ]
    this.checkAccessMode()
    this.toggleSettings()
    this.selectPpAndInstType()
    this.processInstallmentSettings()
    this.setupListeners()
    this.twoPrefix()
  },

  checkAccessMode: function () {
    const obj = this
    const select = `#${obj.prefix}installment_type`
    const payment_page = '1'

    if (jQuery(obj.selPaymentPage).val() === payment_page) {
      jQuery(`${select} option[value="MF_HOLD"]`).remove()
    }
  },

  setupListeners: function () {
    const obj = this

    jQuery(obj.selPaymentPage).change(function () {
      obj.toggleSettings()

      const select = `#${obj.prefix}installment_type`
      const gateway = '0'
      const payment_page = '1'

      if (jQuery(obj.selPaymentPage).val() === gateway) {
        jQuery(obj.selInstType).append('<option value="MF_HOLD">' +
                                       'Merchant financed' +
                                       '</option>')
      } else {
        jQuery(`${select} option[value="MF_HOLD"]`).remove()
      }

      if (jQuery(obj.selPaymentPage).val() === payment_page) {
        obj.selectPpAndInstType()
      }
    })

    jQuery(obj.selInstType).change(function () {
      obj.selectPpAndInstType()
    })

    jQuery(obj.selInstAmount).change(function () {
      obj.twoPrefix()
    })

    jQuery(obj.selInstEnabled).change(function () {
      obj.processInstallmentSettings()
    })

    jQuery(obj.maximumAcceptedInstallments).
      on('change keyup', function (e) {
        obj.checkMaximumAcceptedInstallments(false, (
          e.type === 'change'
        ))
      })
  },
  twoPrefix: function () {
    jQuery(`#${this.prefix}minimum_installment_amount`).change(function () {
      const elementByIdToFixed = document.getElementById(
        'payment_ul_card_minimum_installment_amount')
      const parsed = elementByIdToFixed.value.split('.')
      let result = elementByIdToFixed.value // Initialize the result variable

      if (parsed[0] && parsed[1] && parsed[1].length > 4) {
        const subString = parsed[1].substr(0, 4)
        result = parsed[0] + '.' + subString // Assign the updated value to the result variable
      }

      elementByIdToFixed.value = result
    })
  },

  processInstallmentSettings: function () {
    const obj = this
    const installments_enabled = '1'
    const show = (
      jQuery(obj.selInstEnabled).val() === installments_enabled
    )
    jQuery(obj.installmentSettingsFields).each(function () {
      const el = jQuery(`#${obj.prefix}${this}`).parent().parent()
      if (show) {
        el.show('slow')
      } else {
        el.hide()
      }
    })
  },
  toggleSettings: function () {
    const cpfEl = jQuery(this.askCpf).parent().parent()
    if (jQuery(this.selPaymentPage).val() !== '0') {
      cpfEl.hide()
    } else {
      cpfEl.show()
    }
  },
  normalizeIntVal: function (val) {
    if (val.length === 0) {
      return val
    }

    val = val.replace(/[^\d](\d*)/g, '')

    return parseInt(val)
  },
  validateInstallmentRange: function (value) {
    const parsed = value.split('-')
    if (parsed.length !== 2) {
      return false
    }

    parsed[0] = this.normalizeIntVal(parsed[0])
    parsed[1] = this.normalizeIntVal(parsed[1])
    let error = (
      this.installmentsLimits.indexOf(parsed[0]) === -1 ||
      this.installmentsLimits.indexOf(parsed[1]) === -1 ||
      parsed[0] >= parsed[1]
    )
    for (let i = parsed[0]; i <= parsed[1]; i++) {
      error = error || (
        this.installmentsLimits.indexOf(i) === -1
      )
    }

    return !error
  },
  fixInstallmentSettings: function (value, defaults) {
    const obj = this
    if (value.substr(value.length - 1) === ',') {
      value = value.substring(0, value.length - 1)
    }
    const values = value.split(',')

    if (values.length === 0 || (
      values.length === 1 && values[0] === ''
    )) {
      return defaults
    }

    const newValues = []
    jQuery.each(values, function () {
      if (this.indexOf('-') > -1) {
        const vals = this.split('-')
        vals[0] = obj.normalizeIntVal(vals[0])
        if (vals.length > 1) {
          vals[1] = obj.normalizeIntVal(vals[1])
          newValues.push(vals.join('-'))
        } else {
          newValues.push(vals[0] + '-')
        }
      } else {
        newValues.push(obj.normalizeIntVal(this))
      }
    })

    return newValues.join(',')
  },
  checkMaximumAcceptedInstallments: function (displayError, fix) {
    const obj = this
    if (!jQuery(obj.maximumAcceptedInstallments).is(':visible')) {
      return true
    }

    let error = false
    let value = jQuery(obj.maximumAcceptedInstallments).val()

    const defaults = (
      jQuery(this.selInstType).val() === 'IF'
    ) ? '3,6,9,12,18' : '2-12'
    const newValue = this.fixInstallmentSettings(value, defaults)
    if (fix === true && newValue !== value) {
      window.setTimeout(function () {
        jQuery(obj.maximumAcceptedInstallments).val(value)
      }, 1)
      value = newValue
    }

    if (value.search(/[^\d-,]/) !== -1) {
      error = true
    }

    if (!Number.isNaN(value)) {
      value = value.replace(/NaN/, '')
    }

    const values = value.split(',')
    jQuery.each(values, function () {
      if (this.indexOf('-') > -1) {
        error = error || !(
          obj.validateInstallmentRange(this)
        )
      } else {
        error = error || (
          obj.installmentsLimits.indexOf(
            obj.normalizeIntVal(this)) === -1
        )
      }
    })

    if (error) {
      if (displayError === true) {
        this.showUlAdminError('maximum_accepted_installments',
          'Allowed installments range')
      }
      highlightUlAdminError(
        jQuery(obj.maximumAcceptedInstallments).attr('id'))
    }

    if (!error) {
      hideUlAdminError(
        jQuery(obj.maximumAcceptedInstallments).attr('id'))
    }

    return !error
  },
  selectPpAndInstType: function () {
    switch (jQuery(this.selInstType).val()) {
      case 'IF': {
        this.installmentsLimits = this.installmentsLimitsIF
        jQuery(this.maximumAcceptedInstallments).
          attr('placeholder', '3, 6, 9, 12, 18')
        break
      }
      case 'MF_HOLD': {
        this.installmentsLimits = this.installmentsLimitsMfHold
        jQuery(this.maximumAcceptedInstallments).
          attr('placeholder', '1, 2, 3-5, 7-12')
        break
      }
      default: {
        //
      }
    }
    this.checkMaximumAcceptedInstallments()
  },
  showUlAdminError: function (errorMessageId, errorMessage) {
    jQuery(
      `<div class='error inline' id='${errorMessageId}'><p>${errorMessage}</p></div>`).
      insertBefore('#form-table')
  },
}

function highlightUlAdminError (id) {
  jQuery(`#${id}`).parent().parent().addClass('ul_error')
}

function hideUlAdminError (id) {
  jQuery(`#${id}`).parent().parent().removeClass('ul_error')
}

window.addEventListener('load', function () {
  const targetPhrase = 'ul_card'
  const currentHref = window.location.search

  if (currentHref.includes(targetPhrase)) {
    unlimitSettingsEvent.init()
  }
})


