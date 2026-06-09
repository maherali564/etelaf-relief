<?php

use App\Models\User;
use App\Policies\ChatMessagePolicy;
use App\Policies\ChatSessionPolicy;
use App\Policies\CryptoTransactionPolicy;
use App\Policies\CurrencyRatePolicy;
use App\Policies\DonorPolicy;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->admin = User::factory()->create();

    Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->admin->assignRole('super_admin');

    Permission::create(['name' => 'view_any_chat_message', 'guard_name' => 'web']);
    Permission::create(['name' => 'view_any_chat_session', 'guard_name' => 'web']);
    Permission::create(['name' => 'view_any_crypto_transaction', 'guard_name' => 'web']);
    Permission::create(['name' => 'view_any_currency_rate', 'guard_name' => 'web']);
    Permission::create(['name' => 'view_any_donor', 'guard_name' => 'web']);
});

it('ChatMessagePolicy returns bool', function () {
    $policy = new ChatMessagePolicy;
    expect(is_bool($policy->viewAny($this->admin)))->toBeTrue();
});

it('ChatSessionPolicy returns bool', function () {
    $policy = new ChatSessionPolicy;
    expect(is_bool($policy->viewAny($this->admin)))->toBeTrue();
});

it('CryptoTransactionPolicy returns bool', function () {
    $policy = new CryptoTransactionPolicy;
    expect(is_bool($policy->viewAny($this->admin)))->toBeTrue();
});

it('CurrencyRatePolicy returns bool', function () {
    $policy = new CurrencyRatePolicy;
    expect(is_bool($policy->viewAny($this->admin)))->toBeTrue();
});

it('DonorPolicy returns bool', function () {
    $policy = new DonorPolicy;
    expect(is_bool($policy->viewAny($this->admin)))->toBeTrue();
});
