<?php

class UlRefundsForm
{
    protected $model_sale_order;
    protected $language;
    protected $currency;
    protected $instance_unlimint;
    protected $instance_refund;
    protected $loader;
    protected $order;
    protected $decimals;
    protected $order_info;
    protected $refunded_items;

    public function drawRefundForm(): string
    {
        $this->order_info = $this->instance_unlimint->getOrderInfo($this->order['order_id']);

        $result = '';
        if ($this->instance_refund->canRefund($this->order, $this->order_info)) {
            $this->refunded_items = $this->instance_refund->getRefundedItems($this->order['order_id']);
            $this->decimals = (int)$this->currency->getDecimalPlace($this->order['currency_code']);
            $result = $this->loader->view('extension/payment/unlimint_refund', array_merge(
                $this->getCommonData(),
                $this->getRefundedHistory(),
                $this->getOrderProducts(),
                $this->getOrderTotals()
            ));
        }

        return $result;
    }


    protected function getOrderProducts(): array
    {
        $data = [];

        $products = $this->model_sale_order->getOrderProducts($this->order['order_id']);
        foreach ($products as $product) {
            $price = $product['price'] + $product['tax'];

            $item_refund = $this->refunded_items['product'][$product['order_product_id']] ?? [
                    'quantity' => 0,
                    'amount' => 0,
                ];
            $item_refund['amount_format'] = $this->currency->format($item_refund['amount'], $this->order['currency_code'], $this->order['currency_value']);

            $data['order_products'][] = [
                'product_id' => $product['product_id'],
                'order_product_id' => $product['order_product_id'],
                'name' => $product['name'],
                'item_refund' => $item_refund,
                'model' => $product['model'],
                'quantity' => $product['quantity'],
                'price_format' => $this->currency->format($price, $this->order['currency_code'], $this->order['currency_value']),
                'total_format' => $this->currency->format($price * $product['quantity'], $this->order['currency_code'], $this->order['currency_value']),
                'price' => round($price, $this->decimals),
                'total' => round($price * $product['quantity'], $this->decimals),
            ];
        }

        return $data;
    }


    protected function getOrderTotals(): array
    {
        $data = [];

        $totals = $this->model_sale_order->getOrderTotals($this->order['order_id']);
        foreach ($totals as $total) {
            $item_refund = $this->refunded_items['total'][$total['order_total_id']] ?? [
                    'quantity' => 0,
                    'amount' => 0,
                ];
            $item_refund['amount_format'] = $this->currency->format($item_refund['amount'], $this->order['currency_code'], $this->order['currency_value']);

            $data[] = [
                'order_total_id' => $total['order_total_id'],
                'title' => $total['title'],
                'item_refund' => $item_refund,
                'text' => $this->currency->format($total['value'], $this->order['currency_code'], $this->order['currency_value']),
                'value' => $total['value']
            ];
        }

        return ['total_data' => $data];
    }

    protected function getCommonData(): array
    {
        $already_refunds = $this->instance_refund->getTotalOrderRefunds($this->order['order_id']);
        $available_refund = round($this->order_info['initial_amount'] - $already_refunds, 2);

        $data = [
            'available_refund' => $this->currency->format($available_refund, $this->order['currency_code'], $this->order['currency_value']),
            'already_refund' => $this->currency->format($already_refunds, $this->order['currency_code'], $this->order['currency_value']),
            'store_id' => $this->order['store_id'],
            'amount_step' => '0.' . str_repeat('0', $this->decimals - 1) . '1'
        ];

        $this->language->load('extension/payment/ul_card');

        $labels = [
            'ul_refund', 'ul_totals', 'ul_product', 'ul_model', 'ul_price', 'ul_quantity', 'ul_amount',
            'ul_restock', 'ul_already', 'ul_available', 'ul_re_amount', 'ul_re_reason', 'ul_cancel'
        ];

        foreach ($labels as $label) {
            $data['labels'][$label] = $this->language->get($label);
        }

        return $data;
    }

    protected function getRefundedHistory(): array
    {
        $data = $this->instance_refund->getRefundedHistory($this->order['order_id']);
        foreach ($data as $i => $item) {
            $data[$i]['amount_format'] = $this->currency->format(
                $item['amount'],
                $this->order['currency_code'],
                $this->order['currency_value']
            );

            $data[$i]['date_added_format'] = date($this->language->get('datetime_format'), strtotime($item['date_added']));
        }

        return ['refunded_history' => $data];
    }


    /**
     * @param mixed $model_sale_order
     *
     * @return self
     */
    public function setModelSaleOrder($model_sale_order): self
    {
        $this->model_sale_order = $model_sale_order;

        return $this;
    }

    /**
     * @param mixed $language
     *
     * @return self
     */
    public function setLanguage($language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param mixed $currency
     *
     * @return self
     */
    public function setCurrency($currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @param mixed $instance_unlimint
     *
     * @return self
     */
    public function setInstanceUnlimint($instance_unlimint): self
    {
        $this->instance_unlimint = $instance_unlimint;

        return $this;
    }

    /**
     * @param mixed $instance_refund
     *
     * @return self
     */
    public function setInstanceRefund($instance_refund): self
    {
        $this->instance_refund = $instance_refund;

        return $this;
    }

    /**
     * @param mixed $loader
     *
     * @return self
     */
    public function setLoader($loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * @param mixed $order
     *
     * @return self
     */
    public function setOrder($order): self
    {
        $this->order = $order;

        return $this;
    }
}
