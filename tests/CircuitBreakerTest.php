<?php

namespace Omidrezasalari\CircuitBreakerBundle\Tests;

use Omidrezasalari\CircuitBreakerBundle\Service\CircuitBreaker;
use Omidrezasalari\CircuitBreakerBundle\Service\StorageInterface;
use PHPUnit\Framework\TestCase;

class CircuitBreakerTest extends TestCase
{
    private CircuitBreaker $circuitBreaker;
    private StorageInterface $storage;
    private string $serviceName = "underTest_service";

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->circuitBreaker = new CircuitBreaker($this->storage, 3, 60);
    }

    public function testIsOpenReturnsFalseWhenStateIsClosed(): void
    {
        $this->storage->method('get')
            ->with(CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE)
            ->willReturn(CircuitBreaker::STATUS_CLOSED);

        $result = $this->circuitBreaker->isOpen($this->serviceName);

        $this->assertFalse($result, 'The circuit breaker should not be open when the state is "closed".');
    }

    public function testIsOpenReturnsTrueWhenStateIsOpenAndTimeoutNotExceeded(): void
    {
        $this->storage->method('get')
            ->willReturnMap([
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE, CircuitBreaker::STATUS_OPEN],
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::LAST_OPEND, time() - 30],
            ]);

        $result = $this->circuitBreaker->isOpen($this->serviceName);

        $this->assertTrue($result, 'The circuit breaker should remain open when the timeout period has not been exceeded.');
    }

    public function testIsOpenReturnsFalseWhenStateIsOpenAndTimeoutExceeded(): void
    {
        $this->storage->method('get')
            ->willReturnMap([
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE, CircuitBreaker::STATUS_OPEN],
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::LAST_OPEND, time() - 120],
            ]);

        $this->storage->expects($this->once())
            ->method('set')
            ->with(CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE, CircuitBreaker::STATUS_HALF_OPEN);

        $result = $this->circuitBreaker->isOpen($this->serviceName);

        $this->assertFalse($result, 'The circuit breaker should transition to half-open state after the timeout period.');
    }

    public function testAttemptSuccessClosesCircuit(): void
    {
        $this->storage->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE, CircuitBreaker::STATUS_CLOSED],
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::FAILURES, 0]
            );

        $this->circuitBreaker->attemptSuccess($this->serviceName);
    }

    public function testAttemptFailureIncrementsFailuresAndOpensCircuit(): void
    {
        $this->storage->method('increment')
            ->with(CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::FAILURES)
            ->willReturn(3);

        $this->storage->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::STATE, CircuitBreaker::STATUS_OPEN],
                [CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::LAST_OPEND, $this->greaterThan(0)]
            );

        $this->circuitBreaker->attemptFailure($this->serviceName);
    }

    public function testAttemptFailureDoesNotOpenCircuitWhenBelowThreshold(): void
    {
        $this->storage->method('increment')
            ->with(CircuitBreaker::PREFIX . ":$this->serviceName:" . CircuitBreaker::FAILURES)
            ->willReturn(2);

        $this->storage->expects($this->never())
            ->method('set');

        $this->circuitBreaker->attemptFailure($this->serviceName);
    }
}
