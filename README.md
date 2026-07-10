# Laravel Newsletter Subscriptions

A lightweight newsletter subscription package for Laravel with double opt-in confirmation and unsubscribe handling. No queues, no external services required — just a table, a facade, and three routes.

## Features

- Email subscribe endpoint with validation
- Double opt-in confirmation via a signed token
- Unsubscribe endpoint
- Simple `Newsletter` facade for use anywhere in your app
- Fully configurable route prefix and redirect targets

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

The package registers three routes out of the box (prefix configurable, default `newsletter`):

| Method | URI                          | Name                  |
|--------|------------------------------|-----------------------|
| POST   | `/newsletter/subscribe`      | `newsletter.subscribe`|
| GET    | `/newsletter/confirm/{token}`| `newsletter.confirm`  |
| GET    | `/newsletter/unsubscribe/{email}` | `newsletter.unsubscribe` |

Point a simple form at the subscribe route:

```blade
<form method="POST" action="{{ route('newsletter.subscribe') }}">
    @csrf
    <input type="email" name="email" required>
    <button type="submit">Subscribe</button>
</form>
```

### Via the facade

```php
use Carloschnouda1\Newsletter\Facades\Newsletter;

Newsletter::subscribe('someone@example.com');
Newsletter::confirm($token);
Newsletter::unsubscribe('someone@example.com');
Newsletter::isSubscribed('someone@example.com');
```

## Configuration

`config/newsletter.php`:

```php
return [
    'route_prefix' => 'newsletter',
    'send_confirmation' => true,
    'redirect_after_confirm' => '/',
    'redirect_after_unsubscribe' => '/',
];
```

Set `send_confirmation` to `false` to skip the double opt-in step and mark subscribers as confirmed manually.

## License

MIT
