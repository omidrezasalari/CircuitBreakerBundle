<?php

namespace Omidrezasalari\CircuitBreakerBundle\Service;

use Predis\Client;

class RedisStorage implements StorageInterface
{
    private Client $redis;

    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
        ]);
    }

    public function get(string $key): ?string
    {
        return $this->redis->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if ($ttl > 0) {
            return (bool) $this->redis->setex($key, $ttl, $value);
        }
        return (bool) $this->redis->set($key, $value);
    }

    public function increment(string $key): int
    {
        return $this->redis->incr($key);
    }


    public function expire(string $key, int $ttl): bool
    {
        return (bool) $this->redis->expire($key, $ttl);
    }
}
