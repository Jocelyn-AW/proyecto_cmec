<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => env('ADMIN_EMAIL')
            ],
            [
                'name' => 'Administrador',
                'password' => Hash::make(env('ADMIN_PASSWORD')),
                'role' => 'administrador',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );
    }
}
