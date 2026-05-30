<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Exceptions\PaymentGatewayException;
use App\Services\Payments\Gateways\ManualGateway;
use Illuminate\Contracts\Container\Container;

/**
 * Resolves a PaymentGateway driver by name.
 *
 * Behaviour:
 *   - When config('payments.enabled') is false → only the "manual"
 *     driver resolves; every other request throws disabled().
 *   - When enabled=true but the registry entry is null → throws
 *     notImplemented(). Prevents accidentally hitting a half-built
 *     provider.
 */
class PaymentGatewayManager
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    protected array $instances = [];

    public function __construct(protected Container $container)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) config('payments.enabled', false);
    }

    public function defaultDriver(): string
    {
        return (string) config('payments.default', 'manual');
    }

    public function driver(?string $name = null): PaymentGatewayInterface
    {
        $name = $name ?: $this->defaultDriver();

        if (! $this->isEnabled() && $name !== 'manual') {
            throw PaymentGatewayException::disabled();
        }

        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        $registry = (array) config('payments.drivers', []);

        if (! array_key_exists($name, $registry)) {
            throw PaymentGatewayException::unknownProvider($name);
        }

        $class = $registry[$name];

        if ($class === null) {
            throw PaymentGatewayException::notImplemented($name);
        }

        $instance = $this->container->make($class);

        if (! $instance instanceof PaymentGatewayInterface) {
            throw PaymentGatewayException::unknownProvider($name);
        }

        return $this->instances[$name] = $instance;
    }

    public function manual(): ManualGateway
    {
        return $this->container->make(ManualGateway::class);
    }
}
