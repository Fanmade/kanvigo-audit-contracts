<?php

namespace Kanvigo\Audit\Contracts;

/**
 * When a sink's {@see AuditSink::record()} runs relative to the domain action.
 */
enum DispatchMode: string
{
    /** Inline in the emitting process (fail-open: after commit; fail-closed: pre-commit, in the domain transaction). */
    case Sync = 'sync';

    /** Shipped by the outbox drain worker after the action committed. */
    case Queued = 'queued';
}
