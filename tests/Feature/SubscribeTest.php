<?php

use Carloschnouda1\Newsletter\Facades\Newsletter;
use Carloschnouda1\Newsletter\Mail\NewsletterConfirmation;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;

beforeEach(fn () => Mail::fake());

it('creates a pending subscription and sends a confirmation email', function () {
    $subscription = Newsletter::subscribe('jane@example.com');

    expect($subscription->email)->toBe('jane@example.com')
        ->and($subscription->token)->toHaveLength(40)
        ->and($subscription->confirmed_at)->toBeNull();

    Mail::assertSent(NewsletterConfirmation::class, fn ($mail) => $mail->hasTo('jane@example.com'));
    expect(Newsletter::isSubscribed('jane@example.com'))->toBeFalse();
});

it('normalizes email addresses so casing does not create duplicates', function () {
    Newsletter::subscribe('  Jane@Example.COM ');
    Newsletter::subscribe('JANE@example.com');

    expect(NewsletterSubscription::count())->toBe(1)
        ->and(NewsletterSubscription::first()->email)->toBe('jane@example.com');
});

it('sends a new confirmation with a fresh token while the subscriber is still pending', function () {
    $first = Newsletter::subscribe('jane@example.com')->token;
    $second = Newsletter::subscribe('jane@example.com')->token;

    expect($second)->not->toBe($first);
    Mail::assertSent(NewsletterConfirmation::class, 2);
});

it('does nothing when the subscriber is already confirmed', function () {
    $subscription = confirmedSubscriber();

    Newsletter::subscribe($subscription->email);

    Mail::assertNothingSent();
    expect(Newsletter::isSubscribed($subscription->email))->toBeTrue();
});

it('skips the confirmation email when send_confirmation is disabled', function () {
    config(['newsletter.send_confirmation' => false]);

    Newsletter::subscribe('jane@example.com');

    Mail::assertNothingSent();
});

it('renders a confirmation email that links to the confirm route', function () {
    $subscription = Newsletter::subscribe('jane@example.com');

    $html = (new NewsletterConfirmation($subscription))->render();

    expect($html)->toContain(route('newsletter.confirm', $subscription->token));
});

it('subscribes through the form endpoint', function () {
    $this->post(route('newsletter.subscribe'), ['email' => 'jane@example.com'])
        ->assertRedirect()
        ->assertSessionHas('status', 'Check your inbox to confirm your subscription.');

    expect(NewsletterSubscription::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('rejects invalid email addresses on the form endpoint', function () {
    $this->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');

    $this->post(route('newsletter.subscribe'), [])
        ->assertSessionHasErrors('email');

    expect(NewsletterSubscription::count())->toBe(0);
});
