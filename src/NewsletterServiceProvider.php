<?php

namespace Carloschnouda1\Newsletter;

use Illuminate\Support\ServiceProvider;

class NewsletterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/newsletter.php', 'newsletter');

        $this->app->singleton('newsletter', function () {
            return new NewsletterManager();
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'newsletter');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/newsletter.php' => config_path('newsletter.php'),
            ], 'newsletter-config');
        }
    }
}
