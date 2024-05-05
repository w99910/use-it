# Use-It - Features, Abilities, Usages and Consumptions

## Table Of Contents

- Introduction
- Installation
- Usage
- Testing
- Bug Report
- License
- Funding

## Introduction

- Introduce concept and implementation

### Problems

You want to create usage

Feature can be either quantity type or ability type.

Feature name must be unique.

When feature has been granted to a model ( user or team or someone else ), lets called creator, usage will be generated
for the creator.
This scenario is aimed for such situation that there is a team and when a team is subscribed to the feature, all team
members can consume the feature created by the team.

When usage has the same feature id and creator id, higher level usage will be consumed first.

## Installation

## Usage

- ### Services
- ### Middleware

In Laravel, `ThomasBrillion\UseIt\Http\Middlewares\CanUseFeatureMiddleware` is automatically registered in service
provider. You can use it in your route by using middleware alias `can-use-feature` such as

```php
Route::post('/post',[ExampleAction::class,'post'])->middleware('can-use-feature:post');
```

You can provide your auth guard as second parameter.

```php
Route::post('/post',[ExampleAction::class,'post'])->middleware('can-use-feature:post,student');
```

To check if user can consume usage of feature, you need to pass `amount` input in the request.

```text
https://example-laravel.test/post?amount=12
```

- ### Using Custom Models

You can change `Feature`, `Ability`, `Usage` and `Consumption` models by either provide custom models in config file or
register it before using it.

Your custom model must implement corresponding interface to register.

- feature: `ThomasBrillion\UseIt\Interfaces\Models\FeatureInterface`
- ability: `ThomasBrillion\UseIt\Interfaces\Models\AbilityInterface`
- usage: `ThomasBrillion\UseIt\Interfaces\Models\UsageInterface`
- consumption: `ThomasBrillion\UseIt\Interfaces\Models\ConsumptionInterface`

- #### Method A: Config File

```php
// configs/use-it.php
[
    'routes' => false,

    'models' => [
    
        // Change your custom model here
        'feature' => MyCustomFeatureModel::class,

        'ability' => \ThomasBrillion\UseIt\Models\Ability::class,

        'usage' => \ThomasBrillion\UseIt\Models\Usage::class,

        'consumption' => \ThomasBrillion\UseIt\Models\Consumption::class,
    ]
];
```

- #### Method B: Manually Register Using Resolver

You can either register your custom model using `ThomasBrillion\UseIt\Support\ModelResolver`.

```php
use ThomasBrillion\UseIt\Support\ModelResolver;

ModelResolver::registerModel('feature', MyCustomFeature::class);
```

## Testing

`composer run test`

## Bug Report

## License

## Funding

Please consider supporting me to continue contribution of open-source libraries.
