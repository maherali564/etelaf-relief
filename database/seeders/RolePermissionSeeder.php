<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        $resources = [
            'slider', 'about', 'contact', 'partner', 'volunteer',
            'volunteer_opportunity', 'volunteer_task',
            'page', 'project', 'post', 'story',
            'donation', 'payment_gateway', 'campaign', 'cryptocurrency', 'crypto_network',
            'testimonial', 'statistic', 'quick_action', 'program', 'payment_method',
            'payment_confirmation', 'newsletter', 'gaza_stat', 'faq',
            'donation_submission', 'contact_submission',
            'user', 'role', 'permission',
        ];

        $actions = ['view_any', 'view', 'create', 'update', 'delete'];

        $permissions = [];
        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = $action.'_'.$resource;
            }
        }
        $permissions[] = 'manage_settings';
        $permissions[] = 'manage_chat';
        $permissions[] = 'review_donation';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::where('name', 'not like', '%role%')
            ->where('name', 'not like', '%permission%')->get());

        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editor->syncPermissions(
            Permission::whereIn('name', [
                'view_any_slider', 'view_slider', 'create_slider', 'update_slider', 'delete_slider',
                'view_any_about', 'view_about', 'create_about', 'update_about', 'delete_about',
                'view_any_contact', 'view_contact', 'create_contact', 'update_contact', 'delete_contact',
                'view_any_partner', 'view_partner', 'create_partner', 'update_partner', 'delete_partner',
                'view_any_volunteer', 'view_volunteer',
                'view_any_volunteer_opportunity', 'view_volunteer_opportunity', 'create_volunteer_opportunity', 'update_volunteer_opportunity', 'delete_volunteer_opportunity',
                'view_any_volunteer_task', 'view_volunteer_task', 'create_volunteer_task', 'update_volunteer_task', 'delete_volunteer_task',
                'view_any_page', 'view_page', 'create_page', 'update_page', 'delete_page',
                'view_any_project', 'view_project', 'create_project', 'update_project', 'delete_project',
                'view_any_post', 'view_post', 'create_post', 'update_post', 'delete_post',
                'view_any_story', 'view_story', 'create_story', 'update_story', 'delete_story',
                'view_any_testimonial', 'view_testimonial', 'create_testimonial', 'update_testimonial', 'delete_testimonial',
                'view_any_faq', 'view_faq', 'create_faq', 'update_faq', 'delete_faq',
                'view_any_statistic', 'view_statistic', 'update_statistic',
                'view_any_quick_action', 'view_quick_action', 'update_quick_action',
                'view_any_program', 'view_program', 'update_program',
                'view_any_gaza_stat', 'view_gaza_stat', 'update_gaza_stat',
                'view_any_newsletter', 'view_newsletter',
                'manage_chat',
            ])->get()
        );

        $donationManager = Role::firstOrCreate(['name' => 'donation_manager', 'guard_name' => 'web']);
        $donationManager->syncPermissions(
            Permission::whereIn('name', [
                'view_any_donation', 'view_donation', 'update_donation', 'review_donation',
                'view_any_campaign', 'view_campaign', 'create_campaign', 'update_campaign',
                'view_any_payment_gateway', 'view_payment_gateway', 'update_payment_gateway',
                'view_any_cryptocurrency', 'view_cryptocurrency', 'update_cryptocurrency',
                'view_any_crypto_network', 'view_crypto_network', 'update_crypto_network',
                'view_any_volunteer', 'view_volunteer',
                'view_any_volunteer_opportunity', 'view_volunteer_opportunity',
                'view_any_volunteer_task', 'view_volunteer_task',
                'manage_settings',
            ])->get()
        );

        $supporter = Role::firstOrCreate(['name' => 'supporter', 'guard_name' => 'web']);
        $supporter->syncPermissions(
            Permission::whereIn('name', [
                'view_any_donation', 'view_donation',
                'view_any_volunteer', 'view_volunteer',
                'view_any_volunteer_opportunity', 'view_volunteer_opportunity',
                'view_any_volunteer_task', 'view_volunteer_task',
                'view_any_contact', 'view_contact',
                'manage_chat',
            ])->get()
        );

        $users = User::all();
        foreach ($users as $user) {
            $roleName = match ($user->role) {
                'super_admin' => 'super_admin',
                'admin' => 'admin',
                'editor' => 'editor',
                'supporter' => 'supporter',
                default => null,
            };
            if ($roleName && $role = Role::where('name', $roleName)->first()) {
                $user->assignRole($role);
            }
        }
    }
}
