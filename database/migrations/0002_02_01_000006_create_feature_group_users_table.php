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
        Schema::create('use_it_feature_group_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_group_id')->references('id')->on('use_it_feature_groups')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('use_it_feature_group_users');
    }
};
