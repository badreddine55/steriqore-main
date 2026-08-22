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
        // Truncate users table and create single Admin
        DB::table('personal_access_tokens')->delete();
        DB::table('sessions')->delete();
        if (DB::getSchemaBuilder()->hasTable('instrument_usages')) {
            DB::table('instrument_usages')->update(['user_id' => null]);
        }
        User::query()->delete();

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@steriqore.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->call([
            PatientSeeder::class,
            LabelSeeder::class,
        ]);
    }
}
