<?php

require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_card.php';
require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_ticket.php';
require_once __DIR__ . '/../../../../admin/model/extension/payment/ul_payment_migrations.php';

/**
 * @property Loader $load
 * @property Config $config
 */
class ControllerExtensionPaymentUl extends Controller
{
    public const COMMON_EXTENSION = 'extension/payment/ul_payment_migrations';

    protected const POST_FIELDS = [
        'status',
        'terminal_code',
        'terminal_password',
        'callback_secret',
        'test_environment',
        'payment_title',
        'log_to_file',
        'new_status',
        'processing',
        'authorized',
        'cancelled',
        'declined',
        'charged_back',
        'completed',
        'chargeback_resolved',
    ];


    /**
     * @throws Exception
     */
    public function loadCommonHeader(string $extension_payment_ul): void
    {
        $this->load->model('setting/setting');
        $this->load->language($extension_payment_ul);
    }

    /**
     * @param array $posts
     * @param string $prefix
     * @return array
     */
    public function getData(array $posts, string $prefix): array
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

    /**
     * @param array $posts_fields
     * @return array
     * @throws Exception
     */
    public function loadCommonFooter(array $posts_fields): array
    {
        $data = $this->getData($posts_fields, static::CODE);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_warning'] = $this->_error['warning'] ?? '';

        $this->model_setting_setting->editSetting(static::CODE, $data);

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $data;
    }

    /**
     * @throws Exception
     */
    public function install(): void
    {
        $this->load->model(self::COMMON_EXTENSION);
        $this->model_extension_payment_ul_payment_migrations->install(static::CODE);
    }

    /**
     * @throws Exception
     */
    public function uninstall(): void
    {
        $this->load->model(self::COMMON_EXTENSION);
        $this->model_extension_payment_ul_payment_migrations->uninstall(static::CODE);
    }
}
