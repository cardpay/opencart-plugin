(
  function ($) {
    'use strict'

    $(function () {
      $('form.checkout').
        on('checkout_place_order_woo-unlimit-mbway', function () {
          return true
        })
    })
  }(jQuery)
)