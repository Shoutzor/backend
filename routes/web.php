<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function() {
    // Errors are handled in middleware
    // NOT_INSTALLED: App\Http\Middleware\RequireInstalled

    return json_encode([
        'available' => true,
        'status' => 'OK'
    ]);
});
