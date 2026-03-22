<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../payment_gateway.php';

class ManualPixGateway implements PaymentGatewayInterface
{
    private string $pixKey;
    private string $recipientName;

    public function __construct(array $config)
    {
        $this->pixKey = trim((string)($config['pix_key'] ?? ''));
        $this->recipientName = trim((string)($config['recipient_name'] ?? 'Loja'));
    }

    public function getProviderName(): string
    {
        return 'manual_pix';
    }

    public function createPixCharge(array $orderData): array
    {
        if ($this->pixKey === '') {
            return [
                'success' => false,
                'error' => 'PIX manual key is not configured.'
            ];
        }

        $amount = number_format((float)($orderData['total'] ?? 0), 2, '.', '');
        $orderId = (int)($orderData['order_id'] ?? 0);
        $txid = 'MANUAL' . $orderId . time();
        
        $brCode = $this->generateStaticPixPayload($this->pixKey, $this->recipientName, 'SAO PAULO', $amount, $txid);
        
        $qrOptions = new \chillerlan\QRCode\QROptions([
            'scale' => 5,
            'eccLevel' => \chillerlan\QRCode\Common\EccLevel::M,
            'addQuietzone' => true,
        ]);
        $qrCodeUrl = (new \chillerlan\QRCode\QRCode($qrOptions))->render($brCode);

        return [
            'success' => true,
            'provider' => $this->getProviderName(),
            'transaction_id' => $txid,
            'reference' => 'order_' . $orderId,
            'status' => 'pending',
            'currency' => 'BRL',
            'pix_qr_code' => $qrCodeUrl,
            'pix_copy_paste' => $brCode,
            'pix_expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes')),
            'payload' => [
                'recipient_name' => $this->recipientName,
                'manual' => true,
                'pix_key' => $this->pixKey
            ]
        ];
    }

    private function generateStaticPixPayload(string $pixKey, string $merchantName, string $city, string $amount, string $txid): string
    {
        $merchantName = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $this->removeAccents($merchantName)), 0, 25);
        $city = substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $this->removeAccents($city)), 0, 15);
        $merchantName = strtoupper($merchantName ?: 'LOJA');
        $city = strtoupper($city ?: 'SAO PAULO');
        
        $gui = "0014br.gov.bcb.pix01" . str_pad((string)strlen($pixKey), 2, '0', STR_PAD_LEFT) . $pixKey;
        $mai = "26" . str_pad((string)strlen($gui), 2, '0', STR_PAD_LEFT) . $gui;
        
        $txidField = "05" . str_pad((string)strlen($txid), 2, '0', STR_PAD_LEFT) . $txid;
        $additionalData = "62" . str_pad((string)strlen($txidField), 2, '0', STR_PAD_LEFT) . $txidField;

        $payload = "000201" .
                   "010211" .
                   $mai .
                   "52040000" .
                   "5303986" .
                   "54" . str_pad((string)strlen($amount), 2, '0', STR_PAD_LEFT) . $amount .
                   "5802BR" .
                   "59" . str_pad((string)strlen($merchantName), 2, '0', STR_PAD_LEFT) . $merchantName .
                   "60" . str_pad((string)strlen($city), 2, '0', STR_PAD_LEFT) . $city .
                   $additionalData .
                   "6304";
                   
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($payload); $i++) {
            $crc ^= (ord($payload[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }
        $crc = $crc & 0xFFFF;
        return $payload . strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
    
    private function removeAccents(string $string): string
    {
        $map = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ñ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y'
        ];
        return strtr($string, $map);
    }

    public function verifyWebhookSignature(string $payload, array $headers): bool
    {
        return true;
    }

    public function parseWebhook(string $payload, array $headers): array
    {
        $data = json_decode($payload, true);
        if (!is_array($data)) {
            return ['success' => false, 'error' => 'Invalid payload'];
        }

        return [
            'success' => true,
            'event_id' => (string)($data['event_id'] ?? ('manual_' . md5($payload))),
            'status' => (string)($data['status'] ?? ''),
            'transaction_id' => (string)($data['transaction_id'] ?? ''),
            'reference' => (string)($data['reference'] ?? ''),
            'raw' => $data
        ];
    }
}
