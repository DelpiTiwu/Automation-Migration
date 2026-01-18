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
        Schema::create('scraped_products', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->integer('harga')->nullable();
            $table->integer('stok')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('img_url')->nullable();
            $table->string('cloud_img_url')->nullable();
            $table->string('local_image_path')->nullable();
            $table->string('original_url')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['scraped', 'downloaded', 'uploaded', 'completed'])->default('scraped');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraped_products');
    }
};
