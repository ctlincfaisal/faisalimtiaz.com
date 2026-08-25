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
    return view('studio.home');
})->name('studio');

Route::get('testimonials', function () {
    return view('studio.testimonials');
})->name('studio.testimonials');

Route::get('work/{slug}', function (string $slug) {
    $project = collect(config('projects'))->firstWhere('slug', $slug);
    abort_unless($project, 404);

    return view('studio.project', ['project' => $project]);
})->name('studio.work');

Route::get('blog', [MainController::class, 'blogIndex'])->name('blog');
Route::get('blog/{slug}', [MainController::class, 'blogPost'])->name('blog.post');

Route::post('contactus', 'App\Http\Controllers\MainController@contactus');



Route::get('aboutme', function () {
    return view('studio.about');
})->name('aboutme');

Route::get('services/mobile-app-development', [MainController::class, 'servicePage'])
    ->defaults('slug', 'mobile-app-development')
    ->name('services.mobile-app-development');
Route::get('services/react-native-development', [MainController::class, 'servicePage'])
    ->defaults('slug', 'react-native-development')
    ->name('services.react-native-development');
Route::get('services/website-development', [MainController::class, 'servicePage'])
    ->defaults('slug', 'website-development')
    ->name('services.website-development');
Route::get('services/laravel-development', [MainController::class, 'servicePage'])
    ->defaults('slug', 'laravel-development')
    ->name('services.laravel-development');
Route::get('services/seo-services', [MainController::class, 'servicePage'])
    ->defaults('slug', 'seo-services')
    ->name('services.seo-services');

Route::post('analytics/visit', [MainController::class, 'trackWebsiteVisit'])->name('analytics.visit');
Route::post('analytics/heartbeat', [MainController::class, 'trackWebsiteHeartbeat'])->name('analytics.heartbeat');
Route::post('analytics/click', [MainController::class, 'trackWebsiteClick'])->name('analytics.click');

Route::get('marketing/login', [MainController::class, 'showMarketingLogin'])->name('marketing.login');
Route::post('marketing/login', [MainController::class, 'loginMarketing'])->name('marketing.login.submit');

Route::middleware('marketing.auth')->group(function () {
    Route::get('marketing', [MainController::class, 'marketing'])->name('marketing');
    Route::get('marketing/contact-form', [MainController::class, 'marketing'])->name('marketing.contact-form');
    Route::get('marketing/contact-form/{contact}', [MainController::class, 'marketing'])->name('marketing.contact-form.show');
    Route::delete('marketing/contact-form/{contact}', [MainController::class, 'deleteContactForm'])->name('marketing.contact-form.destroy');
    Route::post('marketing/logout', [MainController::class, 'logoutMarketing'])->name('marketing.logout');
    Route::post('marketing/send', [MainController::class, 'sendMarketingEmail'])->name('marketing.send');
    Route::post('marketing/followups', [MainController::class, 'storeMarketingFollowup'])->name('marketing.followups.store');
    Route::put('marketing/followups/{followup}', [MainController::class, 'updateMarketingFollowup'])->name('marketing.followups.update');
    Route::delete('marketing/followups/{followup}', [MainController::class, 'deleteMarketingFollowup'])->name('marketing.followups.delete');
    Route::post('marketing/templates', [MainController::class, 'storeMarketingTemplate'])->name('marketing.templates.store');
    Route::put('marketing/templates/{template}', [MainController::class, 'updateMarketingTemplate'])->name('marketing.templates.update');
    Route::delete('marketing/templates/{template}', [MainController::class, 'deleteMarketingTemplate'])->name('marketing.templates.delete');
});

Route::get('marketing/debug-image', [MainController::class, 'marketingDebugImage'])->name('marketing.debug-image');
Route::get('marketing/open/{trackingId}', [MainController::class, 'trackMarketingOpen'])->name('marketing.open');
Route::get('marketing/followup-open/{trackingId}', [MainController::class, 'trackMarketingFollowupOpen'])->name('marketing.followup-open');
Route::match(['get', 'post'], 'marketing/unsubscribe', [MainController::class, 'unsubscribeMarketingEmail'])->name('marketing.unsubscribe');
