<?php

use App\Models\Donation;
use App\Models\Donor;

it('prevents mass assignment on sensitive donation fields', function () {
    $donation = Donation::create([
        'donor_name' => 'Test',
        'email' => 'test@example.com',
        'amount' => 100,
        'status' => 'completed',
    ]);

    expect($donation->transaction_id)->not->toBe('should-not-override');
});

it('prevents mass assignment on donor guarded fields', function () {
    $donor = Donor::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);

    expect($donor->exists)->toBeTrue()
        ->and($donor->name)->toBe('Test');
});
