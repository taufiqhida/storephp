<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // array gambar tambahan
            $table->decimal('base_price', 12, 2)->default(0); // harga dasar (terendah)
            $table->decimal('modal_price', 12, 2)->default(0); // harga modal
            $table->integer('stock')->default(0);
            $table->enum('badge', ['none', 'best_seller', 'new', 'promo', 'limited'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
