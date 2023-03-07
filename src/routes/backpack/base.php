<?php

use Backpack\CRUD\app\Http\Controllers\AdminController;
use Backpack\CRUD\app\Http\Controllers\Auth\ForgotPasswordController;
use Backpack\CRUD\app\Http\Controllers\Auth\LoginController;
use Backpack\CRUD\app\Http\Controllers\Auth\RegisterController;
use Backpack\CRUD\app\Http\Controllers\Auth\ResetPasswordController;
use Backpack\CRUD\app\Http\Controllers\MyAccountController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backpack\Base Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are
| handled by the Backpack\Base package.
|
*/

Route::group(
[
    'middleware' => config('backpack.base.web_middleware', 'web'),
    'prefix'     => config('backpack.base.route_prefix'),
],
function () {
    // if not otherwise configured, setup the auth routes
    if (config('backpack.base.setup_auth_routes')) {
        // Authentication Routes...
        Route::group([
            'controller' => LoginController::class,
        ], function (): void {
            Route::get('login', 'showLoginForm')->name('backpack.auth.login');
            Route::post('login', 'login');
            Route::get('logout', 'logout')->name('backpack.auth.logout');
            Route::post('logout', 'logout');
        });

        // Registration Routes...
        Route::group([
            'controller' => RegisterController::class,
        ], function (): void {
            Route::get('register', 'showRegistrationForm')->name('backpack.auth.register');
            Route::post('register', 'register');
        });

        // if not otherwise configured, setup the password recovery routes
        if (config('backpack.base.setup_password_recovery_routes', true)) {
            Route::group([
                'controller' => ForgotPasswordController::class,
            ], function (): void {
                Route::get('password/reset', 'showLinkRequestForm')->name('backpack.auth.password.reset');
                Route::post('password/email', 'sendResetLinkEmail')->name('backpack.auth.password.email')->middleware('backpack.throttle.password.recovery:'.config('backpack.base.password_recovery_throttle_access'));
            });
            Route::group([
                'controller' => ResetPasswordController::class,
            ], function (): void {
                Route::get('password/reset/{token}', 'showResetForm')->name('backpack.auth.password.reset.token');
                Route::post('password/reset', 'reset');
            });
        }
    }

    // if not otherwise configured, setup the dashboard routes
    if (config('backpack.base.setup_dashboard_routes')) {
        Route::group([
            'controller' => AdminController::class,
        ], function (): void {
            Route::get('/', 'redirect')->name('backpack');
            Route::get('dashboard', 'dashboard')->name('backpack.dashboard');
        });
    }

    // if not otherwise configured, setup the "my account" routes
    if (config('backpack.base.setup_my_account_routes')) {
        Route::group([
            'controller' => MyAccountController::class,
        ], function (): void {
            Route::get('edit-account-info', 'getAccountInfoForm')->name('backpack.account.info');
            Route::post('edit-account-info', 'postAccountInfoForm')->name('backpack.account.info.store');
            Route::post('change-password', 'postChangePasswordForm')->name('backpack.account.password');
        });
    }
});
