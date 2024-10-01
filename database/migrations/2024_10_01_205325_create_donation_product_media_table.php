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
        Schema::create('donation_product_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donation_product_id');
            $table->string('title')->nullable();
            $table->enum('type', ['image', 'video']);
            $table->string('url');
            $table->timestamps();

            $table->foreign('donation_product_id')->references('id')->on('donation_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_product_media');
    }
};
