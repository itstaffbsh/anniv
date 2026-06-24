<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (!User::where('email', 'superadmin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'super_admin'
            ]);
        }

        if (!User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin'
            ]);
        }

        // Seed default companies
        $companies = [
            'Balisuperhost',
            'Bali Helper',
            'Bali Sunshine',
            'CV Maju Bersama'
        ];
        foreach ($companies as $name) {
            \App\Models\Company::firstOrCreate(['name' => $name]);
        }

        // Seed default departments
        $departments = [
            'IT',
            'Finance',
            'HR & GA',
            'Marketing',
            'Operations'
        ];
        foreach ($departments as $name) {
            \App\Models\Department::firstOrCreate(['name' => $name]);
        }
    }
}
