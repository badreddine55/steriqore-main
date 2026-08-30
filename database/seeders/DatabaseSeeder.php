<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Truncate auth tokens, sessions, usages, labels, patients, users, cabinets
        DB::table('personal_access_tokens')->delete();
        DB::table('sessions')->delete();
        if (DB::getSchemaBuilder()->hasTable('instrument_usages')) {
            DB::table('instrument_usages')->delete();
        }
        if (DB::getSchemaBuilder()->hasTable('labels')) {
            DB::table('labels')->delete();
        }
        if (DB::getSchemaBuilder()->hasTable('patients')) {
            DB::table('patients')->delete();
        }
        User::query()->delete();
        if (DB::getSchemaBuilder()->hasTable('cabinets')) {
            DB::table('cabinets')->delete();
        }

        // Seed ONLY the Super Administrator
        User::create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@steriqore.com',
            'password' => 'password',
            'role' => 'super_admin',
            'cabinet_id' => null,
            'cabinet_name' => null,
            'cabinet_room' => null,
            'email_verified_at' => now(),
        ]);
    }
}
