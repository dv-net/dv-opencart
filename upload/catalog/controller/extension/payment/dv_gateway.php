<?php
// Autoload DV.net client library
require_once(DIR_SYSTEM . 'library/dvnet/startup.php');
require_once(DIR_SYSTEM . 'library/dvnet/vendor/autoload.php');

class ControllerExtensionPaymentDvGateway extends Controller
{
    /**
     * Renders the payment method on the checkout page.
     */
    public function index()
    {
        $this->load->language('extension/payment/dv_gateway');
        $data['button_confirm'] = $this->language->get('button_confirm');
        return $this->load->view('extension/payment/dv_gateway', $data);
    }

    /**
     * Called when the user confirms the order.
     * Creates the payment request and returns a redirect URL.
     */
    public function confirm()
    {
        $json = array();

        if ($this->session->data['payment_method']['code'] == 'dv_gateway') {
            $this->load->model('checkout/order');
            $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $merchantUrl = $this->config->get('payment_dv_gateway_merchant_url');
            $apiKey = $this->config->get('payment_dv_gateway_api_key');

            if (empty($merchantUrl) || empty($apiKey)) {
                $json['error'] = 'Payment gateway is not configured. Please contact support.';
            } else {
                try {
                    $client = new \DvNet\DvNetClient\MerchantClient(
                        new \DvNet\DvNetClient\SimpleHttpClient(),
                        new \DvNet\DvNetClient\MerchantMapper(),
                        $merchantUrl,
                        $apiKey
                    );

                    $result = $client->getExternalWallet(
                        storeExternalId: (string)$order_info['order_id'],
                        email: $order_info['email'],
                        amount: $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false),
                        currency: $order_info['currency_code']
                    );

                    if (isset($result->payUrl)) {
                        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('config_order_status_id'), 'Redirected to DV.net for payment.');
                        $json['redirect'] = $result->payUrl;
                    } else {
                        $json['error'] = 'Could not retrieve payment URL from DV.net.';
                    }
                } catch (\Exception $e) {
                    $json['error'] = 'Payment error: ' . $e->getMessage();
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Handles incoming webhooks from DV.net.
     */
    public function callback() {
        $payload = file_get_contents('php://input');
        $signature = isset($_SERVER['HTTP_X_SIGN']) ? $_SERVER['HTTP_X_SIGN'] : '';
        $apiSecret = $this->config->get('payment_dv_gateway_api_secret');

        if (!$payload || !$signature || !$apiSecret) {
            $this->response->addHeader('HTTP/1.1 400 Bad Request');
            $this->response->setOutput('Missing payload, signature, or API secret.');
            return;
        }

        try {
            $hashChecker = new \DvNet\DvNetClient\MerchantUtilsManager();
            $isValid = $hashChecker->checkSign(
                clientSignature: $signature,
                clientKey: $apiSecret,
                requestBody: json_decode($payload)
            );

            if (!$isValid) {
                $this->response->addHeader('HTTP/1.1 403 Forbidden');
                $this->response->setOutput('Invalid signature.');
                return;
            }

            $webhookMapper = new \DvNet\DvNetClient\WebhookMapper();
            $webhookData = $webhookMapper->mapWebhook(json_decode($payload, true));

            if ($webhookData instanceof \DvNet\DvNetClient\Dto\WebhookMapper\ConfirmedWebhookResponse) {
                $order_id = $webhookData->wallet->storeExternalId;

                $this->load->model('checkout/order');
                $order_info = $this->model_checkout_order->getOrder($order_id);

                if ($order_info) {
                    $order_status_id = $this->config->get('payment_dv_gateway_order_status_id');
                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, 'Payment confirmed by DV.net webhook.', true);
                }
            }

            $this->response->addHeader('HTTP/1.1 200 OK');
            $this->response->setOutput('Webhook processed.');

        } catch (\Exception $e) {
            // Log error
            $this->log->write('DV.net Webhook Error: ' . $e->getMessage());
            $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
            $this->response->setOutput('Webhook processing error: ' . $e->getMessage());
        }
    }
}
