<?php

namespace Carloschnouda1\Newsletter\Http\Controllers;

use Carloschnouda1\Newsletter\Facades\Newsletter;
use Carloschnouda1\Newsletter\Http\Requests\SubscribeRequest;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Illuminate\Routing\Controller;

class NewsletterSubscriptionController extends Controller
{
    public function store(SubscribeRequest $request)
    {
        Newsletter::subscribe($request->validated()['email']);

        return back()->with('status', 'Check your inbox to confirm your subscription.');
    }

    public function confirm(string $token)
    {
        $confirmed = Newsletter::confirm($token);

        return redirect(config('newsletter.redirect_after_confirm', '/'))
            ->with('status', $confirmed ? 'Subscription confirmed!' : 'Invalid or expired confirmation link.');
    }

    public function unsubscribe(int $id)
    {
        // The signature has already been validated by the route middleware.
        // The response is the same whether or not the subscriber still exists.
        $subscription = NewsletterSubscription::find($id);

        if ($subscription) {
            Newsletter::unsubscribe($subscription);
        }

        return redirect(config('newsletter.redirect_after_unsubscribe', '/'))
            ->with('status', 'You have been unsubscribed.');
    }
}
