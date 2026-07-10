<?php

namespace Carloschnouda1\Newsletter\Mail;

use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscription $subscription)
    {
    }

    public function build()
    {
        return $this->subject('Confirm your subscription')
            ->view('newsletter::emails.confirmation', [
                'confirmUrl' => route('newsletter.confirm', $this->subscription->token),
            ]);
    }
}
