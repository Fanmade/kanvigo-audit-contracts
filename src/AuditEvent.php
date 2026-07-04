<?php

namespace Kanvigo\Audit\Contracts;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * An immutable audit event — the single value that flows from the emitting
 * application through the outbox to every registered {@see AuditSink}.
 *
 * The metadata array carries event-specific payload; for content change
 * events the conventional keys are "field", "old" and "new". Keep payloads
 * reference-shaped (IDs, names of the changed thing) rather than content
 * snapshots where possible — free text in an immutable trail is a PII
 * liability.
 *
 * The idempotency key is stamped once per recorded event; queued sinks are
 * delivered at-least-once and must dedupe on it.
 *
 * The array schema produced by {@see toArray()} is versioned ("v") and
 * evolves additively only: fields are added, never renamed, retyped or
 * removed within a major version. Consumers must ignore unknown fields.
 */
final readonly class AuditEvent
{
    public const int SCHEMA_VERSION = 1;

    /**
     * @param  array<string, mixed>  $metadata
     * @param  list<string>  $tags
     */
    public function __construct(
        public string $action,
        public AuditCategory $category,
        public ?string $subjectType = null,
        public int|string|null $subjectId = null,
        public int|string|null $actorId = null,
        public array $metadata = [],
        public array $tags = [],
        public ?AuditContext $context = null,
        public ?DateTimeImmutable $occurredAt = null,
        public ?string $idempotencyKey = null,
    ) {}

    public static function make(string $action, AuditCategory $category): self
    {
        return new self($action, $category);
    }

    public function withSubject(string $type, int|string $id): self
    {
        return $this->copy(subjectType: $type, subjectId: $id);
    }

    public function withActor(int|string $actorId): self
    {
        return $this->copy(actorId: $actorId);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function withMetadata(array $metadata): self
    {
        return $this->copy(metadata: $metadata);
    }

    public function withTags(string ...$tags): self
    {
        return $this->copy(tags: array_values($tags));
    }

    public function withContext(AuditContext $context): self
    {
        return $this->copy(context: $context);
    }

    public function withOccurredAt(DateTimeImmutable $occurredAt): self
    {
        return $this->copy(occurredAt: $occurredAt);
    }

    public function withIdempotencyKey(string $idempotencyKey): self
    {
        return $this->copy(idempotencyKey: $idempotencyKey);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'v' => self::SCHEMA_VERSION,
            'action' => $this->action,
            'category' => $this->category->value,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'actor_id' => $this->actorId,
            'metadata' => $this->metadata,
            'tags' => $this->tags,
            'context' => $this->context?->toArray(),
            'occurred_at' => $this->occurredAt?->format(DateTimeInterface::RFC3339_EXTENDED),
            'idempotency_key' => $this->idempotencyKey,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            action: $data['action'],
            category: AuditCategory::from($data['category']),
            subjectType: $data['subject_type'] ?? null,
            subjectId: $data['subject_id'] ?? null,
            actorId: $data['actor_id'] ?? null,
            metadata: $data['metadata'] ?? [],
            tags: $data['tags'] ?? [],
            context: isset($data['context']) ? AuditContext::fromArray($data['context']) : null,
            occurredAt: isset($data['occurred_at']) ? new DateTimeImmutable($data['occurred_at']) : null,
            idempotencyKey: $data['idempotency_key'] ?? null,
        );
    }

    /**
     * Build a modified copy; unset arguments keep the current value.
     *
     * @param  array<string, mixed>|null  $metadata
     * @param  list<string>|null  $tags
     */
    private function copy(
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        int|string|null $actorId = null,
        ?array $metadata = null,
        ?array $tags = null,
        ?AuditContext $context = null,
        ?DateTimeImmutable $occurredAt = null,
        ?string $idempotencyKey = null,
    ): self {
        return new self(
            action: $this->action,
            category: $this->category,
            subjectType: $subjectType ?? $this->subjectType,
            subjectId: $subjectId ?? $this->subjectId,
            actorId: $actorId ?? $this->actorId,
            metadata: $metadata ?? $this->metadata,
            tags: $tags ?? $this->tags,
            context: $context ?? $this->context,
            occurredAt: $occurredAt ?? $this->occurredAt,
            idempotencyKey: $idempotencyKey ?? $this->idempotencyKey,
        );
    }
}
