<?php

require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_card.php';
require_once __DIR__ . '/../../../../admin/controller/extension/payment/ul_ticket.php';
require_once __DIR__ . '/../../../../admin/model/extension/payment/ul_payment_migrations.php';

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

    public function loadCommonHeader($extension_payment_ul, $user_token)
    {
        $this->load->model('setting/setting');

        $this->load->language($extension_payment_ul);

        $data['breadcrumbs'] = [];

        $token_session = $user_token . $this->session->data['user_token'];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $token_session, true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', $token_session, true),
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($extension_payment_ul, $token_session, true),
        ];

        $data['action'] = $this->url->link($extension_payment_ul, $token_session, true);

        $data['cancel'] = $this->url->link('extension/extension', $token_session, true);
    }

    public function getData($posts, $prefix)
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
     */
    public function loadCommonFooter(array $posts_fields)
    {
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_warning'] = $this->_error['warning'] ?? '';

        $data = $this->getData($posts_fields, static::CODE);

        $this->model_setting_setting->editSetting(static::CODE, $data);

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        return $data;
    }

    public function install()
    {
        $this->load->model(self::COMMON_EXTENSION);
        $this->model_extension_payment_ul_payment_migrations->install(static::CODE);
    }

    public function uninstall()
    {
        $this->load->model(self::COMMON_EXTENSION);
        $this->model_extension_payment_ul_payment_migrations->uninstall(static::CODE);
    }
}
