
# Circuit Breaker Bundle
This package is a **Symfony bundle** that implements the Circuit Breaker pattern. It helps protect your services from repeated requests when failures occur. The package is designed to allow you to easily change the storage mechanism (e.g., Redis, APCu, or custom implementations).

## Features
- **Circuit Breaker Pattern:** Prevents repeated requests after multiple failures.
- **Flexible Storage:** By default, it uses APCu, but you can switch to Redis or any custom storage implementation.
- **Configurable:** Dynamically configure failure thresholds and timeout periods via Symfony configuration files.
- **Symfony 6+ Compatible:** Integrates seamlessly with modern Symfony projects.

---

## Installation

### 1. APCu Implementation (Optimized and Persistent in Memory)

APCu is an internal PHP cache that stores data in shared memory and can be accessed across different requests on a server.
#### 1.1 Install and Enable APCu

Before using APCu as a storage mechanism for the Circuit Breaker bundle, ensure that APCu is installed and enabled on your server.

**1.2 Check if APCu is installed:**

```bash
php -m | grep apcu
````

#### If it's not installed, you can install it with the following commands:
* For Linux (Ubuntu/Debian):
````
sudo apt install php-apcu
````
* For macOS:
````
brew install php-apcu
````

#### 1.3 Enable APCu for CLI (Command Line Interface):

Edit your ``php.ini`` file to enable APCu for the CLI:
````ini
apc.enable_cli = 1
````

### 2. Install the package via Composer, run the following command:

```bash
composer require omidrezasalari/circuit-breaker-bundle
````

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
  Omidrezasalari\CircuitBreakerBundle\Service\RedisStorage:
    arguments:
      $host: '%redis_host%'
      $port: '%redis_port%'

  Omidrezasalari\CircuitBreakerBundle\Service\ApcuStorage: ~

  Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker:
    arguments:
      $failureTreshHold: '%circuit_breaker.failure_threshold%'
      $timeoutPeriod: '%circuit_breaker.timeout_period%'

```
### Package Configuration File (circuit_breaker.yaml):
This file is placed under the ```config/packages/``` directory and allows users to define custom configurations for the package. The file looks like this

```yaml
# config/packages/circuit_breaker.yaml
circuit_breaker:
  # By default, storage_service is set to ApcuStorage. You can change it to RedisStorage or any custom service.
  storage_service: 'Omidrezasalari\CircuitBreakerBundle\Service\ApcuStorage'
  failure_threshold: 5
  timeout_period: 60
```

### Configuration Details:
#### Storage Service:
By default, the storage_service is set to ```ApcuStorage```. If you prefer to use ```RedisStorage```, simply change this value in ```circuit_breaker.yaml```:
````yaml
circuit_breaker:
  storage_service: 'Omidrezasalari\CircuitBreakerBundle\Service\RedisStorage'
  failure_threshold: 5
  timeout_period: 60 
````
#### ```failure_threshold``` and ```timeout_period``` parameters:
These values control how many failures are allowed before the circuit breaker trips and for how long it stays open.
### Custom Storage

The bundle comes with a default `ApcuStorage` implementation. If you want to use a different storage solution, implement the `StorageInterface` and pass it to the `CircuitBreaker` service.

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