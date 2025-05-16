<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Ali Mohamed',
                'email' => 'ali@ali.com',
                'password' => '12345678'
            ],
            [
                'name' => 'John Manager',
                'email' => 'john@john.com',
                'password' => 'password123'
            ],
            [
                'name' => 'Sarah Supervisor',
                'email' => 'ss@ss.com',
                'password' => 'password123'
            ],
        ];

        foreach ($users as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password'])
            ]);
        }
    }
}
