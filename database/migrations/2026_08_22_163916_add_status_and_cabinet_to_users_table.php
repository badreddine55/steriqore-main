<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
            if (! Schema::hasColumn('users', 'cabinet_name')) {
                $table->string('cabinet_name')->nullable()->default('Cabinet Dentaire')->after('is_active');
            }
            if (! Schema::hasColumn('users', 'cabinet_room')) {
                $table->string('cabinet_room')->nullable()->default('Fauteuil 1')->after('cabinet_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'cabinet_name', 'cabinet_room']);
        });
    }
};
