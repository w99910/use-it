<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('use_it_consumptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('consumer_id');
            $table->string('consumer_type', 256);
            $table->unsignedBigInteger('usage_id');
            $table->bigInteger('amount');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('usage_id')->references('id')->on('use_it_usages');
            $table->index(['consumer_id', 'consumer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('use_it_consumptions');
    }
};
