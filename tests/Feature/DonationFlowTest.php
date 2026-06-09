<?php

use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Project;

beforeEach(function () {
    $gateway = PaymentGateway::factory()->create([
        'driver' => 'bank_transfer',
        'is_active' => true,
        'config' => ['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123'],
    ]);

    PaymentMethod::factory()->create([
        'gateway_id' => $gateway->id,
        'is_active' => true,
    ]);
});

it('stores a donation and redirects to confirmation', function () {
    $project = Project::factory()->create(['is_active' => true]);

    $response = $this->post(route('donate.store', 'en'), [
        'donor_name' => 'Test Donor',
        'email' => 'test@example.com',
        'amount' => 100,
        'payment_method_id' => PaymentMethod::first()->id,
        'project_id' => $project->id,
        'notes' => 'Test donation',
    ]);

    $this->assertDatabaseHas('donations', [
        'email' => 'test@example.com',
        'amount' => 100.00,
        'status' => 'pending',
    ]);
});

it('rejects donation with invalid payment method', function () {
    $response = $this->post(route('donate.store', 'en'), [
        'donor_name' => 'Test Donor',
        'email' => 'test@example.com',
        'amount' => 100,
        'payment_method_id' => 999,
    ]);

    $response->assertSessionHasErrors('payment_method_id');
});

it('rejects donation with negative amount', function () {
    $response = $this->post(route('donate.store', 'en'), [
        'donor_name' => 'Test Donor',
        'email' => 'test@example.com',
        'amount' => -50,
        'payment_method_id' => PaymentMethod::first()->id,
    ]);

    $response->assertSessionHasErrors('amount');
});

it('rejects donation without email', function () {
    $response = $this->post(route('donate.store', 'en'), [
        'donor_name' => 'Test Donor',
        'amount' => 100,
        'payment_method_id' => PaymentMethod::first()->id,
    ]);

    $response->assertSessionHasErrors('email');
});

it('handles spam honeypot field', function () {
    $response = $this->post(route('donate.store', 'en'), [
        'donor_name' => 'Test Donor',
        'email' => 'test@example.com',
        'amount' => 100,
        'payment_method_id' => PaymentMethod::first()->id,
        'hp_website' => 'spam value',
    ]);

    $this->assertDatabaseMissing('donations', ['email' => 'test@example.com']);
});
