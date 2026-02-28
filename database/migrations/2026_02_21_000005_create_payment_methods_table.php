<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // GoPay, BCA, QRIS
            $table->enum('type', ['ewallet', 'bank', 'qris'])->default('bank');
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->enum('fee_type', ['fixed', 'percent'])->default('fixed');
            $table->string('logo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
