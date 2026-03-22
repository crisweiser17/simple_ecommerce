<?php
require_once __DIR__ . '/../payment_gateway.php';

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private string $accessToken;
    private string $webhookSecret;
    private string $environment;

    public function __construct(array $config)
    {
        $this->accessToken = trim((string)($config['access_token'] ?? ''));
        $this->webhookSecret = trim((string)($config['webhook_secret'] ?? ''));
        $this->environment = trim((string)($config['environment'] ?? 'sandbox'));
    }

    public function getProviderName(): string
    {
        return 'mercadopago';
    }

    public function createPixCharge(array $orderData): array
    {
        if ($this->accessToken === '') {
            return ['success' => false, 'error' => 'Mercado Pago access token is not configured.'];
        }

        $externalReference = (string)($orderData['reference'] ?? ('order_' . ($orderData['order_id'] ?? '')));
        $amount = round((float)($orderData['total'] ?? 0), 2);
        $payerEmail = (string)($orderData['customer']['email'] ?? 'guest@example.com');
        $description = (string)($orderData['description'] ?? 'Pedido #' . ($orderData['order_id'] ?? ''));

        $requestBody = [
            'transaction_amount' => $amount,
            'description' => $description,
            'payment_method_id' => 'pix',
            'external_reference' => $externalReference,
            'payer' => [
                'email' => $payerEmail
            ]
        ];

        $response = $this->httpRequest(
            'POST',
            'https://api.mercadopago.com/v1/payments',
            $requestBody,
            [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . md5($externalReference . '|' . $amount)
            ]
        );

        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }

        $payload = $response['data'];
        $point = $payload['point_of_interaction']['transaction_data'] ?? [];

        return [
            'success' => true,
            'provider' => $this->getProviderName(),
            'transaction_id' => isset($payload['id']) ? (string)$payload['id'] : '',
            'reference' => $externalReference,
            'status' => $this->normalizeStatus((string)($payload['status'] ?? 'pending')),
            'currency' => (string)($payload['currency_id'] ?? 'BRL'),
            'pix_qr_code' => (string)($point['qr_code_base64'] ?? ''),
            'pix_copy_paste' => (string)($point['qr_code'] ?? ''),
            'pix_expires_at' => (string)($payload['date_of_expiration'] ?? ''),
            'payload' => $payload
        ];
    }

    public function verifyWebhookSignature(string $payload, array $headers): bool
    {
        if ($this->webhookSecret === '') {
            return true;
        }

        // Find signature header (case-insensitive)
        $signatureHeader = '';
        foreach ($headers as $key => $val) {
            if (strtolower($key) === 'x-signature') {
                $signatureHeader = $val;
                break;
            }
        }

        if ($signatureHeader === '') {
            return false;
        }

        // Find request-id header
        $requestId = '';
        foreach ($headers as $key => $val) {
            if (strtolower($key) === 'x-request-id') {
                $requestId = $val;
                break;
            }
        }

        // Parse x-signature: ts=...,v1=...
        $parts = explode(',', $signatureHeader);
        $ts = '';
        $v1 = '';
        foreach ($parts as $part) {
            $keyValue = explode('=', trim($part), 2);
            if (count($keyValue) === 2) {
                if ($keyValue[0] === 'ts') {
                    $ts = $keyValue[1];
                } elseif ($keyValue[0] === 'v1') {
                    $v1 = $keyValue[1];
                }
            }
        }

        if ($ts === '' || $v1 === '') {
            return false;
        }

        // Get data.id from URL parameters or payload
        $dataId = '';
        $data = json_decode($payload, true);
        if (isset($_GET['data_id'])) {
            $dataId = (string)$_GET['data_id'];
        } elseif (isset($_GET['data']['id'])) {
            $dataId = (string)$_GET['data']['id'];
        } elseif (isset($_GET['id'])) {
            $dataId = (string)$_GET['id'];
        } elseif (is_array($data) && isset($data['data']['id'])) {
            $dataId = (string)$data['data']['id'];
        } elseif (is_array($data) && isset($data['id'])) {
            $dataId = (string)$data['id'];
        }

        if ($dataId === '') {
            return false;
        }

        // Build manifest string according to Mercado Pago docs:
        // manifest = "id:{data.id};request-id:{x-request-id};ts:{ts};"
        $manifest = sprintf("id:%s;request-id:%s;ts:%s;", $dataId, $requestId, $ts);
        
        $calculated = hash_hmac('sha256', $manifest, $this->webhookSecret);
        return hash_equals($calculated, $v1);
    }

    public function parseWebhook(string $payload, array $headers): array
    {
        $data = json_decode($payload, true);
        if (!is_array($data)) {
            return ['success' => false, 'error' => 'Invalid payload'];
        }

        $eventId = (string)($data['id'] ?? '');
        $resource = (string)($data['data']['id'] ?? '');
        $action = (string)($data['action'] ?? '');

        if ($resource === '' && isset($data['type']) && $data['type'] === 'payment' && isset($data['id'])) {
            $resource = (string)$data['id'];
        }

        $paymentDetails = [];
        if ($resource !== '') {
            $detailsResponse = $this->httpRequest(
                'GET',
                'https://api.mercadopago.com/v1/payments/' . urlencode($resource),
                null,
                ['Authorization: Bearer ' . $this->accessToken]
            );
            if ($detailsResponse['success']) {
                $paymentDetails = $detailsResponse['data'];
            }
        }

        return [
            'success' => true,
            'event_id' => $eventId !== '' ? $eventId : ('mp_' . md5($payload)),
            'status' => $this->normalizeStatus((string)($paymentDetails['status'] ?? $action)),
            'transaction_id' => $resource !== '' ? $resource : ((string)($paymentDetails['id'] ?? '')),
            'reference' => (string)($paymentDetails['external_reference'] ?? ''),
            'raw' => [
                'webhook' => $data,
                'payment' => $paymentDetails
            ]
        ];
    }

    private function normalizeStatus(string $status): string
    {
        $status = strtolower($status);
        return match ($status) {
            'approved' => 'paid',
            'authorized' => 'authorized',
            'in_process', 'pending_waiting_transfer', 'pending' => 'pending',
            'rejected', 'cancelled', 'cancelled_by_user' => 'failed',
            default => $status === '' ? 'pending' : $status
        };
    }

    private function httpRequest(string $method, string $url, ?array $body, array $headers): array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            $opts = [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers
            ];
            if ($body !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($body, JSON_UNESCAPED_UNICODE);
            }
            curl_setopt_array($ch, $opts);
            $raw = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            // curl_close($ch); // Deprecated in PHP 8.0+
            if ($raw === false) {
                return ['success' => false, 'error' => $error !== '' ? $error : 'HTTP request failed'];
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $headers),
                    'content' => $body !== null ? json_encode($body, JSON_UNESCAPED_UNICODE) : ''
                ]
            ]);
            $raw = @file_get_contents($url, false, $context);
            $httpCode = 200;
            $responseHeaders = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?: []) : [];
            if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $m)) {
                $httpCode = (int)$m[1];
            }
            if ($raw === false) {
                return ['success' => false, 'error' => 'HTTP request failed'];
            }
        }

        $decoded = json_decode((string)$raw, true);
        if ($httpCode >= 400) {
            $msg = is_array($decoded) ? ($decoded['message'] ?? $decoded['error'] ?? 'Gateway error') : 'Gateway error';
            return ['success' => false, 'error' => 'Gateway HTTP ' . $httpCode . ': ' . $msg];
        }
        if (!is_array($decoded)) {
            return ['success' => false, 'error' => 'Invalid gateway response'];
        }
        return ['success' => true, 'data' => $decoded];
    }
}
