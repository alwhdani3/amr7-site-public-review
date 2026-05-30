<?php

namespace App\Services\Payments\Gateways;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Exceptions\PaymentGatewayException;

/**
 * Base class for any future external Gateway. Holds the bits that every
 * provider implementation will need (the provider key, a default
 * isOperational of false). Concrete providers override charge() and
 * handleWebhook().
 *
 * Currently no external provider extends this — they all stay null in
 * config/payments.php "drivers" until their implementation lands.
 */
abstract class AbstractGateway implements PaymentGatewayInterface
{
    /**
     * Concrete subclasses MUST set this to the short provider key
     * matching config/payments.php (e.g. "moyasar").
     */
    protected string $key;

    public function name(): string
    {
        return $this->key;
    }

    public function isOperational(): bool
    {
        return false;
    }

    public function charge(Invoice $invoice, array $context = []): array
    {
        throw PaymentGatewayException::notImplemented($this->name());
    }

    public function handleWebhook(array $payload, array $headers = []): ?Payment
    {
        throw PaymentGatewayException::notImplemented($this->name());
    }
}
