<?php
/**
 * Authentication routes.
 */
Route::get('auth/token', 'AuthController@refreshToken');
Route::post('auth', 'AuthController@authenticate');

/**
 * Invite register
 */
Route::post('users/registration/validate', 'UserRegistrationController@isValid');
Route::post('users/register/{user}', 'UserController@register');

/**
 * PasswordResetController routes.
 */
Route::post('password/request', 'PasswordResetController@sendResetLinkEmail');
Route::post('password/reset', 'PasswordResetController@resetPassword');

/**
 * All the routes in this group will need to send a Header
 * Authorization with a valide token, withou this the user will
 * not be authorized to access the route.
 */
Route::group(['middleware' => 'auth:api'], function () {
    /**
     * AuthController routes.
     */
    Route::post('auth/user', 'AuthController@getAuthenticatedUser');
    Route::post('auth/authorization', 'AuthController@isAuthorized');

    /**
     * UserController routes.
     */
    Route::post('users/{user}/permissions', 'UserController@togglePermission');
    Route::post('users/profile', 'UserController@updateProfile');
    Route::post('users/profile/password', 'UserController@updatePassword');

    Route::get('users', 'UserController@index');
    Route::post('users', 'UserController@add');
    Route::delete('users/{user}', 'UserController@revoke');

    /**
     * UserSettingsController routes.
     */
    Route::get('users/settings/notifications', 'UserSettingsController@notifications');
    Route::post('users/settings/notifications', 'UserSettingsController@toggleNotification');

    /**
     * PermissionController routes
     */
    Route::get('permissions', 'PermissionController@index');

    /**
     * NotificationController routes
     */
    Route::get('notifications', 'NotificationController@unread');
    Route::post('notifications/read', 'NotificationController@read');

    /**
     * CompanyController routes.
     */
    Route::get('companies/import/download', 'CompanyController@downloadImportSheet');
    Route::get('companies/total', 'CompanyController@total');
    Route::get('companies/{company}/contacts', 'CompanyController@contacts');
    Route::get('companies/{company}/monthly-opened-documents', 'CompanyController@monthlyOpenedDocuments');
    Route::post('companies/import', 'CompanyController@import');

    Route::get('companies', 'CompanyController@index');
    Route::get('companies/{company}', 'CompanyController@show');
    Route::post('companies', 'CompanyController@store');
    Route::patch('companies/{company}', 'CompanyController@update');
    Route::delete('companies', 'CompanyController@destroy');

    /**
     * ContactController routes.
     */
    Route::get('contacts/import/download', 'ContactController@downloadImportSheet');
    Route::get('contacts/total', 'ContactController@total');
    Route::get('contacts/{contact}/companies', 'ContactController@companies');
    Route::get('contacts/{contact}/monthly-opened-documents', 'ContactController@monthlyOpenedDocuments');
    Route::post('contacts/{company}/add', 'ContactController@addToCompany');
    Route::post('contacts/{company}/{contact}/revoke', 'ContactController@revokeFromCompany');
    Route::post('contacts/import', 'ContactController@import');

    Route::get('contacts', 'ContactController@index');
    Route::get('contacts/{contact}', 'ContactController@show');
    Route::post('contacts', 'ContactController@store');
    Route::patch('contacts/{contact}', 'ContactController@update');
    Route::delete('contacts', 'ContactController@destroy');


    /**
     * DocumentController routes.
     */
    Route::get('documents/{document}/protocol', 'DocumentController@protocol');
    Route::get('documents/total', 'DocumentController@total');
    Route::get('documents/{document}/download', 'DocumentController@download');
    Route::get('documents/{document}/visualize', 'DocumentController@visualize');

    Route::get('documents/{document}', 'DocumentController@show');
    Route::patch('documents/{document}', 'DocumentController@update');
    Route::delete('documents/{document}', 'DocumentController@destroy');

    /**
     * UPDriveController routes.
     */
    Route::get('updrive/tracking', 'UPDriveController@tracking');
    Route::get('updrive/companies', 'UPDriveController@companies');
    Route::get('updrive/pending', 'UPDriveController@pending');
    Route::get('updrive/documents', 'UPDriveController@documents');
    Route::get('updrive/amounts', 'UPDriveController@amounts');
    Route::post('updrive/send', 'UPDriveController@send');

    /**
     * AccountController routes
     */
    Route::get('account', 'AccountController@show');

    /**
     * DashboardController routes
     */
    Route::get('dashboard/overview', 'DashboardController@getOverview');
    Route::get('dashboard/pending-documents', 'DashboardController@getPendingDocuments');
    Route::get('dashboard/metrics', 'DashboardController@getMetrics');


});
