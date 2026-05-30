<?php

namespace App\Services\Payments\Exceptions;

use RuntimeException;

class PaymentGatewayException extends RuntimeException
{
    public static function disabled(): self
    {
        return new self('Payment gateway is disabled.');
    }

    public static function unknownProvider(string $name): self
    {
        return new self("Unknown payment provider: {$name}.");
    }

    public static function notImplemented(string $name): self
    {
        return new self("Payment provider \"{$name}\" is not implemented yet.");
    }

    public static function webhookDisabled(string $provider): self
    {
        return new self("Webhook for provider \"{$provider}\" is disabled.");
    }
}
