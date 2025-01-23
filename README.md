
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
    Omidrezasalari\CircuitBreakerBundle\CircuitBreakerBundle::class => ['all' => true],
];
```

---

## Configuration

The circuit breaker bundle uses a configurable failure threshold and timeout period. You can configure these parameters in your `config/services.yaml`.

```yaml
#.env
REDIS_HOST=localhost
REDIS_PORT=6379
FAILURE_THRESHOLD=5
TIMEOUT_PERIOD=60
```


```yaml
# config/services.yaml
parameters:
  redis_host: '%env(REDIS_HOST)%'
  redis_port: '%env(REDIS_PORT)%'
  circuit_breaker.failure_threshold: '%env(FAILURE_THRESHOLD)%'
  circuit_breaker.timeout_period: '%env(TIMEOUT_PERIOD)%'

services:
  Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker:
    arguments:
      $storage: '@App\Service\RedisStorage'
      $failureTreshHold: '%circuit_breaker.failure_threshold%'
      $timeoutPeriod: '%circuit_breaker.timeout_period%'
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

class YourService
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

## Running Tests

To run the tests for the bundle, you can use PHPUnit:

```bash
vendor/bin/phpunit 
```

---

## Contributing

If you'd like to contribute to the development of this bundle, feel free to fork the repository, create a branch, and submit a pull request. Please ensure your code follows the coding standards and includes tests for any new features or bug fixes.

---

## License

This bundle is open-source and available under the [MIT License](LICENSE).

---