<?php

namespace Kanvigo\Audit\Contracts;

/**
 * The coarse taxonomy every audit event is filed under. Sinks filter on it in
 * {@see AuditSink::accepts()} — e.g. a product activity feed accepts only
 * Content events, while a compliance ledger accepts everything.
 *
 * Event → category mapping (the canonical table; extend additively):
 *
 * - Content:  domain/content mutations — created, status_changed, priority_changed,
 *             type_changed, parent_changed, assignee_changed, tags_changed,
 *             dependency_changed, commented, comment_deleted, attachment_added,
 *             attachment_removed, archived, unarchived, canceled, reopened,
 *             tag_renamed, tag_recolored, tag_deleted, tag_merged.
 * - Authn:    authentication lifecycle — login, logout, failed login, password
 *             reset, 2FA changes.
 * - Authz:    authorization changes — membership, role and permission changes.
 * - Token:    API/MCP token lifecycle — created, revoked, expired.
 * - Security: everything security-relevant that fits none of the above —
 *             lockouts, invitation misuse, integrity alerts.
 */
enum AuditCategory: string
{
    case Content = 'content';
    case Authn = 'authn';
    case Authz = 'authz';
    case Token = 'token';
    case Security = 'security';
}
