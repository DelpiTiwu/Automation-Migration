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
        Schema::create('scraping_logs', function (Blueprint $table) {
            $table->id();
            $table->string('product_name')->nullable(); // Nama produk yang dipindah
            $table->string('status'); // 'Sukses' atau 'Gagal'
            $table->text('message')->nullable(); // Catatan error kalo gagal
            $table->timestamp('scraped_at')->useCurrent(); // Jam eksekusi
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_logs');
    }
};
