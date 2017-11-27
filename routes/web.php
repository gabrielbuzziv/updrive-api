<?php

/**
 * WebhookController routes.
 * All webhooks should access this controller.
 */
Route::post('webhook/tracking-deliveries', 'WebhookController@trackingDeliveries');
Route::post('webhook/tracking-read', 'WebhookController@trackingRead');
Route::post('webhook/tracking-bounces', 'WebhookController@trackingBounces');
Route::post('webhook/tracking-dropped', 'WebhookController@trackingDropped');