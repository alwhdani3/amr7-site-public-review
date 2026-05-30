<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(protected PaymentGatewayManager $manager)
    {
    }

    /**
     * Generic per-provider webhook endpoint. Stays 410 until BOTH the
     * gateway is enabled AND the provider's webhook flag is flipped on
     * the operator's host. Phase D never charges customers — this is
     * scaffolding for the upcoming provider work.
     */
    public function handle(Request $request, string $provider): JsonResponse
    {
        $provider = strtolower($provider);

        if (! $this->manager->isEnabled()) {
            return $this->disabled(__('payments_gateway_disabled'));
        }

        if (! (bool) config('payments.webhooks.enabled', false)) {
            return $this->disabled(__('payments_webhooks_disabled'));
        }

        $providerEnabled = (bool) config(
            "payments.webhooks.providers.{$provider}.enabled",
            false
        );

        if (! $providerEnabled) {
            return $this->disabled(__('payments_webhooks_disabled'));
        }

        // Secret MUST be configured when the webhook is live.
        // A missing secret with an enabled endpoint is a misconfiguration, not a
        // caller error — return 503 and log loudly so it gets noticed immediately.
        $secret = (string) config('payments.webhooks.shared_secret', '');
        if ($secret === '') {
            Log::error('Payment webhook is enabled but PAYMENT_WEBHOOK_SECRET is not set.', [
                'provider' => $provider,
                'ip'       => $request->ip(),
            ]);
            return response()->json([
                'ok'      => false,
                'message' => 'Webhook endpoint is not ready.',
            ], 503);
        }

        if (! $this->verifySignature($request, $secret)) {
            return response()->json(['ok' => false, 'message' => 'Invalid signature.'], 401);
        }

        if ($this->isDuplicate($request, $provider)) {
            return response()->json(['ok' => true, 'duplicate' => true]);
        }

        try {
            $payment = $this->manager->driver($provider)->handleWebhook(
                (array) $request->all(),
                $this->safeHeaders($request)
            );
        } catch (PaymentGatewayException $e) {
            return response()->json([
                'ok' => false,
                'message' => __('payments_gateway_not_implemented'),
            ], 501);
        }

        return response()->json([
            'ok' => true,
            'payment_public_id' => $payment?->public_id,
        ]);
    }

    /**
     * Verify HMAC-SHA256 of the raw body against the pre-validated $secret.
     * Header: x-amr7-payment-signature (provider-agnostic; each future driver
     * populates this from its own field before forwarding to handleWebhook).
     * No provider-specific timestamp header exists yet — all drivers are
     * unimplemented stubs. Replay protection relies on isDuplicate() below.
     */
    protected function verifySignature(Request $request, string $secret): bool
    {
        $signature = (string) $request->header('x-amr7-payment-signature', '');
        if ($signature === '') {
            return false;
        }

        return hash_equals(
            hash_hmac('sha256', $request->getContent(), $secret),
            $signature
        );
    }

    /**
     * Cache-based idempotency guard (TTL: 10 minutes).
     * Key = SHA-256(provider + raw body). This is a first-line defense only —
     * it is NOT a replacement for provider-supplied event IDs. When a real
     * payment provider is wired up its Gateway implementation should persist
     * the provider's unique event ID to avoid cross-restart replay gaps.
     */
    protected function isDuplicate(Request $request, string $provider): bool
    {
        $key = 'wh_idem_' . hash('sha256', $provider . $request->getContent());

        if (cache()->has($key)) {
            return true;
        }

        cache()->put($key, 1, now()->addMinutes(10));
        return false;
    }

    /**
     * @return array<string,string>
     */
    protected function safeHeaders(Request $request): array
    {
        $allow = [
            'x-amr7-payment-signature',
            'x-amr7-payment-provider',
            'content-type',
        ];

        $out = [];
        foreach ($allow as $h) {
            $value = $request->headers->get($h);
            if (is_string($value) && $value !== '') {
                $out[$h] = substr($value, 0, 512);
            }
        }

        return $out;
    }

    protected function disabled(string $message): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'disabled' => true,
            'message' => $message,
        ], 410);
    }
}
