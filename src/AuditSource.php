<?php

namespace Kanvigo\Audit\Contracts;

/**
 * Where an audited action originated. A token name alone cannot distinguish
 * MCP from REST traffic, so the emitting application stamps the source
 * explicitly (e.g. via a route middleware marker).
 */
enum AuditSource: string
{
    /** A direct web-session (UI) action. */
    case Ui = 'ui';

    /** An action performed through the MCP server with a personal access token. */
    case Mcp = 'mcp';

    /** An action performed through the REST API with a personal access token. */
    case Api = 'api';

    /** An action performed inside a queued job. */
    case Queue = 'queue';

    /** A scheduled command or other system-initiated action (no request, often no actor). */
    case System = 'system';
}
