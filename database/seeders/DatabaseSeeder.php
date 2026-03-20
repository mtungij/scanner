<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'role' => 'admin',
            'password' => 'password',
        ]);

        User::query()->updateOrCreate([
            'email' => 'cashier@example.com',
        ], [
            'name' => 'Cashier User',
            'role' => 'cashier',
            'password' => 'password',
        ]);
    }
}
