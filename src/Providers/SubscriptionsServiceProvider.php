<?php

declare(strict_types=1);

namespace Rinvex\Subscriptions\Providers;

use Rinvex\Subscriptions\Models\Plan;
use Illuminate\Support\ServiceProvider;
use Rinvex\Support\Traits\ConsoleTools;
use Rinvex\Subscriptions\Models\PlanFeature;
use Rinvex\Subscriptions\Models\PlanSubscription;
use Rinvex\Subscriptions\Models\PlanSubscriptionUsage;
use Rinvex\Subscriptions\Console\Commands\MigrateCommand;
use Rinvex\Subscriptions\Console\Commands\PublishCommand;
use Rinvex\Subscriptions\Console\Commands\RollbackCommand;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use ConsoleTools;

    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commandss = [
        'command.rinvex.subscriptions.migrate',
        'command.rinvex.subscriptions.publish',
        'command.rinvex.subscriptions.rollback',
    ];

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(realpath(__DIR__.'/../../config/config.php'), 'rinvex.subscriptions');

        // Bind eloquent models to IoC container
        $this->registerModels([
            'rinvex.subscriptions.plan' => Plan::class,
            'rinvex.subscriptions.plan_feature' => PlanFeature::class,
            'rinvex.subscriptions.plan_subscription' => PlanSubscription::class,
            'rinvex.subscriptions.plan_subscription_usage' => PlanSubscriptionUsage::class,
        ]);
        $this->app->singleton('command.rinvex.subscriptions.migrate', function ($app) {
            return new MigrateCommand; // Replace with the actual command class
        });
        $this->app->singleton('command.rinvex.subscriptions.publish', function ($app) {
            return new PublishCommand; // Replace with the actual command class
        });
        $this->app->singleton('command.rinvex.subscriptions.rollback', function ($app) {
            return new RollbackCommand; // Replace with the actual command class
        });

        // Register console commands
        $this->commands($this->commandss);
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish Resources
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('rinvex.subscriptions.php'),
        ], 'rinvex/subscriptions::config');
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations/rinvex/laravel-subscriptions')
        ], 'rinvex/subscriptions::migrations');
        if (! $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        }
    }
}