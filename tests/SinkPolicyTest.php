<?php

declare(strict_types=1);

use Kanvigo\Audit\Contracts\DispatchMode;
use Kanvigo\Audit\Contracts\FailureMode;
use Kanvigo\Audit\Contracts\SinkPolicy;

test('sync is inline and fail-open', function (): void {
    $policy = SinkPolicy::sync();

    expect($policy->dispatch)->toBe(DispatchMode::Sync)
        ->and($policy->failure)->toBe(FailureMode::FailOpen)
        ->and($policy->isQueued())->toBeFalse()
        ->and($policy->isFailClosed())->toBeFalse();
});

test('queued is drained and fail-open', function (): void {
    $policy = SinkPolicy::queued();

    expect($policy->isQueued())->toBeTrue()
        ->and($policy->isFailClosed())->toBeFalse();
});

test('fail-closed is synchronous by construction', function (): void {
    $policy = SinkPolicy::failClosed();

    expect($policy->dispatch)->toBe(DispatchMode::Sync)
        ->and($policy->isFailClosed())->toBeTrue()
        ->and($policy->isQueued())->toBeFalse();
});
