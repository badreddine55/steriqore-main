<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create cabinets table
        Schema::create('cabinets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 2. Add cabinet_id column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('id')->constrained('cabinets')->nullOnDelete();
        });

        // 3. Add cabinet_id column to patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('id')->constrained('cabinets')->cascadeOnDelete();
        });

        // 4. Add cabinet_id column to labels table
        Schema::table('labels', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('id')->constrained('cabinets')->cascadeOnDelete();
        });

        // 5. Add cabinet_id column to instrument_usages table
        Schema::table('instrument_usages', function (Blueprint $table) {
            $table->foreignId('cabinet_id')->nullable()->after('id')->constrained('cabinets')->cascadeOnDelete();
        });

        // 6. Backfill existing records with a default cabinet if records exist
        $hasUsers = DB::table('users')->exists();
        $hasPatients = DB::table('patients')->exists();
        $hasLabels = DB::table('labels')->exists();

        if ($hasUsers || $hasPatients || $hasLabels) {
            $defaultCabinetId = DB::table('cabinets')->insertGetId([
                'name' => 'Cabinet Dentaire Principal',
                'address' => '12 Rue de la Paix, 75002 Paris',
                'phone' => '+33 1 42 68 00 00',
                'email' => 'contact@cabinetdentaire.fr',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')
                ->whereNull('cabinet_id')
                ->where('role', '!=', 'super_admin')
                ->update(['cabinet_id' => $defaultCabinetId]);

            DB::table('patients')
                ->whereNull('cabinet_id')
                ->update(['cabinet_id' => $defaultCabinetId]);

            DB::table('labels')
                ->whereNull('cabinet_id')
                ->update(['cabinet_id' => $defaultCabinetId]);

            DB::table('instrument_usages')
                ->whereNull('cabinet_id')
                ->update(['cabinet_id' => $defaultCabinetId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instrument_usages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cabinet_id');
        });

        Schema::table('labels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cabinet_id');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cabinet_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cabinet_id');
        });

        Schema::dropIfExists('cabinets');
    }
};
