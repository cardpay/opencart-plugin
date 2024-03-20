<?php

use Opencart\System\Engine\Config;
use Opencart\System\Library\DB;
use Unlimit\Unlimit;
use Opencart\System\Library\Cart\Currency;


/**
 * Unlimit refunds
 */
class ULRefunds
{
    public const OC_COMPLETE_STATUS_ID = 5;

    protected Config $config;
    protected Unlimit $ul;
    protected DB $db;
    protected Currency $currency;

    public function get_total_order_refunds($order_id)
    {
        $query = $this->db->query(
            "SELECT SUM(amount) AS amount FROM " . DB_PREFIX . "ul_refunds WHERE order_id = $order_id"
        );

        return (!empty($query->row) && !empty($query->row['amount'])) ? $query->row['amount'] : 0;
    }

    public function record_refunded_items($order, $data): void
    {
        $reason = $data['reason'] ?? '';
        if (!empty($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->write_returned_item(
                    $order['order_id'],
                    $product['product_id'],
                    'product',
					$this->formatAmountForDb($product['amount'], $order),
                    $product['quantity'],
                    $reason
                );
            }
        }

        if (!empty($data['totals'])) {
            foreach ($data['totals'] as $total) {
                $this->write_returned_item(
                    $order['order_id'],
                    $total['total_id'],
                    'total',
					$this->formatAmountForDb($total['amount'], $order),
                    0,
                    $reason
                );
            }
        }

        $this->write_returned_item(
			$order['order_id'],
			0,
			'sum',
			$this->formatAmountForDb($data['refund'], $order),
			0,
			$reason
        );
    }

	public function formatAmountForDb( $amount, $order ) {
		return $this->currency->format(
            $amount,
            $order['currency_code'],
			(1/$order['currency_value']),
			false
        );
	}

    public function save_refund($order, $payment): void
    {
        $this->db->query(
            'INSERT INTO ' . DB_PREFIX . 'ul_refunds SET
            order_id=' . $order['order_id'] . ',
            refund_id=' . ((int)$payment['refund_data']['id']) . ',
            amount=' . ($this->formatAmountForDb((float)$payment['refund_data']['amount'], $order))
        );
    }

    protected function write_returned_item($order_id, $item_id, $item_type, $amount, $quantity, $comment): void
    {
        $sql = sprintf(
            'INSERT INTO ' . DB_PREFIX . 'ul_refund_history
        SET order_id=%s, item_id=%s, item_type="%s", amount=%s, quantity=%s, comment="%s"',
            (int)$order_id,
            (int)$item_id,
            $item_type,
            (float)$amount,
            (float)$quantity,
            $this->db->escape($comment)
        );

        $this->db->query($sql);
    }

    /**
     * @param array $order
     * @param array|null $order_info
     *
     * @return bool
     */
    public function can_cefund(array $order, ?array $order_info = null): bool
    {
        if ((int)$order['order_status_id'] !== self::OC_COMPLETE_STATUS_ID) {
            return false;
        }

        if (empty($order_info)) {
            $sql = 'select * from ' . DB_PREFIX . 'ul_orders where order_id=' . ((int)$order['order_id']) . ' limit 1';
            $res = $this->db->query($sql);
            $order_info = (!empty($res->row)) ? $res->row : [];
        }

        if (empty($order_info) || $order_info['payment_recurring'] > 0) {
            return false;
        }

        $isUlCard = $order['payment_method']['code'] === 'ul_card.ul_card';
        $isOtherMethod = in_array(
            $order['payment_method']['code'],
            ['ul_apay.ul_apay', 'ul_gpay.ul_gpay', 'ul_mbway.ul_mbway', 'ul_paypal.ul_paypal']
        );

        $field_installment_type = $order_info['installment_type'] ?? '';
        $field_count_installment_type = $order_info['count_installment_type'] ?? '';
        $isInstallmentTypeValid = ($field_installment_type == 'IF' || ($field_installment_type == 'MF_HOLD' && $field_count_installment_type == 1))
            || (empty($field_installment_type) && empty($field_count_installment_type));

        return ($isInstallmentTypeValid && $isUlCard) || $isOtherMethod;
    }

    public function get_refunded_items($order_id): array
    {
        $sql = 'SELECT item_id, item_type, SUM(amount) AS amount, SUM(quantity) AS quantity
                FROM ' . DB_PREFIX . 'ul_refund_history
                WHERE order_id=' . ((int)$order_id) . ' AND item_type<>"sum" GROUP BY item_id, item_type';
        $res = $this->db->query($sql);

        $result = [
            'product' => [],
            'total' => [],
            'sum' => 0
        ];

        if (!empty($res->rows)) {
            foreach ($res->rows as $row) {
                $data = [
                    'amount' => $row['amount'],
                    'quantity' => $row['quantity'],
                ];

                if ($row['item_type'] !== 'sum') {
                    $result[$row['item_type']][$row['item_id']] = $data;
                }
            }
        }

        return $result;
    }

    /**
     * @param Config $config
     *
     * @return self
     */
    public function set_config(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Unlimit $ul
     *
     * @return self
     */
    public function set_ul(Unlimit $ul): self
    {
        $this->ul = $ul;

        return $this;
    }

    /**
     * @param DB $db
     *
     * @return self
     */
    public function set_db(DB $db): self
    {
        $this->db = $db;

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
}
