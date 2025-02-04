<?php

namespace Omidrezasalari\CircuitBreakerBundle\Service;

class ApcuStorage implements StorageInterface
{
    public function get(string $key): ?string
    {
        return apcu_exists($key) ? apcu_fetch($key) : null;
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        return apcu_store($key, $value, $ttl);
    }

    public function increment(string $key): int
    {
        return apcu_inc($key);
    }

    public function expire(string $key, int $ttl): bool
    {
        return apcu_store($key, $this->get($key), $ttl);
    }
}
