<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface;
use ThomasBrillion\UseIt\Models\Ability;
use ThomasBrillion\UseIt\Models\Consumption;
use ThomasBrillion\UseIt\Models\Feature;
use ThomasBrillion\UseIt\Models\FeatureGroup;
use ThomasBrillion\UseIt\Models\Usage;
use ThomasBrillion\UseIt\Services\AbilityService;
use ThomasBrillion\UseIt\Services\ConsumptionService;
use ThomasBrillion\UseIt\Services\FeatureGroupService;
use ThomasBrillion\UseIt\Services\FeatureService;
use ThomasBrillion\UseIt\Support\Enums\FeatureType;
use ThomasBrillion\UseIt\Support\ModelResolver;

require_once __DIR__ . '/User.php';

it('can create ability feature and grant user to it', function () {
    $user = User::create();

    $featureService = FeatureService::of($user);

    $feature = FeatureService::create(
        'Feature1',
        'Feature is new',
        FeatureType::Ability
    );

    expect($feature)->toBeInstanceOf(Feature::class)
        ->and($feature->name)->toBe('Feature1');

    $expireAt = (new DateTime())->add(DateInterval::createFromDateString('1 day'));
    $response = $featureService->grantFeature($feature, $expireAt);

    expect($response)->toBeInstanceOf(Ability::class)
        ->and($featureService->try($feature))->toBeTrue()
        ->and($user->canUseFeature('Feature1'))->toBeTrue();

});

it('can create quantity feature and consume it', function () {
    $user = User::first();

    $featureService = FeatureService::of($user);

    $feature = FeatureService::create(
        'Feature2',
        'Feature is new',
        FeatureType::Quantity,
    );

    expect($feature)->toBeInstanceOf(Feature::class)
        ->and($feature->name)->toBe('Feature2');

    $expireAt = (new DateTime())->add(DateInterval::createFromDateString('1 day'));

    // throw error if total is not specified for quantitative feature
    try {
        $featureService->grantFeature($feature, $expireAt);
    } catch (\Exception $exception) {
        expect($exception->getMessage())->toBe('Please specify total to create usage');
    }

    $response = $featureService->grantFeature($feature, $expireAt, 100);
    expect($user->canUseFeature('Feature2', 10))->toBeTrue()
        ->and($response)->toBeInstanceOf(Usage::class)
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

    $featureService = FeatureService::of($user);

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

it('can disable/enable feature', function () {
    $user = User::first();

    $featureService = FeatureService::of($user);
    $feature = Feature::first();
    $featureService->grantFeature($feature->name, new DateTime('1month'));
    expect($feature->disabled)->toBeFalse();

    $featureService->disableFeature($feature);
    expect($feature->refresh()->disabled)->toBeTrue()
        ->and($user->canUseFeature($feature->name))->toBeFalse();

    $featureService->enableFeature($feature);
    expect($feature->refresh()->disabled)->toBeFalse()
        ->and($user->canUseFeature($feature->name))->toBeTrue();
});

it('can list features with meta data', function () {
    $user = User::create();
    $featureService = FeatureService::of($user);

    $feature = FeatureService::create(
        'Fruit Feature',
        'Feature is new',
        FeatureType::Ability,
        [
            'fruit' => 'apple',
        ]
    );
    expect($featureService->listFeatures([
        'fruit' => ['apple']
    ])->count())->toBe(1);

    expect($featureService->listFeatures([
        'fruit' => ['hello', 'world']
    ])->count())->toBe(0);

    // expect()->toBe($feature);
});

it('can create feature group', function () {
    $user = User::create();

    $featureGroup = FeatureGroupService::create(
        'normal',
        'normal user group'
    );
    expect($featureGroup)->toBeInstanceOf(FeatureGroup::class)->and($featureGroup->name)->toBe('normal');
});

it('can add features to feature group', function () {
    $user = User::create();

    $featureGroup = FeatureGroupService::create(
        'premium-plan',
        'gives access to premium features'
    );

    $fourTeamMembersFeature = FeatureService::create('add-team-members', 'add four team members', FeatureType::Quantity, total: 4, expireInSeconds: 60 * 60 * 24 * 30);
    $fourTeraByteStorage = FeatureService::create('use-four-terabyte-storage', 'use 4TB storage', FeatureType::Quantity, total: 4194304, expireInSeconds: 60 * 60 * 24 * 30);
    $AIPoweredModelAccess = FeatureService::create('ai-powered-model', 'get access to AI powered model', FeatureType::Ability, expireInSeconds: 60 * 60 * 24 * 30);

    FeatureGroupService::addFeatures($featureGroup, [
        $fourTeamMembersFeature,
        $fourTeraByteStorage,
        $AIPoweredModelAccess,
    ]);

    expect(FeatureGroupService::hasFeature($featureGroup, $fourTeamMembersFeature))->toBeTrue();
    expect(FeatureGroupService::hasFeature($featureGroup, $fourTeraByteStorage))->toBeTrue();
    expect(FeatureGroupService::hasFeature($featureGroup, $AIPoweredModelAccess))->toBeTrue();
});

it('can grant user access to feature group', function () {
    $user = User::first();

    FeatureGroupService::of($user)->grantFeatureGroup('premium-plan');

    expect($user->try('add-team-members', 1))->toBeTrue();
    expect($user->try('use-four-terabyte-storage', 2000))->toBeTrue();
    expect($user->try('ai-powered-model'))->toBeTrue();
});

it('can revoke user access to feature group', function () {
    $user = User::first();

    FeatureGroupService::of($user)->revokeFeatureGroup('premium-plan');

    expect($user->canUseFeature('add-team-members', 1))->toBeFalse();
    expect($user->canUseFeature('use-four-terabyte-storage', 2000))->toBeFalse();
    expect($user->canUseFeature('ai-powered-model'))->toBeFalse();
});

it('can remove features to feature group', function () {
    FeatureGroupService::removeFeature('premium-plan', 'add-team-members');
    FeatureGroupService::removeFeature('premium-plan', 'ai-powered-model');
    expect(FeatureGroupService::hasFeature('premium-plan', 'add-team-members'))->toBeFalse();
    expect(FeatureGroupService::hasFeature('premium-plan', 'ai-powered-model'))->toBeFalse();
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

        public function getId(): string|int
        {
            return $this->id;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getType(): FeatureType
        {
            return $this->type;
        }

        public function isDisabled(): bool
        {
            return $this->disabled;
        }

        public function toggleDisability(): bool
        {
            $this->disabled = !$this->disabled;
            $this->save();

            return $this->disabled;
        }
    };

    ModelResolver::registerModel('feature', get_class($customFeature));

    expect(ModelResolver::getFeatureModel())->toBe(get_class($customFeature));
});

