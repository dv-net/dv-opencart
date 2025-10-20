<?php

class ControllerExtensionPaymentDvGateway extends Controller
{
    private $error = array();

    /**
     * Main method for the settings page.
     * Handles loading settings, saving them, and rendering the view.
     */
    public function index()
    {
        $this->load->language('extension/payment/dv_gateway');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_dv_gateway', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        // Load language strings
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');

        $data['entry_merchant_url'] = $this->language->get('entry_merchant_url');
        $data['entry_api_key'] = $this->language->get('entry_api_key');
        $data['entry_api_secret'] = $this->language->get('entry_api_secret');
        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_merchant_url'] = $this->language->get('help_merchant_url');
        $data['help_api_key'] = $this->language->get('help_api_key');
        $data['help_api_secret'] = $this->language->get('help_api_secret');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        // Set error messages if any
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['merchant_url'])) {
            $data['error_merchant_url'] = $this->error['merchant_url'];
        } else {
            $data['error_merchant_url'] = '';
        }

        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }

        if (isset($this->error['api_secret'])) {
            $data['error_api_secret'] = $this->error['api_secret'];
        } else {
            $data['error_api_secret'] = '';
        }

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/dv_gateway', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Actions
        $data['action'] = $this->url->link('extension/payment/dv_gateway', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        // Load settings values
        $fields = ['merchant_url', 'api_key', 'api_secret', 'order_status_id', 'status', 'sort_order'];
        foreach ($fields as $field) {
            if (isset($this->request->post['payment_dv_gateway_' . $field])) {
                $data['payment_dv_gateway_' . $field] = $this->request->post['payment_dv_gateway_' . $field];
            } else {
                $data['payment_dv_gateway_' . $field] = $this->config->get('payment_dv_gateway_' . $field);
            }
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/dv_gateway', $data));
    }

    /**
     * Validates the submitted settings form.
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/dv_gateway')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->request->post['payment_dv_gateway_merchant_url']) {
            $this->error['merchant_url'] = $this->language->get('error_merchant_url');
        }

        if (!$this->request->post['payment_dv_gateway_api_key']) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        if (!$this->request->post['payment_dv_gateway_api_secret']) {
            $this->error['api_secret'] = $this->language->get('error_api_secret');
        }

        return !$this->error;
    }

    /**
     * Runs when the plugin is installed.
     */
    public function install() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('payment_dv_gateway', ['payment_dv_gateway_order_status_id' => $this->config->get('config_order_status_id')]);
    }
}
