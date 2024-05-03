<?php

require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;

$databasePath = __DIR__.'/../database.sqlite';

if (! file_exists($databasePath)) {
    exec("touch $databasePath");
}

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => $databasePath,
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

$schema = $capsule->schema();

$schema->dropIfExists('use_it_users');
$schema->create('use_it_users', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
});


// Features table
$schema->dropIfExists('use_it_features');
$schema->create('use_it_features', function (Blueprint $table) {
    $table->id();
    $table->string('name', length: 256);
    $table->string('description', length: 256);
    $table->enum('type', FeatureType::values());
    $table->bigInteger('quantity')->nullable();
    $table->json('meta')->nullable();
    $table->boolean('disabled')->default(false);
    $table->timestamps();
});


// Usages table
$schema->dropIfExists('use_it_usages');
$schema->create('use_it_usages', function (Blueprint $table) {
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


// Abilities table
$schema->dropIfExists('use_it_abilities');
$schema->create('use_it_abilities', function (Blueprint $table) {
    $table->id();
    $table->string('name', length: 256);
    $table->unsignedBigInteger('feature_id');
    $table->unsignedBigInteger('creator_id');
    $table->string('creator_type', 256);
    $table->timestamp('expire_at');
    $table->json('meta')->nullable();
    $table->timestamps();

    $table->foreign('feature_id')->references('id')->on('use_it_features');
});


// Consumptions table
$schema->dropIfExists('use_it_consumptions');
$schema->create('use_it_consumptions', function (Blueprint $table) {
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
