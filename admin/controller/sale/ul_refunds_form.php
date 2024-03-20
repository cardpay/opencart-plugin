<?php

use Opencart\System\Engine\Loader;
use Opencart\System\Engine\Proxy;
use Opencart\System\Library\Cart\Currency;
use Opencart\System\Library\Language;
use Unlimit\Unlimit;

class UlRefundsForm
{
    protected Proxy $model_sale_order;
    protected Language $language;
    protected Currency $currency;
    protected Unlimit $instance_unlimit;
    protected ULRefunds $instance_refund;
    protected Loader $loader;
    protected array $order;
    protected int $decimals;
    protected array $order_info;
    protected array $refunded_items;

    public function draw_refund_form($db): string
    {
        $this->order_info = $this->instance_unlimit->get_order_info($this->order['order_id']);

        $result = '';
        if ($this->instance_refund->can_cefund($this->order, $this->order_info)) {
            $this->refunded_items = $this->instance_refund->get_refunded_items($this->order['order_id']);
            $this->decimals = (int)$this->currency->getDecimalPlace($this->order['currency_code']);
            $result = $this->loader->view(
                'extension/unlimit/payment/unlimit_refund',
                array_merge(
                    $this->get_common_data($db),
                    $this->get_order_products($db),
                    $this->get_order_totals($db)
                )
            );
        }

        return $result;
    }


    protected function get_order_products($db): array
    {
        $orderProducts = $db->query(
            "SELECT * FROM `oc_order_product` WHERE `order_id` = " . $this->order['order_id']
        )->rows;
        foreach ($orderProducts as $product) {
            $price = $product['price'] + $product['tax'];

            $item_refund = $this->refunded_items['product'][$product['order_product_id']] ?? [
                    'quantity' => 0,
                    'amount' => 0,
                ];
            $item_refund['amount_format'] = $this->currency->format(
                $item_refund['amount'],
                $this->order['currency_code'],
                $this->order['currency_value']
            );

            $data['order_products'][] = [
                'product_id' => $product['product_id'],
                'order_product_id' => $product['order_product_id'],
                'name' => $product['name'],
                'item_refund' => $item_refund,
                'model' => $product['model'],
                'quantity' => $product['quantity'],
                'price_format' => $this->currency->format(
                    $price,
                    $this->order['currency_code'],
                    $this->order['currency_value']
                ),
                'total_format' => $this->currency->format(
                    $price * $product['quantity'],
                    $this->order['currency_code'],
                    $this->order['currency_value']
                ),
                'price' => preg_replace(
                    '/[^\d.]+/',
                    '',
                    $this->currency->format(
                        $price,
                        $this->order['currency_code'],
                        $this->order['currency_value']
                    )
                ),
                'total' => preg_replace(
                    '/[^\d.]+/',
                    '',
                    $this->currency->format(
                        $price * $product['quantity'],
                        $this->order['currency_code'],
                        $this->order['currency_value']
                    )
                ),
            ];
        }

        return $data;
    }


    protected function get_order_totals($db): array
    {
        $data = [];

        $totals = $db->query(
            "SELECT * FROM " . DB_PREFIX . "order_total
          WHERE order_id = '" . (int)$this->order['order_id'] . "'
          AND code = 'shipping'
          ORDER BY sort_order"
        )->rows;
        foreach ($totals as $total) {
            $item_refund = $this->refunded_items['total'][$total['order_total_id']] ?? [
                    'quantity' => 0,
                    'amount' => 0,
                ];
            $item_refund['amount_format'] = $this->currency->format(
                $item_refund['amount'],
                $this->order['currency_code'],
                $this->order['currency_value']
            );

            $data[] = [
                'order_total_id' => $total['order_total_id'],
                'title' => $total['title'],
                'item_refund' => $item_refund,
                'text' => $this->currency->format(
                    $total['value'],
                    $this->order['currency_code'],
                    $this->order['currency_value']
                ),
                'value' => $total['value']
            ];
        }

        return ['total_data' => $data];
    }

    protected function get_common_data($db): array
    {
        $already_refunds = $this->instance_refund->get_total_order_refunds($this->order['order_id']);
        $decimal_places = $this->currency->getDecimalPlace($this->order['currency_code']);
        $available_refund = round($this->order['total'] - $already_refunds, $decimal_places);

        $data = [
            'available_refund' => $this->currency->format(
                $available_refund,
                $this->order['currency_code'],
                $this->order['currency_value']
            ),
            'already_refund' => $this->currency->format(
                $already_refunds,
                $this->order['currency_code'],
                $this->order['currency_value']
            ),
            'store_id' => $this->order['store_id'],
            'amount_step' => '10'
        ];

        $config_language_value = $db->query(
            "SELECT `value` FROM `oc_setting` WHERE `key` = 'config_language'"
        )->row['value'];
        $this->language->load('extension/unlimit/payment/ul_card', '', $config_language_value);

        $labels = [
            'ul_refund',
            'ul_totals',
            'ul_product',
            'ul_model',
            'ul_price',
            'ul_quantity',
            'ul_amount',
            'ul_restock',
            'ul_already',
            'ul_available',
            'ul_re_amount',
            'ul_re_reason',
            'ul_cancel'
        ];

        foreach ($labels as $label) {
            $data['labels'][$label] = $this->language->get($label);
        }

        return $data;
    }

    /**
     * @param mixed $model_sale_order
     *
     * @return self
     */
    public function set_model_sale_order($model_sale_order): self
    {
        $this->model_sale_order = $model_sale_order;

        return $this;
    }

    /**
     * @param mixed $language
     *
     * @return self
     */
    public function set_language($language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param mixed $currency
     *
     * @return self
     */
    public function set_currency($currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param mixed $instance_unlimit
     *
     * @return self
     */
    public function set_instance_unlimit($instance_unlimit): self
    {
        $this->instance_unlimit = $instance_unlimit;

        return $this;
    }

    /**
     * @param ULRefunds $instance_refund
     *
     * @return self
     */
    public function set_instance_refund(ULRefunds $instance_refund): self
    {
        $this->instance_refund = $instance_refund;

        return $this;
    }

    /**
     * @param mixed $loader
     *
     * @return self
     */
    public function set_loader($loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @param mixed $order
     *
     * @return self
     */
    public function set_order($order): self
    {
        $this->order = $order;

        return $this;
    }
}
