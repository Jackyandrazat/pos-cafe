<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan role default lengkap: admin, kasir, owner, kitchen
        $defaultRoles = [
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'kasir', 'guard_name' => 'web'],
            ['name' => 'owner', 'guard_name' => 'web'],
            ['name' => 'kitchen', 'guard_name' => 'web'],
        ];

        foreach ($defaultRoles as $role) {
            $exists = DB::table('roles')->where('name', $role['name'])->exists();
            if (! $exists) {
                DB::table('roles')->insert(array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'kitchen')->delete();
    }
};
