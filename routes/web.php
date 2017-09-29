<?php

/**
 * WebhookController routes.
 * All webhooks should access this controller.
 */
Route::post('webhook/tracking-deliveries', 'WebhookController@trackingDeliveries');
Route::post('webhook/tracking-opened', 'WebhookController@trackingOpened');