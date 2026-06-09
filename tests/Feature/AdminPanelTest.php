<?php

use App\Models\Campaign;
use App\Models\ContactSubmission;
use App\Models\Cryptocurrency;
use App\Models\CryptoNetwork;
use App\Models\Donation;
use App\Models\Donor;
use App\Models\Faq;
use App\Models\GazaStat;
use App\Models\Newsletter;
use App\Models\Page;
use App\Models\PaymentConfirmation;
use App\Models\PaymentGateway;
use App\Models\PaymentMethod;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\QuickAction;
use App\Models\Slider;
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
    GazaStat::factory()->create();
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
        '/admin/campaigns',
        '/admin/contact-submissions',
        '/admin/crypto-networks',
        '/admin/cryptocurrencies',
        '/admin/donation-submissions',
        '/admin/donations',
        '/admin/faqs',
        '/admin/gaza-stats',
        '/admin/newsletters',
        '/admin/pages',
        '/admin/payment-confirmations',
        '/admin/payment-gateways',
        '/admin/payment-methods',
        '/admin/permissions',
        '/admin/posts',
        '/admin/programs',
        '/admin/projects',
        '/admin/quick-actions',
        '/admin/roles',
        '/admin/sliders',
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
        '/admin/campaigns/create',
        '/admin/crypto-networks/create',
        '/admin/cryptocurrencies/create',
        '/admin/faqs/create',
        '/admin/gaza-stats/create',
        '/admin/pages/create',
        '/admin/payment-gateways/create',
        '/admin/payment-methods/create',
        '/admin/permissions/create',
        '/admin/posts/create',
        '/admin/programs/create',
        '/admin/projects/create',
        '/admin/quick-actions/create',
        '/admin/roles/create',
        '/admin/sliders/create',
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
        'campaigns' => Campaign::first(),
        'contact-submissions' => ContactSubmission::first(),
        'crypto-networks' => CryptoNetwork::first(),
        'cryptocurrencies' => Cryptocurrency::first(),
        'donations' => Donation::first(),
        'faqs' => Faq::first(),
        'gaza-stats' => GazaStat::first(),
        'newsletters' => Newsletter::first(),
        'pages' => Page::first(),
        'payment-confirmations' => PaymentConfirmation::first(),
        'payment-gateways' => PaymentGateway::first(),
        'payment-methods' => PaymentMethod::first(),
        'permissions' => Permission::where('name', 'test_perm')->first(),
        'posts' => Post::first(),
        'programs' => Program::first(),
        'projects' => Project::first(),
        'quick-actions' => QuickAction::first(),
        'roles' => Role::where('name', 'test_role')->first(),
        'sliders' => Slider::first(),
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
