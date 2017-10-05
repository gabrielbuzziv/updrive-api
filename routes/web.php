<?php

/**
 * WebhookController routes.
 * All webhooks should access this controller.
 */
Route::post('webhook/tracking-deliveries', 'WebhookController@trackingDeliveries');
Route::post('webhook/tracking-opened', 'WebhookController@trackingOpened');
Route::post('webhook/tracking-spams', 'WebhookController@trackingSpams');
Route::post('webhook/tracking-bounces', 'WebhookController@trackingBounces');
