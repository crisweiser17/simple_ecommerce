<?php

interface PaymentGatewayInterface
{
    public function getProviderName(): string;
    public function createPixCharge(array $orderData): array;
    public function verifyWebhookSignature(string $payload, array $headers): bool;
    public function parseWebhook(string $payload, array $headers): array;
}
