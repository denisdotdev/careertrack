<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanyUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create companies
        $company1 = Company::firstOrCreate(['name' => 'TechCorp']);
        $company2 = Company::firstOrCreate(['name' => 'StartupXYZ']);

        // Create users with different roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@techcorp.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@techcorp.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('password'),
            ]
        );

        $member = User::firstOrCreate(
            ['email' => 'member@techcorp.com'],
            [
                'name' => 'Member User',
                'password' => bcrypt('password'),
            ]
        );

        $viewer = User::firstOrCreate(
            ['email' => 'viewer@techcorp.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password'),
            ]
        );

        // Assign roles to users in companies
        $company1->addUser($admin, 'admin');
        $company1->addUser($manager, 'manager');
        $company1->addUser($member, 'member');
        $company1->addUser($viewer, 'viewer');

        // Add some users to the second company
        $company2->addUser($admin, 'manager'); // Same user, different role
        $company2->addUser($member, 'admin');  // Same user, different role

        $this->command->info('Company users seeded successfully!');
        $this->command->info('Sample users created:');
        $this->command->info('- admin@techcorp.com (Admin in TechCorp, Manager in StartupXYZ)');
        $this->command->info('- manager@techcorp.com (Manager in TechCorp)');
        $this->command->info('- member@techcorp.com (Member in TechCorp, Admin in StartupXYZ)');
        $this->command->info('- viewer@techcorp.com (Viewer in TechCorp)');
    }
}
