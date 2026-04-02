<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ]);

        User::create([
            'name' => 'Test Driver',
            'username' => 'driver01',
            'email' => 'driver@swagoto.com',
            'password' => Hash::make('12345678'),
            'role' => 'driver',
            'phone' => '01700000000',
        ]);

        // ৩. টেস্ট প্যাসেঞ্জার তৈরি করা
        User::create([
            'name' => 'Test Student',
            'username' => 'student01',
            'email' => 'student@swagoto.com',
            'password' => Hash::make('12345678'),
            'role' => 'passenger',
            'student_id' => '2220000000',
        ]);
    }
}
