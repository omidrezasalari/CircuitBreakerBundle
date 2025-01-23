<?php

namespace Omidrezasalari\CircuitBreakerBundle\Service;

use Redis;

class RedisStorage implements StorageInterface
{
    private Redis $redis;

    /**
     * @throws \RedisException
     */
    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    /**
     * @throws \RedisException
     */
    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    /**
     * @throws \RedisException
     */
    public function set(string $key, mixed $value, int $ttl = 0): bool
    {
        if ($ttl > 0) {
            return $this->redis->set($key, $value, $ttl);
        }
        return $this->redis->set($key, $value);
    }

    /**
     * @throws \RedisException
     */
    public function increment(string $key): int
    {
        return $this->redis->incr($key);
    }

    /**
     * @throws \RedisException
     */
    public function expire(string $key, int $ttl): bool
    {
        return $this->redis->expire($key, $ttl);
    }
}
