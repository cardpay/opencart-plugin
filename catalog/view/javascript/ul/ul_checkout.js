function formatUlBoletoCpf (cpfFieldId) {
  const cpfField = $(`#${cpfFieldId}`)
  if (!cpfField.length) {
    return cpfField.val()
  }

  const cpfFormatted = formatUlCpf(cpfField.val())
  cpfField.val(cpfFormatted)
  return cpfFormatted
}

function validatePhone (prefix) {
  var phoneField = document.getElementById('ul' + prefix + 'Number')
  phoneField.value = phoneField.value.replace(/[^+\d]/g, '')

  var numbersOnly = phoneField.value.trim()
  var phonePattern = /^\+?\d{8,18}$/
  if (prefix === 'Mbway') {
    phonePattern = /^\+?351\d{9}$|^(?!.*[a-zA-Z])\d{9}$/
  }

  var errorElement = document.getElementById('ul' + prefix + 'Error')
  var errorElementSecond = document.getElementById(
    'ul' + prefix + 'Error-second')

  if (numbersOnly === '') {
    errorElement.style.display = 'inline-block'
    errorElementSecond.style.display = 'none'
    return false
  }

  if (!numbersOnly.match(phonePattern)) {
    errorElement.style.display = 'none'
    errorElementSecond.style.display = 'inline-block'
    return false
  }

  errorElement.style.display = 'none'
  errorElementSecond.style.display = 'none'
  return true
}

var ulFormControlError = 'ul-form-control-error' //NOSONAR

function validateUlCpf (cpfFieldId, errorField) {
  const cpfField = $(`#${cpfFieldId}`)
  cpfField.removeClass(ulFormControlError)

  const cpfError = $(`#${errorField}`)
  cpfError.hide()

  const cpfFormatted = formatUlBoletoCpf(cpfFieldId)
  const isCpfValid = isUlCpfValid(cpfFormatted)
  const validPostCode = validatePostCodeInput()
  const valueDocNumber = document.getElementById('docnumber').value
  const cpfErrorPostCode = $(`#cpf-error-1`)
  cpfErrorPostCode.hide()

  if (valueDocNumber === '') {
    cpfField.addClass(ulFormControlError)
    cpfErrorPostCode.focus()
    cpfErrorPostCode.show()
    return isCpfValid && validPostCode
  }

  if (!isCpfValid) {
    cpfField.addClass(ulFormControlError)
    cpfError.focus()
    cpfError.show()
  }

  return isCpfValid && validPostCode
}

function validatePostCodeInput () {
  const postCodeObject = document.getElementById('input_payment_postcode')
  if (postCodeObject) {
    const postCode = postCodeObject.value
    const cpfField = $('#input_payment_postcode')
    cpfField.removeClass(ulFormControlError)

    const cpfError = $('#post-code')
    cpfError.hide()

    if (postCode === '' || postCode.length !== 8) {
      cpfField.addClass(ulFormControlError)
      cpfError.focus()
      cpfError.show()
      return false
    }

    return true
  }

  return true
}

var unlimitFormCheckout
$(document).ready(function () {
  unlimitFormCheckout = $('#ul-form')
})

var unlimitIframeProcessor = {//NOSONAR
  oldEvents: false,
  maxIframeWidth: 1000,
  iframePadding: 80,
  spinner: '#checkout-payment-method, #checkout-confirm',
  afterSubmit: function () {
    unlimitFormCheckout.removeClass('processing')
  },
  redirectFunc: function (url) {
    var modalSelector = $('#modal-unlimit')
    modalSelector.remove()

    let html = '<div id="modal-unlimit" class="modal">'
    html += '  <div class="modal-dialog modal-dialog-centered">'
    html += '    <div class="modal-content">'
    html += '      <div class="modal-body">'
    html += '      <iframe src="' + url + '" />'
    html += '      </div>'
    html += '    </div>'
    html += '  </div>'
    html += '</div>'

    $('body').append(html)

    $('#modal-unlimit').modal('show')

    this.setModalSize()
  },
  onSuccessSubmit: function (e) {
    unlimitIframeProcessor.afterSubmit()
    unlimitFormCheckout.removeClass('processing')
    try {
      if ('success' !== e.result) {
        if ('failure' === e.result) {
          throw new Error('Result failure')
        } else {
          throw new Error('Invalid response')
        }
      }
      -1 === e.redirect.indexOf('https://') || -1 ===
      e.redirect.indexOf('http://')
        ? this.redirectFunc(e.redirect)
        : this.redirectFunc(decodeURI(e.redirect))
    } catch (t) {
      window.location.reload()
    }
  },
  formSubmit: function () {
    const obj = this
    if (unlimitFormCheckout.hasClass('processing')) {
      return
    }
    var submitBtn = $('#ulBtnSubmit')
    $.ajax({
      type: 'POST',
      url: unlimitFormCheckout.attr('action'),
      data: unlimitFormCheckout.serialize(),
      dataType: 'json',
      beforeSend: function () {
        submitBtn.button('loading')
      },
      complete: function () {
        submitBtn.button('reset')
      },
      success: function (e) {
        obj.onSuccessSubmit(e)
      },
      error: function (xhr, ajaxOptions, thrownError) {
        console.log(
          thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText)
      },
    })
  },
  setModalSize: function () {
    const backWindow = $('#modal-unlimit .modal-dialog')
    const w = $(window).width()
    const { iframePadding, maxIframeWidth } = this

    const marginTop = 40
    const marginBottom = 20

    const newWidth = Math.min(w - iframePadding, maxIframeWidth)
    const margin = Math.round((
                                w - newWidth
                              ) / 2)

    backWindow.css({
      'background': '#FFF',
      'max-height': '800px',
      'height': '100%',
      'border-radius': '10px',
      'padding': '10px',
      'box-shadow': '0 0 10px rgba(0, 0, 0, 0.2)',
      'margin-top': marginTop + 'px',
      'margin-left': margin + 'px',
      'margin-bottom': marginBottom + 'px',
      'width': newWidth + 'px',
      'max-width': 'none',
    })
  },
}

$(document).on('click', '#ulBtnSubmit', function (e) {
  const method = $(this).data('method')
  const prefix = $(this).data('prefix')
  if (prefix !== 'Card') {
    if (!validatePhone(prefix)) {
      e.preventDefault()
      return
    }
  }
  if (method === 'gateway') {
    return
  }
  e.preventDefault()
  unlimitIframeProcessor.formSubmit()
})
$(window).resize(function () {
  unlimitIframeProcessor.setModalSize()
})