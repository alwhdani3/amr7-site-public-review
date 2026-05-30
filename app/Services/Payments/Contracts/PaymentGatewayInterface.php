<?php

namespace App\Services\Payments\Contracts;

use App\Models\Invoice;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Short, stable identifier matching the config/payments.php drivers
     * registry key (e.g. "manual", "moyasar").
     */
    public function name(): string;

    /**
     * Whether this driver is wired to actually move money. Manual is
     * always "true" because it just records what the operator entered;
     * external providers return false until their credentials + Gateway
     * implementation land.
     */
    public function isOperational(): bool;

    /**
     * Initialise a payment for the given invoice and return whatever the
     * caller needs to continue the flow (a redirect URL for hosted
     * checkout, a client token for an SDK, or an immediate confirmation
     * for the manual driver).
     *
     * @param  array<string,mixed>  $context  Optional extra fields
     *                                        (operator notes, bank ref).
     * @return array<string,mixed>
     */
    public function charge(Invoice $invoice, array $context = []): array;

    /**
     * Parse + validate an incoming webhook payload from this provider
     * and update the matching Payment row. Manual driver returns false
     * (it has no webhook surface).
     *
     * @param  array<string,mixed>  $payload
     * @param  array<string,string>  $headers
     */
    public function handleWebhook(array $payload, array $headers = []): ?Payment;
}
