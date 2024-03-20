<?php

namespace Opencart\Admin\Controller\Extension\Unlimit;

require_once __DIR__ . '/sale/ul_order_info.php';
require_once __DIR__ . '/ajax/ajax_form.php';

use Unlimit\AjaxForm;
use Unlimit\UlOrderInfo;

class UlPayment extends \Opencart\System\Engine\Controller
{
    private const STORE_ID = 0;

    protected const POST_FIELDS = [
        'status',
        'terminal_code',
        'terminal_password',
        'callback_secret',
        'test_environment',
        'payment_title',
        'log_to_file',
        'sort_order',
    ];

    /**
     * @param string $code
     * @param string $key
     * @param string $value
     *
     * @return string
     */
    protected function get_code_insert(string $code, string $key, string $value): string
    {
        return sprintf("(%s, '%s', '%s%s', '%s', '0')", self::STORE_ID, $code, $code, $key, $value);
    }

    public function info()
    {
        $ulOrderInfo = new UlOrderInfo($this->registry);
        $ulOrderInfo->info();
    }

    public function ajax_refund_form(): void
    {
        $ajax_form = new AjaxForm($this->registry);
        $ajax_form->ajax_refund_form();
    }

    public function install(): void
    {
        $this->load->model('setting/event');
        $code_prefix = static::CODE;

        $select_count_ul_order_info = $this->db->query(
            "SELECT code FROM " . DB_PREFIX . "event WHERE " . DB_PREFIX . "event.code = 'order_info_payment'"
        );
        if ($select_count_ul_order_info->num_rows < 1) {
            $eventData = [
                'code' => 'order_info_payment',
                'description' => '',
                'trigger' => 'admin/controller/sale/order.info/after',
                'action' => 'extension/unlimit/ul_payment.info',
                'status' => 1,
                'sort_order' => 1
            ];

            $this->model_setting_event->addEvent($eventData);
        }

        $sql = '';
        $select_count_ul_ticket = $this->db->query(
            "SELECT code FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.code = '" . $code_prefix . "'"
        );
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
            " . implode(', ', [
                    $this->get_code_insert($code_prefix, 'new_status', 1),
                    $this->get_code_insert($code_prefix, 'processing', 1),
                    $this->get_code_insert($code_prefix, 'declined', 10),
                    $this->get_code_insert($code_prefix, 'authorized', 2),
                    $this->get_code_insert($code_prefix, 'completed', 5),
                    $this->get_code_insert($code_prefix, 'cancelled', 7),
                    $this->get_code_insert($code_prefix, 'charged_back', 13),
                    $this->get_code_insert($code_prefix, 'chargeback_resolved', 5)
                ]);

            if ($code_prefix === 'payment_ul_card_') {
                $sql .= "," . implode(
                        ', ',
                        [
                            $this->get_code_insert($code_prefix, 'refunded', 11),
                            $this->get_code_insert($code_prefix, 'voided', 7),
                            $this->get_code_insert($code_prefix, 'terminated', 1)
                        ]
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
        `telephone_number` varchar(32) NOT NULL,
        `is_complete` int default 0,
        `initial_amount` decimal(15,4),
        `payment_recurring` int,
        `installment_type` varchar(255),
        `count_installment_type` int,
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

        $sql = $create . 'ul_order_meta (
        `order_meta_id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` int(11) NOT NULL,
        `key` varchar(255) NOT NULL,
        `value` text NOT NULL,
        PRIMARY KEY (`order_meta_id`),
        KEY `order_id` (`order_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
        $this->db->query($sql);
    }

    public function uninstall(): void
    {
        $code_prefix = static::CODE;

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "event WHERE " . DB_PREFIX . "event.code = 'order_info_payment' AND `action` = 'extension/unlimit/ul_payment.info'"
        );

		$this->load->model('setting/extension');
	    $number_of_extensions = $this->model_setting_extension->getTotalExtensionsByExtension('unlimit');
	    if($number_of_extensions === 1) {
            $tables = ['ul_orders', 'ul_refunds', 'ul_refund_history', 'ul_cart_backup'];
            foreach ($tables as $table) {
                $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . $table . "`");
            }
        }

        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "setting WHERE " . DB_PREFIX . "setting.code = '" . $code_prefix . "'"
        );
    }

    public function load_common_footer(array $posts_fields): array
    {
        $data = $this->get_data($posts_fields, static::CODE);

        $data['cancel'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment',
            true
        );
        $data['save'] = $this->url->link(
            'extension/example_payment/payment/example_payment.save',
            'user_token=' . $this->session->data['user_token']
        );
        $data['back'] = $this->url->link(
            'marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment'
        );

        $this->model_setting_setting->editSetting(static::CODE, $data);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $data;
    }

    public function get_data(array $posts, string $prefix): array
    {
        $posts = array_merge(self::POST_FIELDS, $posts);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($posts as $field) {
                $fieldname = $prefix . $field;
                if (isset($this->request->post[$fieldname])) {
                    $data[$fieldname] = $this->request->post[$fieldname];
                }
            }
        } else {
            foreach ($posts as $field) {
                $fieldname = $prefix . $field;
                $value = $this->config->get($fieldname);
                $data[$fieldname] = $value;
            }
        }

        return $data ?? [];
    }
}
