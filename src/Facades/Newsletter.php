<?php

namespace Carloschnouda1\Newsletter\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Carloschnouda1\Newsletter\Models\NewsletterSubscription subscribe(string $email)
 * @method static bool confirm(string $token)
 * @method static bool unsubscribe(string|\Carloschnouda1\Newsletter\Models\NewsletterSubscription $subscriber)
 * @method static bool isSubscribed(string $email)
 * @method static string unsubscribeUrl(\Carloschnouda1\Newsletter\Models\NewsletterSubscription $subscription, ?\DateTimeInterface $expiresAt = null)
 */
class Newsletter extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'newsletter';
    }
}
