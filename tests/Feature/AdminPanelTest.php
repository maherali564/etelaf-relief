<?php

use App\Models\ContactSubmission;
use App\Models\Cryptocurrency;
use App\Models\CryptoNetwork;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Page;
use App\Models\PaymentConfirmation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Program;
use App\Models\Project;
use App\Models\Statistic;
use App\Models\Story;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerOpportunity;
use App\Models\VolunteerTask;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->admin = User::where('email', 'admin@etelafrelief.org')->first();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin);

    // Ensure records exist for edit/view tests
    CryptoNetwork::factory()->create();
    Cryptocurrency::factory()->create();
    $donor = Donor::factory()->create();
    Donation::factory()->create(['donor_id' => $donor->id]);
    Permission::create(['name' => 'test_perm', 'guard_name' => 'web']);
    Role::create(['name' => 'test_role', 'guard_name' => 'web']);
    VolunteerOpportunity::factory()->create();
    VolunteerTask::factory()->create();
    ContactSubmission::factory()->create();
    PaymentConfirmation::factory()->create();
});

it('loads dashboard', function () {
    $this->get('/admin')->assertStatus(200);
});

it('loads reports', function () {
    $this->get('/admin/reports')->assertStatus(200);
});

it('loads manage-site-settings', function () {
    $this->get('/admin/manage-site-settings')->assertStatus(200);
});

it('loads all list pages', function () {
    $pages = [
        '/admin/contact-submissions',
        '/admin/crypto-networks',
        '/admin/cryptocurrencies',
        '/admin/donation-submissions',
        '/admin/donations',
        '/admin/pages',
        '/admin/payment-confirmations',
        '/admin/payment-gateways',
        '/admin/payment-methods',
        '/admin/permissions',
        '/admin/programs',
        '/admin/projects',
        '/admin/roles',
        '/admin/statistics',
        '/admin/stories',
        '/admin/testimonials',
        '/admin/users',
        '/admin/volunteer-opportunities',
        '/admin/volunteer-tasks',
        '/admin/volunteers',
    ];
    foreach ($pages as $page) {
        $this->get($page)->assertStatus(200);
    }
});

it('loads all create pages', function () {
    $pages = [
        '/admin/crypto-networks/create',
        '/admin/cryptocurrencies/create',
        '/admin/pages/create',
        '/admin/payment-gateways/create',
        '/admin/payment-methods/create',
        '/admin/permissions/create',
        '/admin/programs/create',
        '/admin/projects/create',
        '/admin/roles/create',
        '/admin/statistics/create',
        '/admin/stories/create',
        '/admin/testimonials/create',
        '/admin/users/create',
        '/admin/volunteer-opportunities/create',
        '/admin/volunteer-tasks/create',
    ];
    foreach ($pages as $page) {
        $this->get($page)->assertStatus(200);
    }
});

it('loads all edit/view pages', function () {
    $models = [
        'contact-submissions' => ContactSubmission::first(),
        'crypto-networks' => CryptoNetwork::first(),
        'cryptocurrencies' => Cryptocurrency::first(),
        'donations' => Donation::first(),
        'pages' => Page::first(),
        'payment-confirmations' => PaymentConfirmation::first(),
        'payment-gateways' => PaymentGateway::first(),
        'payment-methods' => PaymentMethod::first(),
        'permissions' => Permission::where('name', 'test_perm')->first(),
        'programs' => Program::first(),
        'projects' => Project::first(),
        'roles' => Role::where('name', 'test_role')->first(),
        'statistics' => Statistic::first(),
        'stories' => Story::first(),
        'testimonials' => Testimonial::first(),
        'users' => $this->admin,
        'volunteer-opportunities' => VolunteerOpportunity::first(),
        'volunteer-tasks' => VolunteerTask::first(),
        'volunteers' => Volunteer::first(),
    ];

    foreach ($models as $resource => $model) {
        if (! $model) {
            continue;
        }
        $suffix = in_array($resource, ['contact-submissions', 'donations']) ? '' : '/edit';
        $this->get("/admin/{$resource}/{$model->id}{$suffix}")->assertStatus(200);
    }
});
