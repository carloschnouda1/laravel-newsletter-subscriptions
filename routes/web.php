<?php

use Carloschnouda1\Newsletter\Http\Controllers\NewsletterSubscriptionController;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Route;

Route::prefix(config('newsletter.route_prefix', 'newsletter'))
    ->middleware(config('newsletter.middleware', ['web']))
    ->group(function () {
        Route::post('/subscribe', [NewsletterSubscriptionController::class, 'store'])
            ->name('newsletter.subscribe');

        Route::get('/confirm/{token}', [NewsletterSubscriptionController::class, 'confirm'])
            ->name('newsletter.confirm');

        // Unsubscribe links must be signed, so nobody can unsubscribe someone else.
        // Generate them with Newsletter::unsubscribeUrl($subscription).
        Route::get('/unsubscribe/{id}', [NewsletterSubscriptionController::class, 'unsubscribe'])
            ->whereNumber('id')
            ->middleware(ValidateSignature::class)
            ->name('newsletter.unsubscribe');
    });
