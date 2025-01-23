<?php

namespace Omidrezasalari\CircuitBreakerBundle\Service;

class CircuitBreaker
{
    public const string STATUS_OPEN = "open";
    public const string STATUS_HALF_OPEN = "half-open";
    public const string STATUS_CLOSED = "closed";
    public const string LAST_OPEND = "lastOpened";
    public const string STATE = "state";
    public const string FAILURES = "failures";
    public const string PREFIX = "circuit";

    private int $failureTreshHold = 5;
    private int $timeoutPeriod = 60;
    private StorageInterface $storage;

    public function __construct(
        StorageInterface $storage,
        int              $failureTreshHold,
        int              $timeoutPeriod
    )
    {
        $this->failureTreshHold = $failureTreshHold;
        $this->timeoutPeriod = $timeoutPeriod;
        $this->storage = $storage;
    }

    public function isOpen(string $serviceName): bool
    {
        $state = $this->storage->get(self::PREFIX . ":$serviceName:" . self::STATE);

        if ($state === self::STATUS_OPEN) {
            $lastOpened = $this->storage->get(self::PREFIX . ":$serviceName:" . self::LAST_OPEND);

            if (time() - (int)$lastOpened > $this->timeoutPeriod) {
                $this->storage->set(self::PREFIX . ":$serviceName:" . self::STATE, self::STATUS_HALF_OPEN);

                return false;
            }

            return true;
        }

        return false;
    }

    public function attemptSuccess(string $serviceName): void
    {
        $this->storage->set(self::PREFIX . ":$serviceName:" . self::STATE, self::STATUS_CLOSED);
        $this->storage->set(self::PREFIX . ":$serviceName:" . self::FAILURES, 0);
    }

    public function attemptFailure(string $serviceName): void
    {
        $failures = $this->storage->increment(self::PREFIX . ":$serviceName:" . self::FAILURES);

        if ($failures >= $this->failureTreshHold) {
            $this->storage->set(self::PREFIX . ":$serviceName:" . self::STATE, self::STATUS_OPEN);
            $this->storage->set(self::PREFIX . ":$serviceName:" . self::LAST_OPEND, time());
        }
    }
}
