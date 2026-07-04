<?php

declare(strict_types=1);

use Kanvigo\Audit\Contracts\AuditCategory;
use Kanvigo\Audit\Contracts\AuditContext;
use Kanvigo\Audit\Contracts\AuditEvent;
use Kanvigo\Audit\Contracts\AuditSource;

test('withers return new instances and leave the original untouched', function (): void {
    $original = AuditEvent::make('status_changed', AuditCategory::Content);

    $modified = $original
        ->withSubject('App\Models\Task', 42)
        ->withActor(7)
        ->withMetadata(['field' => 'status', 'old' => 'ToDo', 'new' => 'Done'])
        ->withTags('board')
        ->withContext(new AuditContext(AuditSource::Ui, ip: '10.0.0.1', userAgent: 'Test/1.0'))
        ->withOccurredAt(new DateTimeImmutable('2026-07-04T12:00:00+00:00'));

    expect($original->subjectType)->toBeNull()
        ->and($original->actorId)->toBeNull()
        ->and($original->metadata)->toBe([])
        ->and($modified->subjectType)->toBe('App\Models\Task')
        ->and($modified->subjectId)->toBe(42)
        ->and($modified->actorId)->toBe(7)
        ->and($modified->metadata)->toBe(['field' => 'status', 'old' => 'ToDo', 'new' => 'Done'])
        ->and($modified->tags)->toBe(['board'])
        ->and($modified->context?->source)->toBe(AuditSource::Ui)
        ->and($modified->action)->toBe('status_changed')
        ->and($modified->category)->toBe(AuditCategory::Content);
});

test('toArray/fromArray round-trips every field', function (): void {
    $event = AuditEvent::make('token_created', AuditCategory::Token)
        ->withSubject('App\Models\User', 3)
        ->withActor(3)
        ->withMetadata(['token' => 'CI deploy'])
        ->withTags('security', 'token')
        ->withContext(new AuditContext(AuditSource::Api, ip: '192.0.2.1', userAgent: 'curl/8', tokenName: 'CI deploy'))
        ->withOccurredAt(new DateTimeImmutable('2026-07-04T12:34:56.789+02:00'));

    $restored = AuditEvent::fromArray($event->toArray());

    expect($restored->toArray())->toBe($event->toArray())
        ->and($restored->category)->toBe(AuditCategory::Token)
        ->and($restored->context?->source)->toBe(AuditSource::Api)
        ->and($restored->occurredAt?->format(DATE_RFC3339_EXTENDED))
        ->toBe($event->occurredAt?->format(DATE_RFC3339_EXTENDED));
});

test('toArray survives a JSON round-trip and carries the schema version', function (): void {
    $event = AuditEvent::make('login_failed', AuditCategory::Authn)
        ->withContext(new AuditContext(AuditSource::Ui));

    $decoded = json_decode(json_encode($event->toArray(), JSON_THROW_ON_ERROR), true, flags: JSON_THROW_ON_ERROR);

    expect($decoded['v'])->toBe(AuditEvent::SCHEMA_VERSION)
        ->and(AuditEvent::fromArray($decoded)->action)->toBe('login_failed')
        ->and(AuditEvent::fromArray($decoded)->actorId)->toBeNull();
});

test('fromArray tolerates unknown fields from a newer schema', function (): void {
    $event = AuditEvent::fromArray([
        'v' => 99,
        'action' => 'created',
        'category' => 'content',
        'subject_type' => 'App\Models\Task',
        'subject_id' => 1,
        'brand_new_field' => 'ignored',
    ]);

    expect($event->action)->toBe('created')
        ->and($event->subjectId)->toBe(1);
});

test('the DTO survives native PHP serialization for queued sinks', function (): void {
    $event = AuditEvent::make('commented', AuditCategory::Content)
        ->withSubject('App\Models\Task', 5)
        ->withContext(new AuditContext(AuditSource::Mcp, tokenName: 'Claude'))
        ->withOccurredAt(new DateTimeImmutable('2026-07-04T08:00:00+00:00'));

    $restored = unserialize(serialize($event));

    expect($restored)->toBeInstanceOf(AuditEvent::class)
        ->and($restored->toArray())->toBe($event->toArray());
});
