<?php

namespace Kanvigo\Audit\Contracts;

/**
 * The request/runtime context an audit event was recorded under. Stamped once
 * by the emitting application's context resolver; immutable afterwards.
 */
final readonly class AuditContext
{
    public function __construct(
        public AuditSource $source,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $tokenName = null,
    ) {}

    /**
     * @return array{source: string, ip: string|null, user_agent: string|null, token_name: string|null}
     */
    public function toArray(): array
    {
        return [
            'source' => $this->source->value,
            'ip' => $this->ip,
            'user_agent' => $this->userAgent,
            'token_name' => $this->tokenName,
        ];
    }

    /**
     * @param  array{source: string, ip?: string|null, user_agent?: string|null, token_name?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            source: AuditSource::from($data['source']),
            ip: $data['ip'] ?? null,
            userAgent: $data['user_agent'] ?? null,
            tokenName: $data['token_name'] ?? null,
        );
    }
}
