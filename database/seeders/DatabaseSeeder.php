<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('roles')->insert([
            ['name' => 'admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'kasir', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'owner', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Admin User
        DB::table('users')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@poscafe.test',
            'password' => Hash::make('password'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign Admin Role to User
        $userId = DB::table('users')->where('email', 'admin@poscafe.test')->first()->id;
        $roleId = DB::table('roles')->where('name', 'admin')->first()->id;

        DB::table('role_user')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);

        // Settings default
        DB::table('settings')->insert([
            [
                'key' => 'manage_stock_enabled',
                'value' => 'true',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
