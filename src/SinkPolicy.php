<?php

namespace Kanvigo\Audit\Contracts;

/**
 * How a sink is dispatched and what its failure means. Only three combinations
 * are valid — fail-closed implies synchronous, pre-commit execution (a queued
 * sink runs after the action committed, so it can no longer abort anything) —
 * which the named constructors enforce.
 */
final readonly class SinkPolicy
{
    private function __construct(
        public DispatchMode $dispatch,
        public FailureMode $failure,
    ) {}

    /**
     * Inline after the surrounding transaction commits; failures are isolated.
     */
    public static function sync(): self
    {
        return new self(DispatchMode::Sync, FailureMode::FailOpen);
    }

    /**
     * Shipped by the outbox drain worker (at-least-once); failures are retried.
     */
    public static function queued(): self
    {
        return new self(DispatchMode::Queued, FailureMode::FailOpen);
    }

    /**
     * Synchronous, pre-commit, inside the domain transaction; a failure rolls
     * the action back.
     */
    public static function failClosed(): self
    {
        return new self(DispatchMode::Sync, FailureMode::FailClosed);
    }

    public function isQueued(): bool
    {
        return $this->dispatch === DispatchMode::Queued;
    }

    public function isFailClosed(): bool
    {
        return $this->failure === FailureMode::FailClosed;
    }
}
