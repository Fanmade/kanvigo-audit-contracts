<?php

declare(strict_types=1);

use Kanvigo\Audit\Contracts\AuditCategory;

test('every category has a stable lowercase string value', function (): void {
    expect(AuditCategory::Content->value)->toBe('content')
        ->and(AuditCategory::Authn->value)->toBe('authn')
        ->and(AuditCategory::Authz->value)->toBe('authz')
        ->and(AuditCategory::Token->value)->toBe('token')
        ->and(AuditCategory::Security->value)->toBe('security')
        ->and(AuditCategory::Access->value)->toBe('access');
});

test('the read/access category exists and round-trips through its wire value', function (): void {
    expect(AuditCategory::tryFrom('access'))->toBe(AuditCategory::Access);
});
