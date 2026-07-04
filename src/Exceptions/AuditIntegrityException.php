<?php

namespace Kanvigo\Audit\Contracts\Exceptions;

use RuntimeException;

/**
 * Thrown when the audit layer's integrity guarantee cannot be honored — e.g.
 * a fail-closed sink would accept an event but the audited mutation is not
 * running inside a database transaction, so a sink failure could no longer
 * abort it. The runtime embodiment of "no guaranteed record → no action".
 */
class AuditIntegrityException extends RuntimeException {}
