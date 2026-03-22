<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/payment_gateway.php';
require_once __DIR__ . '/payment_providers/mercadopago_gateway.php';
require_once __DIR__ . '/payment_providers/manual_pix_gateway.php';

class PaymentEngine
{
    public function getEnabledProviders(): array
    {
        $raw = trim((string)getSetting('payment_provider_modules', 'mercadopago,manual_pix'));
        if ($raw === '') {
            return [];
        }
        $providers = array_filter(array_map('trim', explode(',', $raw)));
        $providers = array_values(array_unique($providers));
        
        $validProviders = array_filter($providers, fn($provider) => in_array($provider, ['mercadopago', 'manual_pix'], true));
        
        // Se a moeda não for BRL, desabilitar gateways exclusivos de BRL
        $storeCurrency = strtoupper(getSetting('store_currency', 'BRL'));
        if ($storeCurrency !== 'BRL') {
            $validProviders = array_filter($validProviders, fn($provider) => !in_array($provider, ['mercadopago', 'manual_pix'], true));
        }
        
        return array_values($validProviders);
    }

    public function isProviderEnabled(string $provider): bool
    {
        return in_array($provider, $this->getEnabledProviders(), true);
    }

    public function hasEnabledProviders(): bool
    {
        return count($this->getEnabledProviders()) > 0;
    }

    public function getActiveProvider(): string
    {
        $provider = trim((string)getSetting('payment_provider_active', 'mercadopago'));
        if ($provider === '') {
            return 'mercadopago';
        }
        return $provider;
    }

    public function getCheckoutProvider(): ?string
    {
        $activeProvider = $this->getActiveProvider();
        if ($this->isProviderEnabled($activeProvider)) {
            return $activeProvider;
        }
        $enabledProviders = $this->getEnabledProviders();
        if (empty($enabledProviders)) {
            return null;
        }
        return $enabledProviders[0];
    }

    public function createPixCharge(array $orderData): array
    {
        $provider = $this->getCheckoutProvider();
        if ($provider === null) {
            return ['success' => false, 'error' => 'No enabled payment provider found.'];
        }
        $gateway = $this->buildGateway($provider);
        if ($gateway === null) {
            return ['success' => false, 'error' => 'Payment provider not available.'];
        }
        return $gateway->createPixCharge($orderData);
    }

    public function parseWebhook(string $provider, string $payload, array $headers): array
    {
        $gateway = $this->buildGateway($provider);
        if ($gateway === null) {
            return ['success' => false, 'error' => 'Provider not configured'];
        }
        if (!$gateway->verifyWebhookSignature($payload, $headers)) {
            return ['success' => false, 'error' => 'Invalid webhook signature'];
        }
        return $gateway->parseWebhook($payload, $headers);
    }

    private function buildGateway(string $provider): ?PaymentGatewayInterface
    {
        return match ($provider) {
            'mercadopago' => new MercadoPagoGateway([
                'access_token' => getSetting('payment_mercadopago_access_token', ''),
                'webhook_secret' => getSetting('payment_mercadopago_webhook_secret', ''),
                'environment' => getSetting('payment_mercadopago_environment', 'sandbox')
            ]),
            'manual_pix' => new ManualPixGateway([
                'pix_key' => getSetting('payment_manual_pix_key', ''),
                'recipient_name' => getSetting('payment_manual_pix_recipient_name', '')
            ]),
            default => null
        };
    }
}
