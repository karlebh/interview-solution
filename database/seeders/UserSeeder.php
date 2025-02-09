<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! User::where('role', RoleEnum::ADMIN->value)) {
            User::factory()->create([
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'role' => RoleEnum::ADMIN->value,
                'password' => 'password',
            ]);
        }

        User::factory()->count(10)->create([
            'password' => 'password',
        ]);
    }
}
