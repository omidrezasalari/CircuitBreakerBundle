<?php

namespace Omidrezasalari\CircuitBreakerBundle\Service;

interface StorageInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 0): bool;
    public function increment(string $key): int;
    public function expire(string $key, int $ttl): bool;
}