<?php

class ModelExtensionPaymentUlPaymentMigrations extends Model
{
    private const STORE_ID = 0;

    protected function get_code_insert($code, $key, $value)
    {
        return sprintf("(%s, '%s', '%s%s', '%s', '0')", self::STORE_ID, $code, $code, $key, $value);
    }

    public function install($code_prefix)
    {
        if ($code_prefix === 'payment_ul_card_') {
            $prefix = "INSERT INTO " . DB_PREFIX . "event (`event_id`, `code`, `trigger`, `action`, `status`, `sort_order`) VALUES ";
            $this->db->query($prefix . "(NULL, 'order_info', 'admin/controller/sale/order/info/after', 'sale/ul_order_info/info', 1, 0)");
            $this->db->query($prefix . "(NULL, 'order_edit', 'admin/controller/sale/order/edit/after', 'sale/ul_order_info/edit', 1, 0)");
        }

        $sql = '';
        $select_count_ul_ticket = $this->db->query("SELECT code FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.code = '" . $code_prefix . "'");
        if ($select_count_ul_ticket->num_rows < 1) {
            $sql = "
            INSERT INTO
                " . DB_PREFIX . "setting(
                    `store_id`,
                    `code`,
                    `key`,
                    `value`,
                    `serialized`
                )
            VALUES
            " . join(', ', [
                    $this->get_code_insert($code_prefix, 'new_status', 1),
                    $this->get_code_insert($code_prefix, 'processing', 1),
                    $this->get_code_insert($code_prefix, 'declined', 10),
                    $this->get_code_insert($code_prefix, 'authorized', 2),
                    $this->get_code_insert($code_prefix, 'completed', 5),
                    $this->get_code_insert($code_prefix, 'cancelled', 7),
                    $this->get_code_insert($code_prefix, 'charged_back', 13),
                    $this->get_code_insert($code_prefix, 'chargeback_resolved', 5)
            ]);

            if ($code_prefix == 'payment_ul_card_') {
                $sql .= "," . join(
                    ', ',
                    [$this->get_code_insert($code_prefix, 'refunded', 11),
                            $this->get_code_insert($code_prefix, 'voided', 7),
                            $this->get_code_insert($code_prefix, 'terminated', 1)]
                );
            }
        }
        if ($sql) {
            $this->db->query($sql);
        }

        $sql = 'CREATE TABLE IF NOT EXISTS ' . DB_PREFIX . 'ul_orders (
        `order_id` int NOT NULL,
        `transaction_id` bigint unsigned,
        `initial_amount` decimal(16,2),
        `payment_recurring` int,
        PRIMARY KEY (`order_id`)
        )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci';
        $this->db->query($sql);
    }

    public function uninstall($code_prefix)
    {
        if ($code_prefix === 'payment_ul_card_') {
            $prefix = "DELETE FROM " . DB_PREFIX . "event WHERE " . DB_PREFIX . "event.code=";
            $this->db->query($prefix . "'order_info' AND `action`='sale/ul_order_info/info'");
            $this->db->query($prefix . "'order_edit' AND `action`='sale/ul_order_info/edit'");
        }

        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ul_orders`");
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.code = '" . $code_prefix . "'");
    }
}
