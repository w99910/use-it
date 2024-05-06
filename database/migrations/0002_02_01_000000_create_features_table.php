<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('use_it_features', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 256)->unique();
            $table->string('description', length: 256);
            $table->enum('type', FeatureType::values());
            $table->json('meta')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('use_it_features');
    }
};
