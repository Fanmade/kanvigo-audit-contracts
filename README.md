# kanvigo/audit-contracts

Stable contracts for [Kanvigo](https://github.com/Fanmade/Kanvigo)'s pluggable audit layer.
Both the Kanvigo core and every audit sink — official (e.g. the Chronicle bridge) or
third-party — depend only on this tiny package, so sinks can be developed and versioned
independently of the application (the PSR-3 / Flysystem-adapter pattern).

## The contract

- **`AuditEvent`** — the immutable DTO for a single audited action: `action`, `category`,
  `subject{type,id}`, `actorId` (null = system actor), `metadata[]`, `tags[]`,
  `context{source, ip, userAgent, tokenName}`, `occurredAt`. Explicitly serializable via
  `toArray()` / `fromArray()` with a versioned (`v`) schema.
- **`AuditSink`** — what you implement: `accepts(AuditEvent): bool` (the taxonomy filter),
  `record(AuditEvent): void`, `policy(): SinkPolicy`.
- **`SinkPolicy`** — how a sink runs: `sync()` (inline, after commit, failures isolated),
  `queued()` (shipped by the outbox drain worker, at-least-once), `failClosed()`
  (synchronous, pre-commit, inside the domain transaction — a failure aborts the action).
  Queued + fail-closed is impossible by construction.
- **`AuditCategory`** — Content · Authn · Authz · Token · Security. The canonical
  event → category table lives in the enum's docblock.
- **`AuditSource`** — Ui · Mcp · Api · Queue · System.
- **`Exceptions\AuditIntegrityException`** — thrown by the emitting application when a
  fail-closed sink's guarantee cannot be honored (audited mutation outside a transaction).

## Implementing a sink

```php
use Kanvigo\Audit\Contracts\{AuditEvent, AuditCategory, AuditSink, SinkPolicy};

class SiemSink implements AuditSink
{
    public function accepts(AuditEvent $event): bool
    {
        return $event->category !== AuditCategory::Content;
    }

    public function record(AuditEvent $event): void
    {
        $this->client->ship($event->toArray());
    }

    public function policy(): SinkPolicy
    {
        return SinkPolicy::queued();
    }
}
```

Register the class in the application's `config/audit.php` `sinks` list — done. Sinks run
alongside each other; each receives every event it accepts.

## Stability & versioning

This package follows semver with an **additive-only** policy inside a major version:

- `AuditEvent` fields and array-schema keys are only ever added, never renamed, retyped
  or removed. The `v` key identifies the schema generation.
- Enum cases are only added. Sinks must tolerate (ignore or generically handle) actions,
  categories and sources they don't know.
- The `AuditSink` interface gains no new required methods within a major version.
