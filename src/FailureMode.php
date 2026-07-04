<?php

namespace Kanvigo\Audit\Contracts;

/**
 * What a sink failure means for the audited action.
 */
enum FailureMode: string
{
    /** Best effort: a failing sink is reported and isolated; the action proceeds. */
    case FailOpen = 'fail-open';

    /** Compliance grade: a failing sink aborts the action (no guaranteed record → no action). */
    case FailClosed = 'fail-closed';
}
