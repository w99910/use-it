<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Services\ConsumptionService;
use ThomasBrillion\UseIt\Services\FeatureService;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;
use ThomasBrillion\UseIt\Support\ModelResolver;

require_once __DIR__.'/User.php';

it('can create ability feature and grant user to it', function () {
    $user = User::create();

    $featureService = new FeatureService($user);

    $feature = $featureService->create(
        'Feature1',
        'Feature is new',
        FeatureType::Ability
    );

    expect($feature)->toBeInstanceOf(Feature::class)
        ->and($feature->name)->toBe('Feature1');

    $expireAt = (new DateTime())->add(DateInterval::createFromDateString('1 day'));
    $response = $featureService->grantFeature($feature, $expireAt);

    expect($response)->toBeInstanceOf(Ability::class)
        ->and($featureService->try($feature))->toBeTrue();
});

it('can create quantity feature and consume it', function () {
    $user = User::first();

    $featureService = new FeatureService($user);

    try {
        $featureService->create(
            'Feature1',
            'Feature is new',
            FeatureType::Quantity
        );
    } catch (\Exception $exception) {
        expect($exception->getMessage())->toBe('Please provide quantity for quantity-typed feature');
    }

    $feature = $featureService->create(
        'Feature2',
        'Feature is new',
        FeatureType::Quantity,
        100
    );

    expect($feature)->toBeInstanceOf(Feature::class)
        ->and($feature->name)->toBe('Feature2');

    $expireAt = (new DateTime())->add(DateInterval::createFromDateString('1 day'));
    $response = $featureService->grantFeature($feature, $expireAt);

    expect($response)->toBeInstanceOf(Usage::class)
        ->and($featureService->try($feature, 10))->toBeInstanceOf(Consumption::class);
});

it('can get consumptions of usage', function () {
    $user = User::first();
    $usage = Usage::first();

    $consumptionService = new ConsumptionService($user);
    expect($consumptionService->getConsumptionsOfUsage($usage))->not->toBeEmpty();
});

it('can revoke feature', function () {
    $user = User::first();

    $featureService = new FeatureService($user);

    $abilityFeature = Feature::first();
    expect($featureService->try($abilityFeature))->toBeTrue();
    $featureService->revokeToFeature($abilityFeature);
    expect($featureService->try($abilityFeature))->toBeFalse();

    $usageFeature = Feature::skip(1)->first();
    expect($featureService->try($usageFeature, 10))->toBeInstanceOf(Consumption::class);
    $featureService->revokeToFeature($usageFeature);

    try {
        expect($featureService->try($usageFeature, 10))->toBeFalse();
    } catch (Exception $exception) {
        expect($exception->getMessage())->toBe('Cannot find usages for the feature');
    }
});


it('can register new feature model', function () {
    $customFeature = new class () extends Model implements FeatureInterface {
        protected $table = 'use_it_custom_features';

        public function usages(): HasMany
        {
            return $this->hasMany(Usage::class);
        }

        public function abilities(): HasMany
        {
            return $this->hasMany(Ability::class);
        }
    };

    ModelResolver::registerModel('feature', get_class($customFeature));

    expect(ModelResolver::getFeatureModel())->toBe(get_class($customFeature));
});
