<?php

class ModelExtensionPaymentUlPaymentMigrations extends Model
{
    private const STORE_ID = 0;

    protected function getCodeInsert($code, $key, $value)
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
                    $this->getCodeInsert($code_prefix, 'new_status', 1),
                    $this->getCodeInsert($code_prefix, 'processing', 1),
                    $this->getCodeInsert($code_prefix, 'declined', 10),
                    $this->getCodeInsert($code_prefix, 'authorized', 2),
                    $this->getCodeInsert($code_prefix, 'completed', 5),
                    $this->getCodeInsert($code_prefix, 'cancelled', 7),
                    $this->getCodeInsert($code_prefix, 'charged_back', 13),
                    $this->getCodeInsert($code_prefix, 'chargeback_resolved', 5)
                ]);

            if ($code_prefix == 'payment_ul_card_') {
                $sql .= "," . join(
                    ', ',
                    [$this->getCodeInsert($code_prefix, 'refunded', 11),
                            $this->getCodeInsert($code_prefix, 'voided', 7),
                            $this->getCodeInsert($code_prefix, 'terminated', 1)]
                );
            }
        }

        if ($sql) {
            $this->db->query($sql);
        }

        $create = 'CREATE TABLE IF NOT EXISTS ' . DB_PREFIX;

        $sql = $create . 'ul_orders (
        `order_id` int NOT NULL,
        `transaction_id` bigint unsigned,
        `is_complete` int default 0,
        `initial_amount` decimal(15,4),
        `payment_recurring` int,
        PRIMARY KEY (`order_id`)
        )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci';
        $this->db->query($sql);

        $sql = $create . 'ul_refunds (
        `order_refund_id` int NOT NULL AUTO_INCREMENT,
        `order_id` int NOT NULL,
        `refund_id` bigint unsigned,
        `amount` decimal(15,4),
        `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
        KEY (`order_id`),
        PRIMARY KEY (`order_refund_id`)
        )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci';
        $this->db->query($sql);

        $sql = $create . 'ul_refund_history (
        `history_refund_id` int NOT NULL AUTO_INCREMENT,
        `order_id` int NOT NULL,
        `item_id` int NULL,
        `item_type` varchar(32),
        `amount` decimal(15,4),
        `quantity` decimal(15,4) default 0,
        `comment` varchar(255),
        `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
        KEY (`order_id`),
        PRIMARY KEY (`history_refund_id`)
        )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci';
        $this->db->query($sql);

        $sql = $create . 'ul_cart_backup (
        `session_id` varchar(32) NOT NULL,
        `api_id` int NOT NULL,
        `customer_id` int NOT NULL,
        `products` json,
        `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`session_id`)
        )  ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci';
        $this->db->query($sql);
    }

    public function uninstall($code_prefix)
    {
        if ($code_prefix === 'payment_ul_card_') {
            $prefix = "DELETE FROM " . DB_PREFIX . "event WHERE " . DB_PREFIX . "event.code=";
            $this->db->query($prefix . "'order_info' AND `action`='sale/ul_order_info/info'");
            $this->db->query($prefix . "'order_edit' AND `action`='sale/ul_order_info/edit'");

            $drop = "DROP TABLE IF EXISTS `" . DB_PREFIX;

            $this->db->query($drop . "ul_orders`");
            $this->db->query($drop . "ul_refunds`");
            $this->db->query($drop . "ul_refund_history`");
            $this->db->query($drop . "ul_cart_backup`");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.code = '" . $code_prefix . "'");
    }
}
