<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::post('contactus', 'App\Http\Controllers\MainController@contactus');



Route::get('aboutme', 'App\Http\Controllers\MainController@aboutme');

Route::get('marketing', [MainController::class, 'marketing'])->name('marketing');
Route::post('marketing/send', [MainController::class, 'sendMarketingEmail'])->name('marketing.send');
Route::post('marketing/templates', [MainController::class, 'storeMarketingTemplate'])->name('marketing.templates.store');
Route::put('marketing/templates/{template}', [MainController::class, 'updateMarketingTemplate'])->name('marketing.templates.update');
Route::delete('marketing/templates/{template}', [MainController::class, 'deleteMarketingTemplate'])->name('marketing.templates.delete');
Route::get('marketing/debug-image', [MainController::class, 'marketingDebugImage'])->name('marketing.debug-image');
Route::get('marketing/open/{trackingId}', [MainController::class, 'trackMarketingOpen'])->name('marketing.open');
Route::match(['get', 'post'], 'marketing/unsubscribe', [MainController::class, 'unsubscribeMarketingEmail'])->name('marketing.unsubscribe');
