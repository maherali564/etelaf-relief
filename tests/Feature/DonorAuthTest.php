<?php

use App\Models\Donation;
use App\Models\Donor;

it('registers a new donor', function () {
    $response = $this->post(route('donor.register.post', 'en'), [
        'name' => 'New Donor',
        'email' => 'donor@example.com',
        'phone' => '+123456789',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('donors', ['email' => 'donor@example.com']);
});

it('requires password confirmation for registration', function () {
    $response = $this->post(route('donor.register.post', 'en'), [
        'name' => 'New Donor',
        'email' => 'donor@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different_password',
    ]);

    $response->assertSessionHasErrors('password');
});

it('requires unique email for registration', function () {
    Donor::factory()->create(['email' => 'existing@example.com']);

    $response = $this->post(route('donor.register.post', 'en'), [
        'name' => 'Another Donor',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('email');
});

it('logs in a registered donor', function () {
    $donor = Donor::factory()->create([
        'email' => 'login@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post(route('donor.login.post', 'en'), [
        'email' => 'login@example.com',
        'password' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($donor, 'donor');
});

it('rejects invalid login credentials', function () {
    Donor::factory()->create([
        'email' => 'valid@example.com',
        'password' => bcrypt('correct_password'),
    ]);

    $response = $this->post(route('donor.login.post', 'en'), [
        'email' => 'valid@example.com',
        'password' => 'wrong_password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest('donor');
});

it('logs out authenticated donor', function () {
    $donor = Donor::factory()->create();
    $this->actingAs($donor, 'donor');

    $response = $this->post(route('donor.logout', 'en'));

    $response->assertRedirect();
    $this->assertGuest('donor');
});

it('associates existing donations after registration', function () {
    Donation::factory()->create([
        'email' => 'link@example.com',
        'donor_id' => null,
    ]);

    $this->post(route('donor.register.post', 'en'), [
        'name' => 'Linked Donor',
        'email' => 'link@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect();

    $donor = Donor::where('email', 'link@example.com')->first();
    expect($donor)->not->toBeNull();
    $this->assertDatabaseHas('donations', [
        'email' => 'link@example.com',
        'donor_id' => $donor->id,
    ]);
});
