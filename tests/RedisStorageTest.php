<?php

namespace Omidrezasalari\CircuitBreakerBundle\Tests;

use Omidrezasalari\CircuitBreakerBundle\Service\RedisStorage;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;

class RedisStorageTest extends TestCase
{
    private RedisStorage $redisStorage;
    private $redisMock;

    /**
     * @throws RedisException
     */
    protected function setUp(): void
    {
        $this->redisMock = $this->createMock(Redis::class);
        $this->redisStorage = new RedisStorage('127.0.0.1', 6379);

    }

    /**
     * @throws RedisException
     */
    public function testGetReturnsValue(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->redisMock->expects($this->once())
            ->method('get')
            ->with($key)
            ->willReturn($value);

        $result = $this->redisStorage->get($key);

        $this->assertEquals($value, $result, 'The get method should return the correct value.');
    }

    public function testSetSetsValueWithNoTTL(): void
    {
        $key = 'test_key';
        $value = 'test_value';

        $this->redisMock->expects($this->once())
            ->method('set')
            ->with($key, $value)
            ->willReturn(true);

        $result = $this->redisStorage->set($key, $value);

        $this->assertTrue($result, 'The set method should return true when no TTL is provided.');
    }

    public function testSetSetsValueWithTTL(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 60;

        $this->redisMock->expects($this->once())
            ->method('set')
            ->with($key, $value, $ttl)
            ->willReturn(true);

        $result = $this->redisStorage->set($key, $value, $ttl);

        $this->assertTrue($result, 'The set method should return true when a TTL is provided.');
    }

    /**
     * @throws RedisException
     */
    public function testIncrementIncrementsValue(): void
    {
        $key = 'test_key';

        $this->redisMock->expects($this->once())
            ->method('incr')
            ->with($key)
            ->willReturn(1);

        $result = $this->redisStorage->increment($key);

        $this->assertEquals(1, $result, 'The increment method should return the incremented value.');
    }

    /**
     * @throws RedisException
     */
    public function testExpireSetsExpiration(): void
    {
        $key = 'test_key';
        $ttl = 60;

        $this->redisMock->expects($this->once())
            ->method('expire')
            ->with($key, $ttl)
            ->willReturn(true);

        $result = $this->redisStorage->expire($key, $ttl);

        $this->assertTrue($result, 'The expire method should return true when TTL is set.');
    }

    public function testExceptionThrownOnGetError(): void
    {
        $this->redisMock->expects($this->once())
            ->method('get')
            ->willThrowException(new RedisException('Error retrieving value'));

        $this->expectException(RedisException::class);
        $this->expectExceptionMessage('Error retrieving value');

        $this->redisStorage->get('test_key');
    }

    /**
     * @throws RedisException
     */
    public function testExceptionThrownOnSetError(): void
    {
        $this->redisMock->expects($this->once())
            ->method('set')
            ->willThrowException(new RedisException('Error setting value'));

        $this->expectException(RedisException::class);
        $this->expectExceptionMessage('Error setting value');

        $this->redisStorage->set('test_key', 'test_value');
    }

    /**
     * @throws RedisException
     */
    public function testExceptionThrownOnIncrementError(): void
    {
        $this->redisMock->expects($this->once())
            ->method('incr')
            ->willThrowException(new RedisException('Error incrementing value'));

        $this->expectException(RedisException::class);
        $this->expectExceptionMessage('Error incrementing value');

        $this->redisStorage->increment('test_key');
    }

    /**
     * @throws RedisException
     */
    public function testExceptionThrownOnExpireError(): void
    {
        $this->redisMock->expects($this->once())
            ->method('expire')
            ->willThrowException(new RedisException('Error setting expiration'));

        $this->expectException(RedisException::class);
        $this->expectExceptionMessage('Error setting expiration');

        $this->redisStorage->expire('test_key', 60);
    }
}
