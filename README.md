
# Circuit Breaker Bundle

This is a Symfony bundle that implements a circuit breaker pattern with Redis as the storage mechanism. It provides an easy way to protect services by preventing repeated failures and can be easily swapped with another storage technology.

## Features

- **Circuit Breaker Pattern:** Protects services by stopping repeated failed attempts after a set number of failures.
- **Redis Storage:** Uses Redis to store state information for the circuit breaker. Can be replaced with another storage mechanism via an interface.
- **Customizable:** Configure failure thresholds and timeout periods.

---

## Installation

You can install the `CircuitBreakerBundle` in your Symfony project using Composer.

```bash
composer require omidrezasalari/circuit-breaker-bundle
```

After installing, register the bundle in your `config/bundles.php`:

```php
return [
    // Other bundles...
    Omidrezasalari\CircuitBreakerBundle\OmidrezasalariCircuitBreakerBundle::class => ['all' => true],
];
```

---

## Configuration

The circuit breaker bundle uses a configurable failure threshold and timeout period. You can configure these parameters in your `config/services.yaml`.

```yaml
# config/services.yaml
parameters:
    circuit_breaker.failure_threshold: 5
    circuit_breaker.timeout_period: 60

services:
    Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker:
        arguments:
            $failureTreshHold: '%circuit_breaker.failure_threshold%'
            $timeoutPeriod: '%circuit_breaker.timeout_period%'
            $storage: '@App\Service\RedisStorage'
```

### Custom Storage

The bundle comes with a default `RedisStorage` implementation. If you want to use a different storage solution, implement the `StorageInterface` and pass it to the `CircuitBreaker` service.

```php
namespace App\Service;

use Omidrezasalari\CircuitBreakerBundle\Service\StorageInterface;

class CustomStorage implements StorageInterface
{
    // Implement required methods: get, set, increment, expire
}
```

---

## Usage

Here’s an example of how to use the CircuitBreaker service:

```php
use Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker;

class SomeService
{
    private CircuitBreaker $circuitBreaker;

    public function __construct(CircuitBreaker $circuitBreaker)
    {
        $this->circuitBreaker = $circuitBreaker;
    }

    public function someOperation()
    {
        if ($this->circuitBreaker->isOpen('your_service')) {
            throw new \Exception("Circuit breaker is open. Operation not allowed.");
        }

        try {
            // Perform the operation
        } catch (\Exception $e) {
            $this->circuitBreaker->attemptFailure('your_service');
            throw $e;
        }

        // If operation succeeds
        $this->circuitBreaker->attemptSuccess('your_service');
    }
}
```

---

## API

### `isOpen(string $serviceName): bool`

Checks whether the circuit breaker for the given service is open. Returns `true` if open, `false` if not.

### `attemptSuccess(string $serviceName): void`

Called when a service operation is successful. Resets the failure count and closes the circuit breaker.

### `attemptFailure(string $serviceName): void`

Called when a service operation fails. Increments the failure count. If the failure threshold is reached, the circuit breaker will open.

---

## Redis Storage

By default, the bundle uses `RedisStorage` for storing the state of the circuit breaker in Redis. Here's a quick overview of how `RedisStorage` works:

```php
namespace Omidrezasalari\CircuitBreakerBundle\Service;

class RedisStorage implements StorageInterface
{
    private Redis $redis;

    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return $ttl > 0 ? $this->redis->set($key, $value, $ttl) : $this->redis->set($key, $value);
    }

    public function increment(string $key): int
    {
        return $this->redis->incr($key);
    }

    public function expire(string $key, int $ttl): bool
    {
        return $this->redis->expire($key, $ttl);
    }
}
```

---

## Running Tests

To run the tests for the bundle, you can use PHPUnit:

```bash
phpunit --bootstrap=vendor/autoload.php tests
```

---

## Contributing

If you'd like to contribute to the development of this bundle, feel free to fork the repository, create a branch, and submit a pull request. Please ensure your code follows the coding standards and includes tests for any new features or bug fixes.

---

## License

This bundle is open-source and available under the [MIT License](LICENSE).

---