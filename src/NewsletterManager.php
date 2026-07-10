<?php

namespace Carloschnouda1\Newsletter;

use Carloschnouda1\Newsletter\Mail\NewsletterConfirmation;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterManager
{
    public function subscribe(string $email): NewsletterSubscription
    {
        $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);

        if ($subscription->exists && $subscription->isConfirmed()) {
            return $subscription;
        }

        $subscription->token = Str::random(40);
        $subscription->unsubscribed_at = null;
        $subscription->save();

        if (config('newsletter.send_confirmation', true)) {
            Mail::to($email)->send(new NewsletterConfirmation($subscription));
        }

        return $subscription;
    }

    public function confirm(string $token): bool
    {
        $subscription = NewsletterSubscription::where('token', $token)->first();

        if (! $subscription) {
            return false;
        }

        $subscription->confirmed_at = now();
        $subscription->token = null;
        $subscription->save();

        return true;
    }

    public function unsubscribe(string $email): bool
    {
        $subscription = NewsletterSubscription::where('email', $email)->first();

        if (! $subscription) {
            return false;
        }

        $subscription->unsubscribed_at = now();
        $subscription->save();

        return true;
    }

    public function isSubscribed(string $email): bool
    {
        return NewsletterSubscription::where('email', $email)->confirmed()->exists();
    }
}
