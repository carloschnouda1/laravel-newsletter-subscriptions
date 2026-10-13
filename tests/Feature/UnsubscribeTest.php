<?php

use Carloschnouda1\Newsletter\Facades\Newsletter;
use Carloschnouda1\Newsletter\Mail\NewsletterConfirmation;
use Illuminate\Support\Facades\Mail;

it('unsubscribes through a valid signed link', function () {
    $subscription = confirmedSubscriber('jane@example.com');

    $this->get(Newsletter::unsubscribeUrl($subscription))
        ->assertRedirect('/')
        ->assertSessionHas('status', 'You have been unsubscribed.');

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeFalse();
});

it('refuses an unsigned unsubscribe link', function () {
    $subscription = confirmedSubscriber('jane@example.com');

    $this->get(route('newsletter.unsubscribe', ['id' => $subscription->id]))
        ->assertForbidden();

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('refuses a signed link whose id was changed to another subscriber', function () {
    $jane = confirmedSubscriber('jane@example.com');
    $john = confirmedSubscriber('john@example.com');

    $tampered = str_replace(
        '/unsubscribe/'.$jane->id.'?',
        '/unsubscribe/'.$john->id.'?',
        Newsletter::unsubscribeUrl($jane)
    );

    $this->get($tampered)->assertForbidden();

    expect(Newsletter::isSubscribed('john@example.com'))->toBeTrue()
        ->and(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('refuses the old unsubscribe-by-email style link', function () {
    confirmedSubscriber('jane@example.com');

    $this->get('/newsletter/unsubscribe/jane@example.com')->assertNotFound();

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('refuses an expired signed link', function () {
    $subscription = confirmedSubscriber('jane@example.com');

    $url = Newsletter::unsubscribeUrl($subscription, now()->addMinute());
    $this->travel(2)->minutes();

    $this->get($url)->assertForbidden();

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});

it('responds the same way when the subscriber no longer exists', function () {
    $subscription = confirmedSubscriber('jane@example.com');
    $url = Newsletter::unsubscribeUrl($subscription);
    $subscription->delete();

    $this->get($url)
        ->assertRedirect('/')
        ->assertSessionHas('status', 'You have been unsubscribed.');
});

it('redirects to the configured location after unsubscribing', function () {
    config(['newsletter.redirect_after_unsubscribe' => '/goodbye']);
    $subscription = confirmedSubscriber('jane@example.com');

    $this->get(Newsletter::unsubscribeUrl($subscription))->assertRedirect('/goodbye');
});

it('unsubscribes programmatically by email, ignoring case', function () {
    confirmedSubscriber('jane@example.com');

    expect(Newsletter::unsubscribe('JANE@example.com'))->toBeTrue()
        ->and(Newsletter::isSubscribed('jane@example.com'))->toBeFalse()
        ->and(Newsletter::unsubscribe('nobody@example.com'))->toBeFalse();
});

it('lets someone who unsubscribed subscribe again, with a fresh confirmation', function () {
    $subscription = confirmedSubscriber('jane@example.com');
    Newsletter::unsubscribe($subscription);

    Mail::fake();
    $again = Newsletter::subscribe('jane@example.com');

    Mail::assertSent(NewsletterConfirmation::class, fn ($mail) => $mail->hasTo('jane@example.com'));
    expect($again->confirmed_at)->toBeNull()
        ->and($again->unsubscribed_at)->toBeNull()
        ->and(Newsletter::isSubscribed('jane@example.com'))->toBeFalse();

    Newsletter::confirm($again->token);

    expect(Newsletter::isSubscribed('jane@example.com'))->toBeTrue();
});
