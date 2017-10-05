<?php

/**
 * WebhookController routes.
 * All webhooks should access this controller.
 */
Route::post('webhook/tracking-deliveries', 'WebhookController@trackingDeliveries');
Route::post('webhook/tracking-opened', 'WebhookController@trackingOpened');
Route::post('webhook/tracking-spams', 'WebhookController@trackingSpams');
Route::post('webhook/tracking-bounces', 'WebhookController@trackingBounces');

Route::get('something', function () {
    abort(403);
});

Route::get('404', function () {
    abort(404);
});


Route::get('500', function () {
    abort(500);
});

