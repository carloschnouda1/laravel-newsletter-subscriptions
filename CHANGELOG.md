# Changelog

All notable changes to this package are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses
[Semantic Versioning](https://semver.org/).

## [0.1.0] - 2026-10-13

First tagged release.

### Added

- Double opt-in subscribe and confirm flow with a one-time token
- Signed, non-guessable unsubscribe links via `Newsletter::unsubscribeUrl($subscription)`
  (optionally with an expiry)
- `Newsletter` facade: `subscribe`, `confirm`, `unsubscribe`, `isSubscribed`, `unsubscribeUrl`
- `middleware` config option (defaults to `['web']`) so the routes get sessions and CSRF protection
- Pest test suite built on Orchestra Testbench
- GitHub Actions workflow testing PHP 8.2 and 8.3 against Laravel 11 and 12
- Laravel 12 support (Laravel 10 is end-of-life and not supported; PHP 8.2 or higher is required)

### Changed

- The unsubscribe route is now `GET /{prefix}/unsubscribe/{id}` and must be signed.
  Unsigned links, or links with a changed id, return `403`.
- Email addresses are trimmed and lowercased, so `Jane@Example.com` and `jane@example.com`
  are the same subscriber.
- `Newsletter::unsubscribe()` accepts an email address or a `NewsletterSubscription`.
- Added an index on the `token` column.

### Fixed

- Someone who had unsubscribed could not subscribe again.
- Unsubscribing no longer works with just an email address in the URL, which let anyone
  unsubscribe anyone else.
