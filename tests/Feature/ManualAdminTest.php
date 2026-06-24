<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->admin = User::where('email', 'admin@etelafrelief.org')->first()
        ?? User::factory()->create([
            'email' => 'admin@etelafrelief.org',
            'password' => bcrypt('password'),
            'is_active' => true,
            'is_admin' => true,
        ]);
    if (! $this->admin->hasRole('super_admin')) {
        $this->admin->assignRole('super_admin');
    }
    $this->admin = $this->admin->fresh();
});

it('1. loads admin login page', function () {
    get('/admin/login')->assertStatus(200);
});

it('2. redirects guest from admin to login', function () {
    get('/admin/donations')->assertRedirect();
});

it('4. loads admin dashboard when authenticated', function () {
    actingAs($this->admin)->get('/admin')->assertStatus(200);
});

it('5. loads donations page', function () {
    actingAs($this->admin)->get('/admin/donations')->assertStatus(200);
});

it('6. loads users page', function () {
    actingAs($this->admin)->get('/admin/users')->assertStatus(200);
});

it('7. loads projects page', function () {
    actingAs($this->admin)->get('/admin/projects')->assertStatus(200);
});

it('9. loads stories page', function () {
    actingAs($this->admin)->get('/admin/stories')->assertStatus(200);
});

it('10. loads chat page', function () {
    actingAs($this->admin)->get('/admin/chats')->assertStatus(200);
});

it('11. loads volunteers page', function () {
    actingAs($this->admin)->get('/admin/volunteers')->assertStatus(200);
});

it('12. loads payment methods page', function () {
    actingAs($this->admin)->get('/admin/payment-methods')->assertStatus(200);
});

it('13. loads public homepage', function () {
    get('/ar')->assertStatus(200);
});

it('14. loads donate page', function () {
    get('/ar/donate')->assertStatus(200);
});

it('15. loads donor login page', function () {
    get('/ar/donor/login')->assertStatus(200);
});

it('16. redirects unauthenticated to login', function () {
    get('/admin/donations')->assertRedirect();
});

it('17. loads projects page', function () {
    get('/ar/projects')->assertStatus(200);
});

it('18. loads about page', function () {
    get('/ar/about')->assertStatus(200);
});
