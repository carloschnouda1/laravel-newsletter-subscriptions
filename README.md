# Laravel Newsletter Subscriptions

[![tests](https://github.com/carloschnouda1/laravel-newsletter-subscriptions/actions/workflows/tests.yml/badge.svg)](https://github.com/carloschnouda1/laravel-newsletter-subscriptions/actions/workflows/tests.yml)
[![license](https://img.shields.io/github/license/carloschnouda1/laravel-newsletter-subscriptions)](LICENSE)

A lightweight newsletter subscription package for Laravel with double opt-in confirmation and signed unsubscribe links. No queues, no external services required: just a table, a facade, and three routes.

## Features

- Email subscribe endpoint with validation
- Double opt-in confirmation via a one-time token
- Signed unsubscribe links that cannot be guessed or edited to unsubscribe someone else
- Simple `Newsletter` facade for use anywhere in your app
- Emails are trimmed and lowercased, so casing never creates duplicates
- Configurable route prefix, middleware and redirect targets

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11 or 12

## Installation

```bash
composer require carloschnouda1/laravel-newsletter-subscriptions
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=newsletter-config
```

## Usage

### Via the built-in routes

The package registers three routes (prefix configurable, default `newsletter`):

| Method | URI                              | Name                     |
|--------|----------------------------------|--------------------------|
| POST   | `/newsletter/subscribe`          | `newsletter.subscribe`   |
| GET    | `/newsletter/confirm/{token}`    | `newsletter.confirm`     |
| GET    | `/newsletter/unsubscribe/{id}`   | `newsletter.unsubscribe` (signed) |

Point a simple form at the subscribe route:

```blade
<form method="POST" action="{{ route('newsletter.subscribe') }}">
    @csrf
    <input type="email" name="email" required>
    <button type="submit">Subscribe</button>
</form>

@if (session('status'))
    <p>{{ session('status') }}</p>
@endif
```

### Unsubscribe links

The unsubscribe route only accepts signed URLs. Generate one for each subscriber when you send a newsletter:

```php
use Carloschnouda1\Newsletter\Facades\Newsletter;
use Carloschnouda1\Newsletter\Models\NewsletterSubscription;
use Illuminate\Support\Facades\Mail;

NewsletterSubscription::confirmed()->each(function (NewsletterSubscription $subscriber) {
    Mail::to($subscriber->email)->send(new MyNewsletter(
        unsubscribeUrl: Newsletter::unsubscribeUrl($subscriber),
    ));
});
```

The link does not expire. Pass a date to make it expire:

```php
Newsletter::unsubscribeUrl($subscriber, now()->addDays(30));
```

Unsigned links, links with a changed id, and expired links return `403`.

### Via the facade

```php
use Carloschnouda1\Newsletter\Facades\Newsletter;

$subscription = Newsletter::subscribe('someone@example.com'); // sends the confirmation email
Newsletter::confirm($token);
Newsletter::unsubscribe('someone@example.com'); // or pass a NewsletterSubscription
Newsletter::isSubscribed('someone@example.com'); // true only for confirmed, active subscribers
Newsletter::unsubscribeUrl($subscription);
```

Someone who unsubscribed can subscribe again; they go through confirmation again.

## Configuration

`config/newsletter.php`:

```php
return [
    'route_prefix' => 'newsletter',
    'middleware' => ['web'],
    'send_confirmation' => true,
    'redirect_after_confirm' => '/',
    'redirect_after_unsubscribe' => '/',
];
```

| Key | Description |
|-----|-------------|
| `route_prefix` | URL prefix for the package routes |
| `middleware` | Middleware for the package routes. `web` provides the session (for the `status` flash message) and CSRF protection |
| `send_confirmation` | Set to `false` to skip the double opt-in email |
| `redirect_after_confirm` | Where to redirect after confirming |
| `redirect_after_unsubscribe` | Where to redirect after unsubscribing |

## Testing

```bash
composer install
composer test
```

The suite uses Pest and Orchestra Testbench, and CI runs it on PHP 8.2 and 8.3 against Laravel 10, 11 and 12.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

MIT
