<?php

namespace Carloschnouda1\Newsletter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscription extends Model
{
    protected $table = 'newsletter_subscriptions';

    protected $fillable = ['email', 'token', 'confirmed_at', 'unsubscribed_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at')->whereNull('unsubscribed_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('confirmed_at');
    }

    public function isConfirmed(): bool
    {
        return ! is_null($this->confirmed_at);
    }
}
