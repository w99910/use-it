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
        Schema::create('use_it_usages', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 256);
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('creator_id');
            $table->string('creator_type', 256);
            $table->unsignedBigInteger('total'); // negative value means unlimited
            $table->bigInteger('spend')->default(0);
            $table->bigInteger('level')->default(0); // higher level will consume first
            $table->timestamp('expire_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('feature_id')->references('id')->on('use_it_features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('use_it_usages');
    }
};
