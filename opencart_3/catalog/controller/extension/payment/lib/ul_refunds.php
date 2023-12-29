<?php

/**
 * Unlimint refunds
 */
class ULRefunds
{
    public const OC_COMPLETE_STATUS_ID = 5;

    protected Config $config;
    protected Unlimint $ul;
    protected DB $db;

    public function getTotalOrderRefunds($order_id)
    {
        $query = $this->db->query("SELECT SUM(amount) AS amount FROM " . DB_PREFIX . "ul_refunds WHERE order_id = $order_id");

        return (!empty($query->row) && !empty($query->row['amount'])) ? $query->row['amount'] : 0;
    }

    public function logRefundedItems($order_id, $data): void
    {
        $reason = $data['reason'] ?? '';
        if (!empty($data['products'])) {
            foreach ($data['products'] as $product) {
                $this->writeReturnedItem(
                    $order_id,
                    $product['product_id'],
                    'product',
                    $product['amount'],
                    $product['quantity'],
                    $reason
                );
            }
        }

        if (!empty($data['totals'])) {
            foreach ($data['totals'] as $total) {
                $this->writeReturnedItem(
                    $order_id,
                    $total['total_id'],
                    'total',
                    $total['amount'],
                    0,
                    $reason
                );
            }
        }

        $this->writeReturnedItem($order_id, 0, 'sum', $data['refund'], 0, $reason);
    }

    public function saveRefund($order_id, $payment): void
    {
        $this->db->query(
            'INSERT INTO ' . DB_PREFIX . 'ul_refunds SET 
            order_id=' . $order_id . ', 
            refund_id=' . ((int)$payment['refund_data']['id']) . ',
            amount=' . ((float)$payment['refund_data']['amount'])
        );
    }

    protected function writeReturnedItem($order_id, $item_id, $item_type, $amount, $quantity, $comment): void
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
     * @return bool
     */
    public function canRefund(array $order, ?array $order_info = null): bool
    {
        if ((int)$order['order_status_id'] !== self::OC_COMPLETE_STATUS_ID) {
            return false;
        }

        if (empty($order_info)) {
            $sql = 'select * from ' . DB_PREFIX . 'ul_orders where order_id=' . ((int)$order['order_id']) . ' limit 1';
            $res = $this->db->query($sql);
            $order_info = (!empty($res->row)) ? $res->row : [];
        }

        return !(
            (empty($order_info)) ||
            ($order_info['payment_recurring'] > 0)
        );
    }

    public function getRefundedHistory($order_id): array
    {
        $sql = 'SELECT * 
                FROM ' . DB_PREFIX . 'ul_refund_history 
                WHERE order_id=' . ((int)$order_id) . ' AND item_type="sum"';
        $res = $this->db->query($sql);

        return (empty($res->rows)) ? [] : $res->rows;
    }

    public function getRefundedItems($order_id): array
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
    public function setConfig(Config $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Unlimint $ul
     *
     * @return self
     */
    public function setUl(Unlimint $ul): self
    {
        $this->ul = $ul;

        return $this;
    }

    /**
     * @param DB $db
     *
     * @return self
     */
    public function setDb(DB $db): self
    {
        $this->db = $db;

        return $this;
    }
}
