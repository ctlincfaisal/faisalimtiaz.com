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

Route::get('marketing', function () {
    return view('leadpilot-landing');
})->name('marketing.landing');

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


Route::post('marketing/paddle/webhook', [MainController::class, 'paddleWebhook'])->name('marketing.paddle.webhook');

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
Route::get('services/web-application-development', [MainController::class, 'servicePage'])
    ->defaults('slug', 'web-application-development')
    ->name('services.web-application-development');
Route::get('services/seo-services', [MainController::class, 'servicePage'])
    ->defaults('slug', 'seo-services')
    ->name('services.seo-services');

Route::post('analytics/visit', [MainController::class, 'trackWebsiteVisit'])->name('analytics.visit');
Route::post('analytics/heartbeat', [MainController::class, 'trackWebsiteHeartbeat'])->name('analytics.heartbeat');
Route::post('analytics/click', [MainController::class, 'trackWebsiteClick'])->name('analytics.click');

Route::get('marketing/login', [MainController::class, 'showMarketingLogin'])->name('marketing.login');
Route::post('marketing/login', [MainController::class, 'loginMarketing'])->name('marketing.login.submit');
Route::get('marketing/register', [MainController::class, 'showMarketingRegister'])->name('marketing.register');
Route::post('marketing/register', [MainController::class, 'registerMarketing'])->name('marketing.register.submit');

Route::middleware('marketing.auth')->group(function () {
    Route::get('marketing/onboarding', [MainController::class, 'marketingOnboarding'])->name('marketing.onboarding');
    Route::post('marketing/onboarding/plan', [MainController::class, 'saveMarketingOnboardingPlan'])->name('marketing.onboarding.plan');
    Route::post('marketing/onboarding/smtp', [MainController::class, 'saveMarketingOnboardingSmtp'])->name('marketing.onboarding.smtp');
    Route::post('marketing/onboarding/website', [MainController::class, 'saveMarketingOnboardingWebsite'])->name('marketing.onboarding.website');
    Route::post('marketing/onboarding/signature', [MainController::class, 'saveMarketingOnboardingSignature'])->name('marketing.onboarding.signature');
    Route::post('marketing/onboarding/finish', [MainController::class, 'finishMarketingOnboarding'])->name('marketing.onboarding.finish');
    Route::get('marketing/app', [MainController::class, 'marketing'])->name('marketing');
    Route::get('marketing/dashboard/live-visitors', [MainController::class, 'marketingLiveVisitors'])->name('marketing.dashboard.live-visitors');
    Route::get('marketing/contact-form', [MainController::class, 'marketing'])->name('marketing.contact-form');
    Route::get('marketing/contact-form/{contact}', [MainController::class, 'marketing'])->name('marketing.contact-form.show');
    Route::delete('marketing/contact-form/{contact}', [MainController::class, 'deleteContactForm'])->name('marketing.contact-form.destroy');
    Route::post('marketing/logout', [MainController::class, 'logoutMarketing'])->name('marketing.logout');
    Route::post('marketing/settings/websites', [MainController::class, 'storeConnectedWebsite'])->name('marketing.websites.store');
    Route::delete('marketing/settings/websites/{website}', [MainController::class, 'deleteConnectedWebsite'])->name('marketing.websites.delete');
    Route::post('marketing/settings/email-services', [MainController::class, 'storeMarketingMailService'])->name('marketing.mail-services.store');
    Route::put('marketing/settings/email-services/{service}', [MainController::class, 'updateMarketingMailService'])->name('marketing.mail-services.update');
    Route::delete('marketing/settings/email-services/{service}', [MainController::class, 'deleteMarketingMailService'])->name('marketing.mail-services.delete');
    Route::post('marketing/settings/signatures', [MainController::class, 'storeMarketingSignature'])->name('marketing.signatures.store');
    Route::put('marketing/settings/signatures/{signature}', [MainController::class, 'updateMarketingSignature'])->name('marketing.signatures.update');
    Route::delete('marketing/settings/signatures/{signature}', [MainController::class, 'deleteMarketingSignature'])->name('marketing.signatures.delete');
    Route::get('marketing/settings/signatures/{signature}/image', [MainController::class, 'marketingSignatureImage'])->name('marketing.signatures.image');
    Route::post('marketing/settings/team/moderators', [MainController::class, 'storeMarketingModerator'])->name('marketing.moderators.store');
    Route::delete('marketing/settings/team/moderators/{moderator}', [MainController::class, 'deleteMarketingModerator'])->name('marketing.moderators.delete');
    Route::post('marketing/settings/account/profile', [MainController::class, 'updateMarketingProfile'])->name('marketing.account.profile.update');
    Route::post('marketing/settings/account/password', [MainController::class, 'updateMarketingPassword'])->name('marketing.account.password.update');
    Route::post('marketing/settings/account/theme', [MainController::class, 'updateMarketingTheme'])->name('marketing.account.theme.update');
    Route::get('marketing/settings/account/profile-image/{user}', [MainController::class, 'marketingProfileImage'])->name('marketing.account.profile-image');
    Route::post('marketing/campaigns/audience', [MainController::class, 'storeMarketingCampaignAudience'])->name('marketing.campaigns.audience');
    Route::post('marketing/campaigns/content', [MainController::class, 'updateMarketingCampaignContent'])->name('marketing.campaigns.content');
    Route::post('marketing/campaigns/sequence', [MainController::class, 'updateMarketingCampaignSequence'])->name('marketing.campaigns.sequence');
    Route::post('marketing/campaigns/start', [MainController::class, 'startMarketingCampaign'])->name('marketing.campaigns.start');
    Route::post('marketing/campaigns/{campaign}/restart', [MainController::class, 'restartMarketingCampaign'])->name('marketing.campaigns.restart');
    Route::delete('marketing/campaigns/{campaign}', [MainController::class, 'deleteMarketingCampaign'])->name('marketing.campaigns.delete');
    Route::get('marketing/campaigns/{campaign}/progress', [MainController::class, 'marketingCampaignProgress'])->name('marketing.campaigns.progress');
    Route::post('marketing/campaigns/{campaign}/retry-failed', [MainController::class, 'retryMarketingCampaignEmails'])->name('marketing.campaigns.retry-failed');
    Route::get('marketing/campaign-emails/{email}', [MainController::class, 'marketingCampaignEmailDetails'])->name('marketing.campaigns.email.details');
    Route::post('marketing/campaign-emails/{email}/retry', [MainController::class, 'retryMarketingCampaignEmail'])->name('marketing.campaigns.email.retry');
    Route::post('marketing/send', [MainController::class, 'sendMarketingEmail'])->name('marketing.send');
    Route::post('marketing/email-groups', [MainController::class, 'storeMarketingEmailGroupContact'])->name('marketing.email-groups.store');
    Route::post('marketing/leads/segments', [MainController::class, 'storeMarketingLeadSegment'])->name('marketing.leads.segments.store');
    Route::post('marketing/leads/bulk', [MainController::class, 'bulkMarketingLeads'])->name('marketing.leads.bulk');
    Route::get('marketing/leads/sample-csv', [MainController::class, 'downloadMarketingLeadsSample'])->name('marketing.leads.sample');
    Route::post('marketing/leads/import/preview', [MainController::class, 'previewMarketingLeadsImport'])->name('marketing.leads.import.preview');
    Route::post('marketing/leads/import/reset', [MainController::class, 'resetMarketingLeadsImport'])->name('marketing.leads.import.reset');
    Route::post('marketing/leads/import', [MainController::class, 'importMarketingLeads'])->name('marketing.leads.import');
    Route::get('marketing/progress/{email}', [MainController::class, 'marketingProgress'])->name('marketing.progress');
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
