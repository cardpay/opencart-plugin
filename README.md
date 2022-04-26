# Unlimint Opencart plugin

## Overview

**Unlimint Opencart** engine plugin allows merchants to make payments and installment payments using the Opencart platform, as well as preauthorized payments plugin supports cancellation (void), capture of the payment or installment payment.

### Requirements

**Unlimint Opencart** plugin is open-source and supports:

 * The Opencart engine (version 3.0.3.8)
 * PHP 7.4 and all other requirements regarding official [Opencart recommendations](https://docs.opencart.com/requirements/)

### Supported payment methods

**Unlimint Opencart** plugin supports the following payment methods:

 * Unlimint Credit Card (v1.0.3)
 * Unlimint Boleto (v1.0.3)

## Installation

Installation process explains how to install the **Opencart plugin**:

1. Download the latest version of Opencart plugin from Unlimint's Github [repository](https://gitlab.cardpay-test.com/cms-plugins/opencart).

2. Go to the required root directory.

3. Upload the plugin folder to the root directory. As a result, the required plugin directory should be presented.

![](readme_images/opencart_extension_installer.png)

**Unlimint Opencart** plugin was successfully installed and enabled.

## Configuration

Configuration process explains how to set up and configure the Opencart plugin to accept payments in supported payment methods.

### Basic settings

Begin with the following basic settings:

1. Log in to Admin panel of the **Unlimint Opencart** plugin (using admin credentials).

2. Navigate to **Extensions** > **Extensions** > **Payments** (payment methods settings).

![](readme_images/opencart_extension_payments.png)

3. Select the required payment method.

![](readme_images/opencart_payment_methods.png)

#### Payment methods settings

It is necessary to enable payment methods in the Opencart plugin:

 * Unlimint Credit Card (v1.0.3)
 * Unlimint Boleto (v1.0.3)

Each supported payment method includes the **Settings** tab and the **Order status** tab.

**Order status** tab displays the required _mapping of transaction and order statuses_ and allows the selection of the following order statuses:

 * Canceled
 * Canceled Reversal
 * Chargeback
 * Complete
 * Denied
 * Expired
 * Failed
 * Pending
 * Processed
 * Processing
 * Refunded
 * Reversed
 * Shipped
 * Voided

First, access the requested methods and enable them by **Unlimint support** (a part of merchant onboarding process - see [here](https://www.unlimint.com/integration/)).

To enable payments via **Unlimint Credit Card (v1.0.3)**, do the following steps:

 * Go to **Unlimint Credit Card (v1.0.3)** payment method and click **Edit**.

![](readme_images/opencart_boleto_credit_card_settings.png)

 * Switch on **Enabled** for **Unlimint Credit Card (v1.0.3)** payment method. 

 ![](readme_images/opencart_plugin_enable.png)

 * Set **Terminal code**, **Terminal password**, **Callback secret** values - it should be merchant credentials in Unlimint APIv3 for this payment method (how to obtain credentials see [here](https://www.unlimint.com/integration/)).

 * **Test environment**:
   * Set to **Yes** for Sandbox environment (for test purposes).
   * Set to **No** for Production environment.

 * **Payment title** - fill in the name of the payment method, will be presented for the customer in checkout.
<!-- … 
 * **Capture payment**:
   * Set to **Yes** for completion payment automatically (one phase payment).
   * Set to **No** for two phases payment: the amount will not be captured but only blocked.

With **No** option selected, payments will be captured automatically in 7 days from the time of creating the preauthorized transaction.

In installment case with **No** option selected installments will be declined automatically in 7 days from the time of creating the preauthorized transaction.
-->
 * **Installment enabled:** - this setting enables installment payments possibility.
   * Set to **Yes** - installment payments are enabled, number of installments are available for selection in payment form, it's possible to split payment to 2-12 installments, each installment in 30 days period.
   * Set to **No** - installment payments are disabled.
 * **Ask CPF** - set to **Yes** if **CPF (Brazilian Tax Id)** is required for the customer in checkout.
 * **Dynamic Descriptor** - short description of the service or product, see `dynamic_descriptor` API field in [API documentation](https://integration.unlimint.com/#PaymentRequestPaymentData).
 * **Log to file** - Opencart plugin system log setting, this log file contains the plugin debug information, communication errors between plugin front-end and back-end. By default, it's set to **Yes**. If it will be set to **No** - the log file won't be created. 

Click **Save** or **Cancel** in order to save or cancel the preferred settings.

To enable payments via **Unlimint Boleto (v1.0.3)** payment method, do the following steps:

 * Go to **Unlimint Boleto (v1.0.3)** payment method and click **Edit**.

 ![](readme_images/opencart_boleto_settings.png)

 * Switch on **Enabled** for **Unlimint Boleto (v1.0.3)** payment method.

 ![](readme_images/opencart_plugin_enable.png)

 * Set **Terminal code**, **Terminal password**, **Callback secret** values - it should be merchant credentials in Unlimint API v3 for this payment method (how to obtain credentials see [here](https://www.unlimint.com/integration/)).

 * **Test environment**:
   * Set to **Yes** for Sandbox environment (for test purposes).
   * Set to **No** for Production environment.

 * **Payment title** - fill in the name of the payment method, will be presented for the customer in checkout.
 
 * **Log to file** - Opencart plugin system log setting, this log file contains the plugin debug information, communication errors between plugin front-end and back-end. By default, it's set to **Yes**. If it will be set to **No** - the log file won't be created. 

Click **Save** or **Cancel** in order to save or cancel the preferred settings.

The selected payment methods are successfully enabled in the checkout.

#### Order status tab settings (mapping of the order statuses)

Mapping of the order statuses is set by default and must be changed _only_ if Merchants have custom order statuses flow (not recommended to change).

**Flow of the statuses** is **unique** for each supported payment method in plugin. If Merchants change the status flow for **Unlimint Credit Card (v1.0.3)**, the status flow for the **Unlimint Boleto (v1.0.3)** payment methods is not changed.

If it is required to see or change **Order mapping** statuses for **Unlimint Credit Card (v1.0.3)** - go to **Unlimint Credit Card (v1.0.3)** and choose **Order status** tab.

![](readme_images/opencart_unlimint_credit_card_order_status.png)

Refer to the **Order status** tab in order to see or change the Order mapping statuses for **Unlimint Boleto (v1.0.3)**.

![](readme_images/opencart_unlimint_boleto_order_status.png)

### Payment notification configuration

This process explains how to set up Order statuses for payment notifications:

1. Log in to the Unlimint’s [Merchant account](https://sandbox.cardpay.com/ma) with Merchant credentials (obtaining of merchant credentials is a part of merchant onboarding process - see details [here](https://www.unlimint.com/integration/)).
2. Go to **Wallet Settings** and click on the Wallet's ID (Settings / Wallet settings / choose specific wallet id / Callbacks / JSON callback URL).
3. Fill in the JSON Callback URL field with:

`https://<merchant_domain>/index.php?route=extension/payment/ul_general/callback` where _<merchant_domain>_ is website domain.

The notification statuses have been successfully configured.

<!-- … 

## Supported post-payment operations

Unlimint Opencart plugin supports the following post-payment operations:

 * Cancellation (void) / Capture of the preauthorized payment.

### Cancellation (void) / Capture of the payment

Cancellation (void) / capture of the payment only works for **Unlimint Credit Card (v1.0.3)** payment method.

It's available only for orders which were processed by a certain payment method configuration (**Capture payment** is set to **No**). 

If **Capture payment** is set to **Yes** - an order will be completed without any user actions in Opencart Admin Panel.

#### Capture of the payment

For Capture of the preauthorized payment, navigate to **Order Status** tab and click **Pending** status for capture payment.

The status of the order is changed to **Pending**.

#### Cancel (void) the payment

For cancel (void) the payment, navigate to **Unlimint Credit Card (v1.0.3)** and choose the **Order Status** tab for cancel (void) payment, then click **Canceled**.

Order status is changed to **Canceled**.

-->