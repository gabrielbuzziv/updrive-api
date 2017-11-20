<?php

/**
 * WebhookController routes.
 * All webhooks should access this controller.
 */
Route::post('webhook/tracking-deliveries', 'WebhookController@trackingDeliveries');
Route::post('webhook/tracking-read', 'WebhookController@trackingRead');
Route::post('webhook/tracking-bounces', 'WebhookController@trackingBounces');
Route::post('webhook/tracking-dropped', 'WebhookController@trackingDropped');


Route::get('emails/default', function () {
    $account = \App\Account::where('slug', 'sandbox')->first();
    setActiveAccount($account);
    $company = \App\Company::first();

    return view('emails/default', [
        'subject'       => "Folha de pagamento",
        'company' => $company,
        'description'   => '
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas tincidunt mattis semper. Nulla facilisi. Nullam convallis eros et semper cursus.</p>
            <p>Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed ultrices interdum ligula, eget porttitor odio sollicitudin a. </p>
        ',
        'regards'       => [
            'name'  => 'Gabriel Buzzi Venturi',
            'email' => 'gabrielbuzziv@gmail.com',
        ],
        'token'         => '23213',
        'authorize_url' => action('AuthController@refreshToken', 'crescercontabilidade'),
        'frontend_url'  => config('app.frontend'),
        'footer'        => true,
    ]);
});