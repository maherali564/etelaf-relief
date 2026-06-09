<?php

use App\Models\Newsletter;

it('stores a contact submission', function () {
    $response = $this->post(route('contact.store', 'en'), [
        'name' => 'Test User',
        'email' => 'user@example.com',
        'subject' => 'Test Subject',
        'message' => 'This is a test message.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('contact_submissions', ['email' => 'user@example.com']);
});

it('rejects contact submission with missing fields', function () {
    $response = $this->post(route('contact.store', 'en'), [
        'name' => 'Test',
    ]);

    $response->assertSessionHasErrors(['email', 'subject', 'message']);
});

it('stores a volunteer application', function () {
    $response = $this->post(route('volunteer.store', 'en'), [
        'name' => 'Volunteer User',
        'email' => 'volunteer@example.com',
        'phone' => '+987654321',
        'skills' => 'PHP, Laravel',
        'availability' => 'Weekends',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('volunteers', ['email' => 'volunteer@example.com', 'status' => 'pending']);
});

it('subscribes to newsletter', function () {
    $response = $this->post(route('newsletter.store', 'en'), [
        'email' => 'subscribe@example.com',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('newsletters', ['email' => 'subscribe@example.com']);
});

it('rejects duplicate newsletter subscription', function () {
    Newsletter::factory()->create(['email' => 'dup@example.com']);

    $response = $this->post(route('newsletter.store', 'en'), [
        'email' => 'dup@example.com',
    ]);

    $response->assertSessionHasErrors('email');
});

it('rejects contact with honeypot spam field', function () {
    $response = $this->post(route('contact.store', 'en'), [
        'name' => 'Spammer',
        'email' => 'spam@example.com',
        'subject' => 'Spam',
        'message' => 'Buy now!',
        'hp_website' => 'spam value',
    ]);

    $this->assertDatabaseMissing('contact_submissions', ['email' => 'spam@example.com']);
});

it('rejects volunteer with honeypot spam field', function () {
    $response = $this->post(route('volunteer.store', 'en'), [
        'name' => 'Spammer',
        'email' => 'spam@example.com',
        'phone' => '123',
        'hp_website' => 'spam value',
    ]);

    $this->assertDatabaseMissing('volunteers', ['email' => 'spam@example.com']);
});
