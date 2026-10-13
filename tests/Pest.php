<?php

use Carloschnouda1\Newsletter\Facades\Newsletter;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Carloschnouda1\Newsletter\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

uses(TestCase::class)->in(__DIR__);

/**
 * Create a confirmed subscriber without leaving mail assertions behind.
 */
function confirmedSubscriber(string $email = 'jane@example.com'): NewsletterSubscription
{
    Mail::fake();

    $subscription = Newsletter::subscribe($email);
    Newsletter::confirm($subscription->token);

    Mail::fake();

    return $subscription->fresh();
}
