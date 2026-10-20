<?php

use Carloschnouda1\Newsletter\Facades\Newsletter;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

it('confirms a subscription with a valid token', function () {
    $subscription = Newsletter::subscribe('jane@example.com');

    expect(Newsletter::confirm($subscription->token))->toBeTrue();

    $subscription->refresh();
    expect($subscription->confirmed_at)->not->toBeNull()
        ->and($subscription->token)->toBeNull()
        ->and(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('rejects an unknown token', function () {
    expect(Newsletter::confirm('does-not-exist'))->toBeFalse();
});

it('cannot reuse a confirmation token', function () {
    $token = Newsletter::subscribe('jane@example.com')->token;

    Newsletter::confirm($token);

    expect(Newsletter::confirm($token))->toBeFalse();
});

it('confirms through the confirm route and redirects', function () {
    $subscription = Newsletter::subscribe('jane@example.com');

    $this->get(route('newsletter.confirm', $subscription->token))
        ->assertRedirect('/')
        ->assertSessionHas('status', 'Subscription confirmed!');

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('shows an error for an invalid confirmation link', function () {
    $this->get(route('newsletter.confirm', 'bogus-token'))
        ->assertRedirect('/')
        ->assertSessionHas('status', 'Invalid or expired confirmation link.');
});

it('redirects to the configured location after confirming', function () {
    config(['newsletter.redirect_after_confirm' => '/thanks']);
    $subscription = Newsletter::subscribe('jane@example.com');

    $this->get(route('newsletter.confirm', $subscription->token))->assertRedirect('/thanks');
});
