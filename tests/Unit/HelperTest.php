<?php

namespace Tests\Unit;

use App\Services\Payment\IdempotencyHelper;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HelperTest extends TestCase
{
    #[Test]
    public function idempotency_key_generates_with_prefix()
    {
        $key = IdempotencyHelper::generateKey('test');
        $this->assertStringStartsWith('test_', $key);
        $this->assertGreaterThan(30, strlen($key));
    }

    #[Test]
    public function idempotency_key_returns_empty_string_without_prefix()
    {
        $key = IdempotencyHelper::generateKey();
        $this->assertStringStartsWith('_', $key);
    }
}
