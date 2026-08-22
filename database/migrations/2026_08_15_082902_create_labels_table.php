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
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('product_name');
            $table->string('reference')->nullable();
            $table->string('lot_number')->nullable();
            $table->dateTime('expiration_date');
            $table->string('status')->default('valid'); // valid, expired, recalled, already_used, pending_validation
            $table->unsignedBigInteger('cycle_id')->nullable();
            $table->string('cycle_number')->nullable();
            $table->string('autoclave_name')->nullable();
            $table->dateTime('sterilization_date')->nullable();
            $table->text('recall_reason')->nullable();
            $table->dateTime('used_at')->nullable();
            $table->foreignId('used_by_patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('used_by_patient_name')->nullable();
            $table->string('operator_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
