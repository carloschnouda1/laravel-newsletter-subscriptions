<?php

return [
    // URL prefix for the subscribe/confirm/unsubscribe routes
    'route_prefix' => 'newsletter',

    // Middleware applied to the package routes. The "web" group provides the
    // session (needed for flash messages) and CSRF protection for the subscribe form.
    'middleware' => ['web'],

    // Whether a double opt-in confirmation email is sent on subscribe
    'send_confirmation' => true,

    // Where to redirect after a successful confirmation
    'redirect_after_confirm' => '/',

    // Where to redirect after unsubscribing
    'redirect_after_unsubscribe' => '/',
];
