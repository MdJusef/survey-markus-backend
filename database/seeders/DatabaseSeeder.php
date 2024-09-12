<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

//         \App\Models\User::factory()->create([
//             'name' => 'Super Admin',
//             'email' => 'superadmin@gmail.com',
//             'password' => bcrypt('1234567rr'),
//             'role_type' => 'SUPER ADMIN',
//             'email_verified_at' => now(),
//             'otp' => 0,
//         ]);
//
//        \App\Models\User::factory()->create([
//            'name' => 'Admin',
//            'email' => 'admin@gmail.com',
//            'password' => bcrypt('1234567rr'),
//            'role_type' => 'ADMIN',
//            'email_verified_at' => now(),
//            'otp' => 0,
//        ]);

//        \App\Models\User::factory()->create([
//            'name' => 'employee',
//            'email' => 'employee@gmail.com',
//            'password' => bcrypt('1234567rr'),
//            'role_type' => 'EMPLOYEE',
//            'email_verified_at' => now(),
//            'otp' => 0,
//        ]);

        \App\Models\User::factory()->create([
            'name' => 'COMPANY NAME',
            'email' => 'company@gmail.com',
            'password' => bcrypt('1234567rr'),
            'role_type' => 'COMPANY',
            'email_verified_at' => now(),
            'otp' => 0,
        ]);

//        \App\Models\CompanyJoin::factory()->create([
//            'user_id' => 3,
//            'company_id' => 4,
//            'status' => 'accepted',
//        ]);

    }
}
