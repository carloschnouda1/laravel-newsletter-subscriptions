<?php

use Carloschnouda1\Newsletter\Http\Controllers\NewsletterSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('newsletter.route_prefix', 'newsletter'))->group(function () {
    Route::post('/subscribe', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');
    Route::get('/confirm/{token}', [NewsletterSubscriptionController::class, 'confirm'])->name('newsletter.confirm');
    Route::get('/unsubscribe/{email}', [NewsletterSubscriptionController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
});
