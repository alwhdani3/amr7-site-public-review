<?php

namespace App\Services\Payments\Gateways;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Manual gateway — records bank transfer / cash payments via the
 * backoffice. Does NOT call any external API. The only "real" driver in
 * Phase D.
 *
 * The Filament PaymentResource creates Payment rows directly today;
 * this class exposes the same operation behind the PaymentGateway
 * abstraction so future code (API, automation) can use one entry point.
 */
class ManualGateway implements PaymentGatewayInterface
{
    public function name(): string
    {
        return 'manual';
    }

    public function isOperational(): bool
    {
        return true;
    }

    public function charge(Invoice $invoice, array $context = []): array
    {
        if (! Schema::hasTable('payments')) {
            return [
                'ok' => false,
                'reason' => 'payments_table_missing',
            ];
        }

        $amount = isset($context['amount']) ? (float) $context['amount'] : (float) $invoice->total;
        $currency = (string) ($context['currency'] ?? config('payments.currency', 'SAR'));

        $payment = new Payment();
        $payment->forceFill([
            'public_id' => (string) Str::uuid(),
            'invoice_id' => $invoice->getKey(),
            'recorded_by' => $context['recorded_by'] ?? null,
            'provider' => 'manual',
            'provider_reference' => $context['reference'] ?? null,
            'amount' => $amount,
            'currency' => $currency,
            'status' => $context['status'] ?? 'pending',
            'paid_at' => $context['paid_at'] ?? null,
            'notes' => $context['notes'] ?? null,
        ])->save();

        return [
            'ok' => true,
            'payment_id' => $payment->getKey(),
            'public_id' => $payment->public_id,
            'status' => $payment->status,
        ];
    }

    public function handleWebhook(array $payload, array $headers = []): ?Payment
    {
        return null;
    }
}
