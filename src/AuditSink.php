<?php

namespace Kanvigo\Audit\Contracts;

/**
 * A destination for audit events. Implement this against the versioned
 * contracts package, register the class with the emitting application, and
 * every event it accepts will be delivered according to its policy.
 */
interface AuditSink
{
    /**
     * Whether this sink wants the given event. The taxonomy lives here: a
     * product feed accepts a narrow content subset, a compliance ledger
     * accepts everything. Only accepted events are delivered to record().
     */
    public function accepts(AuditEvent $event): bool;

    /**
     * Persist/ship the event. May throw; what a throw means is defined by
     * {@see policy()} — fail-open failures are isolated and reported,
     * fail-closed failures abort the audited action.
     */
    public function record(AuditEvent $event): void;

    /**
     * How this sink is dispatched and what its failure means.
     */
    public function policy(): SinkPolicy;
}
