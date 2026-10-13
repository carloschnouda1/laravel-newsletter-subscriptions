<?php

namespace Carloschnouda1\Newsletter;

use Carloschnouda1\Newsletter\Mail\NewsletterConfirmation;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use DateTimeInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class NewsletterManager
{
    public function subscribe(string $email): NewsletterSubscription
    {
        $email = $this->normalize($email);

        $subscription = NewsletterSubscription::firstOrNew(['email' => $email]);

        if ($this->isActive($subscription)) {
            return $subscription;
        }

        // New subscribers, pending ones, and people who unsubscribed earlier
        // all go through (double opt-in) confirmation again.
        $subscription->token = Str::random(40);
        $subscription->confirmed_at = null;
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
        $subscription->unsubscribed_at = null;
        $subscription->token = null;
        $subscription->save();

        return true;
    }

    public function unsubscribe(string|NewsletterSubscription $subscriber): bool
    {
        $subscription = $subscriber instanceof NewsletterSubscription
            ? $subscriber
            : NewsletterSubscription::where('email', $this->normalize($subscriber))->first();

        if (! $subscription) {
            return false;
        }

        $subscription->unsubscribed_at = now();
        $subscription->save();

        return true;
    }

    public function isSubscribed(string $email): bool
    {
        return NewsletterSubscription::where('email', $this->normalize($email))->confirmed()->exists();
    }

    /**
     * A signed unsubscribe link for use in the newsletters you send.
     * It does not expire unless you pass an expiry.
     */
    public function unsubscribeUrl(NewsletterSubscription $subscription, ?DateTimeInterface $expiresAt = null): string
    {
        $parameters = ['id' => $subscription->getKey()];

        return $expiresAt
            ? URL::temporarySignedRoute('newsletter.unsubscribe', $expiresAt, $parameters)
            : URL::signedRoute('newsletter.unsubscribe', $parameters);
    }

    private function isActive(NewsletterSubscription $subscription): bool
    {
        return $subscription->exists
            && $subscription->isConfirmed()
            && is_null($subscription->unsubscribed_at);
    }

    private function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }
}
