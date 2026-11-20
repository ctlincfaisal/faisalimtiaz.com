<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Jobs\SendMarketingEmailJob;
use App\Jobs\StartMarketingCampaignJob;
use App\Models\Contact;
use App\Models\MarketingAccount;
use App\Models\MarketingBillingSubscription;
use App\Models\MarketingBillingWebhookEvent;
use App\Models\ConnectedWebsite;
use App\Models\MarketingCredential;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailGroupContact;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignStep;
use App\Models\MarketingFollowupEmail;
use App\Models\MarketingFollowupEmailOpen;
use App\Models\MarketingLead;
use App\Models\MarketingLeadSegment;
use App\Models\MarketingMailService;
use App\Models\MarketingSignature;
use App\Models\MarketingTemplate;
use App\Models\MarketingUnsubscribe;
use App\Models\User;
use App\Models\WebsiteClick;
use App\Models\WebsiteVisit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Support\MarketingHtml;
use App\Support\PaddleBilling;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Stevebauman\Location\Facades\Location;

class MainController extends Controller
{
    public function contactus(ContactRequest $request)
    {
        try {
            $nameParts = preg_split('/\s+/', trim((string) $request->firstname), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            $websiteHost = $this->websiteAnalyticsHost($request->url());
            $accountId = $websiteHost
                ? ConnectedWebsite::query()->where('domain', $websiteHost)->value('marketing_account_id')
                : null;

            Contact::create([
                'marketing_account_id' => $accountId,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $request->email,
                'budget' => $request->budget ?: 'Not specified',
                'details' => $request->details,
            ]);

            return response()->json(['msg' => 'success']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'msg' => 'error',
                'message' => 'Unable to save contact submission right now.',
            ], 500);
        }
    }

    public function paddleWebhook(Request $request)
    {
        $rawBody = $request->getContent();
        $signature = (string) $request->header('Paddle-Signature');

        if (! PaddleBilling::verifySignature($rawBody, $signature, config('marketing.paddle.webhook_secret'))) {
            return response()->json(['message' => 'Invalid Paddle webhook signature.'], 401);
        }

        $event = json_decode($rawBody, true);
        if (! is_array($event) || ! ($event['event_id'] ?? null) || ! ($event['event_type'] ?? null) || ! is_array($event['data'] ?? null)) {
            return response()->json(['message' => 'Invalid Paddle webhook payload.'], 400);
        }

        try {
            DB::transaction(function () use ($event) {
                $webhookEvent = MarketingBillingWebhookEvent::query()->firstOrCreate(
                    ['paddle_event_id' => (string) $event['event_id']],
                    [
                        'event_type' => (string) $event['event_type'],
                        'occurred_at' => $event['occurred_at'] ?? null,
                        'payload' => $event,
                    ],
                );

                if ($webhookEvent->processed_at) {
                    return;
                }

                $eventType = (string) $event['event_type'];
                if (str_starts_with($eventType, 'subscription.')) {
                    if (! $this->syncPaddleSubscription($event)) {
                        throw new \RuntimeException('Paddle subscription could not be matched to a marketing account.');
                    }
                } elseif (str_starts_with($eventType, 'customer.')) {
                    if (! $this->syncPaddleCustomer($event['data'])) {
                        throw new \RuntimeException('Paddle customer could not be matched to a marketing account.');
                    }
                } elseif (in_array($eventType, ['transaction.paid', 'transaction.completed'], true)) {
                    if (! $this->syncPaddleTransaction($event['data'])) {
                        throw new \RuntimeException('Paddle transaction could not be matched to a marketing account.');
                    }
                }

                $webhookEvent->forceFill(['processed_at' => now()])->save();
            });

            return response()->json(['received' => true]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'Paddle webhook processing failed.'], 500);
        }
    }

    private function syncPaddleCustomer(array $data): bool
    {
        $account = $this->resolvePaddleAccount($data);
        if (! $account || ! ($data['id'] ?? null)) {
            return false;
        }

        $account->forceFill(['paddle_customer_id' => (string) $data['id']])->save();

        return true;
    }

    private function syncPaddleTransaction(array $data): bool
    {
        $account = $this->resolvePaddleAccount($data);
        if (! $account) {
            return false;
        }

        $priceId = $this->paddlePriceId($data);
        $plan = PaddleBilling::planForPrice($priceId);
        $values = [];
        if (! empty($data['customer_id'])) {
            $values['paddle_customer_id'] = (string) $data['customer_id'];
        }
        if ($plan) {
            $values['plan'] = $plan;
        }

        if ($values !== []) {
            $account->forceFill($values)->save();
        }

        return true;
    }

    private function syncPaddleSubscription(array $event): bool
    {
        $data = $event['data'];
        $account = $this->resolvePaddleAccount($data);
        if (! $account || ! ($data['id'] ?? null)) {
            return false;
        }

        $occurredAt = ! empty($event['occurred_at']) ? Carbon::parse($event['occurred_at']) : now();
        $subscription = MarketingBillingSubscription::query()->firstOrNew([
            'paddle_subscription_id' => (string) $data['id'],
        ]);

        if ($subscription->exists && $subscription->last_event_at && $subscription->last_event_at->greaterThanOrEqualTo($occurredAt)) {
            return true;
        }

        $items = is_array($data['items'] ?? null) ? $data['items'] : [];
        $priceId = $this->paddlePriceId($data);
        $price = $items[0]['price'] ?? [];
        $scheduledChange = is_array($data['scheduled_change'] ?? null) ? $data['scheduled_change'] : [];
        $status = (string) ($data['status'] ?? 'active');
        $customData = $this->paddleCustomData($data);

        $subscription->fill([
            'marketing_account_id' => $account->id,
            'paddle_customer_id' => $data['customer_id'] ?? null,
            'status' => $status,
            'price_id' => $priceId,
            'product_id' => $price['product_id'] ?? ($price['product']['id'] ?? null),
            'started_at' => $data['started_at'] ?? null,
            'next_billed_at' => $data['next_billed_at'] ?? null,
            'scheduled_change_action' => $scheduledChange['action'] ?? null,
            'scheduled_change_effective_at' => $scheduledChange['effective_at'] ?? null,
            'paddle_created_at' => $data['created_at'] ?? null,
            'last_event_at' => $occurredAt,
            'items' => $items,
            'custom_data' => $customData,
        ])->save();

        $accountValues = [
            'paddle_customer_id' => $data['customer_id'] ?? $account->paddle_customer_id,
            'paddle_subscription_id' => (string) $data['id'],
            'paddle_subscription_status' => $status,
            'paddle_next_billed_at' => $data['next_billed_at'] ?? null,
            'paddle_scheduled_change_action' => $scheduledChange['action'] ?? null,
            'paddle_scheduled_change_effective_at' => $scheduledChange['effective_at'] ?? null,
            'paddle_last_event_at' => $occurredAt,
        ];

        $plan = PaddleBilling::planForPrice($priceId);
        if (in_array($status, ['active', 'trialing', 'past_due'], true) && $plan) {
            $accountValues['plan'] = $plan;
        } elseif (in_array($status, ['canceled', 'paused'], true)) {
            $hasAnotherActiveSubscription = $account->billingSubscriptions()
                ->where('paddle_subscription_id', '!=', (string) $data['id'])
                ->whereIn('status', ['active', 'trialing', 'past_due'])
                ->exists();
            if (! $hasAnotherActiveSubscription) {
                $accountValues['plan'] = 'free';
            }
        }

        $account->forceFill($accountValues)->save();

        return true;
    }

    private function resolvePaddleAccount(array $data): ?MarketingAccount
    {
        $customData = $this->paddleCustomData($data);
        $accountId = $customData['marketing_account_id'] ?? $customData['account_id'] ?? null;
        if (is_numeric($accountId)) {
            $account = MarketingAccount::query()->find((int) $accountId);
            if ($account) {
                return $account;
            }
        }

        $userId = $customData['user_id'] ?? $customData['marketing_user_id'] ?? null;
        if (is_numeric($userId)) {
            $account = User::query()->find((int) $userId)?->account;
            if ($account) {
                return $account;
            }
        }

        $customerId = $data['customer_id'] ?? null;
        if ($customerId) {
            $account = MarketingAccount::query()->where('paddle_customer_id', (string) $customerId)->first();
            if ($account) {
                return $account;
            }
        }

        $email = $data['email']
            ?? $data['customer_email']
            ?? data_get($data, 'customer.email');
        if (is_string($email) && $email !== '') {
            return User::query()->where('email', strtolower(trim($email)))->first()?->account;
        }

        return null;
    }

    private function paddleCustomData(array $data): array
    {
        $customData = $data['custom_data'] ?? $data['customData'] ?? [];

        return is_array($customData) ? $customData : [];
    }

    private function paddlePriceId(array $data): ?string
    {
        $items = $data['items'] ?? data_get($data, 'details.line_items', []);
        if (! is_array($items)) {
            return null;
        }

        $item = $items[0] ?? [];
        $priceId = $item['price_id'] ?? data_get($item, 'price.id');

        return is_string($priceId) && $priceId !== '' ? $priceId : null;
    }

    public function aboutme()
    {
        return view('aboutme');
    }

    public function servicePage(Request $request)
    {
        $pages = $this->servicePages();
        $slug = (string) $request->route('slug');

        abort_unless(array_key_exists($slug, $pages), 404);

        return view('service', [
            'page' => $pages[$slug],
        ]);
    }

    public function getcontacts()
    {
        $contacts = Contact::all();

        return json_encode(['msg' => 'success', 'data' => $contacts]);
    }

    public function showMarketingLogin(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('marketing');
        }

        return view('marketing-login');
    }

    public function showMarketingRegister(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('marketing');
        }

        return view('marketing-register');
    }

    public function loginMarketing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        $validator->validate();

        $identifier = trim((string) ($request->input('identifier') ?: $request->input('username')));
        if ($identifier === '') {
            return back()
                ->withInput()
                ->with('marketing_login_error', 'Enter your email address or username.');
        }

        $this->ensureBootstrapSuperAdmin();
        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (! $user || ! $user->marketing_account_id || ! in_array($user->role, ['super_admin', 'admin', 'moderator'], true) || ! Hash::check((string) $request->input('password'), $user->password)) {
            return back()
                ->withInput($request->only('identifier'))
                ->with('marketing_login_error', 'Invalid username or password.');
        }

        $request->session()->regenerate();
        Auth::login($user, true);

        return redirect()->intended(route('marketing'));
    }

    public function registerMarketing(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $account = MarketingAccount::create([
            'name' => trim($validated['name']).' workspace',
            'slug' => $this->uniqueMarketingAccountSlug($validated['name']),
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'marketing_account_id' => $account->id,
            'role' => 'admin',
        ]);

        $account->update(['owner_user_id' => $user->id]);
        Auth::login($user, true);

        return redirect()->route('marketing')
            ->with('marketing_success', 'Your account is ready. Let’s set up your workspace.');
    }

    public function marketingOnboarding(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $this->needsMarketingOnboarding($user)) {
            return redirect()->route('marketing');
        }

        $step = max(1, min(4, (int) $request->query('step', 1)));

        return view('marketing-onboarding', [
            'step' => $step,
            'plan' => $user->account?->plan ?: 'free',
            'hasMailService' => MarketingMailService::query()->where('is_active', true)->exists(),
            'hasWebsite' => ConnectedWebsite::query()->where('is_active', true)->exists(),
            'hasSignature' => MarketingSignature::query()->where('is_active', true)->exists(),
        ]);
    }

    public function saveMarketingOnboardingPlan(Request $request)
    {
        $user = $this->onboardingUser();

        if ($request->boolean('skip')) {
            return redirect()->route('marketing.onboarding', ['step' => 2]);
        }

        $validated = $request->validate(['plan' => ['required', Rule::in(['free'])]]);
        $user->account?->update(['plan' => $validated['plan']]);

        return redirect()->route('marketing.onboarding', ['step' => 2]);
    }

    public function saveMarketingOnboardingSmtp(Request $request)
    {
        $this->onboardingUser();

        if ($request->boolean('skip')) {
            return redirect()->route('marketing.onboarding', ['step' => 3]);
        }

        $mailServiceLimit = $this->marketingPlanLimit('mail_services');
        if ($mailServiceLimit !== null && MarketingMailService::query()->where('is_active', true)->count() >= $mailServiceLimit) {
            return redirect()->route('marketing.onboarding', ['step' => 3])
                ->with('marketing_onboarding_error', $this->marketingPlanLimitMessage('SMTP services', $mailServiceLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'from_name' => ['nullable', 'string', 'max:100'],
            'from_email' => ['required', 'email', 'max:255'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['required', 'string', 'max:1000'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
        ]);

        $service = MarketingMailService::create([
            'name' => trim((string) $validated['name']),
            'from_name' => trim((string) ($validated['from_name'] ?? '')) ?: null,
            'from_email' => strtolower(trim((string) $validated['from_email'])),
            'smtp_host' => trim((string) $validated['smtp_host']),
            'smtp_port' => (int) $validated['smtp_port'],
            'smtp_username' => trim((string) ($validated['smtp_username'] ?? '')) ?: null,
            'smtp_password' => 'temporary',
            'smtp_encryption' => ($validated['smtp_encryption'] ?? 'tls') === 'none' ? null : ($validated['smtp_encryption'] ?? 'tls'),
            'is_active' => true,
        ]);
        $service->setSmtpPassword((string) $validated['smtp_password']);
        $service->save();

        return redirect()->route('marketing.onboarding', ['step' => 3]);
    }

    public function saveMarketingOnboardingWebsite(Request $request)
    {
        $this->onboardingUser();

        if ($request->boolean('skip')) {
            return redirect()->route('marketing.onboarding', ['step' => 4]);
        }

        $websiteLimit = $this->marketingPlanLimit('websites');
        if ($websiteLimit !== null && ConnectedWebsite::query()->where('is_active', true)->count() >= $websiteLimit) {
            return redirect()->route('marketing.onboarding', ['step' => 4])
                ->with('marketing_onboarding_error', $this->marketingPlanLimitMessage('connected websites', $websiteLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = strtolower(trim((string) $validated['domain']));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        $domain = preg_replace('#^www\\.#', '', $domain);

        if (! filter_var('https://'.$domain, FILTER_VALIDATE_URL) || ! preg_match('/^[a-z0-9.-]+$/', $domain)) {
            return back()->withInput()->with('marketing_onboarding_error', 'Enter a valid website domain, such as faisalimtiaz.com.');
        }

        if (ConnectedWebsite::query()->where('domain', $domain)->exists()) {
            return back()->withInput()->with('marketing_onboarding_error', 'That website is already connected.');
        }

        ConnectedWebsite::create([
            'name' => trim((string) $validated['name']),
            'domain' => $domain,
            'tracking_key' => Str::random(48),
            'is_active' => true,
        ]);

        return redirect()->route('marketing.onboarding', ['step' => 4]);
    }

    public function saveMarketingOnboardingSignature(Request $request)
    {
        $user = $this->onboardingUser();

        if ($request->boolean('skip')) {
            $user->forceFill(['marketing_onboarding_completed_at' => now()])->save();

            return redirect()->route('marketing')
                ->with('marketing_success', 'Setup skipped. You can finish it later from Settings.');
        }

        $signatureLimit = $this->marketingPlanLimit('signatures');
        if ($signatureLimit !== null && MarketingSignature::query()->where('is_active', true)->count() >= $signatureLimit) {
            return redirect()->route('marketing.onboarding', ['step' => 4])
                ->with('marketing_onboarding_error', $this->marketingPlanLimitMessage('email signatures', $signatureLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'dimensions:min_width=100,min_height=20'],
        ]);

        $image = $request->file('image');
        $path = $image->store('marketing-signatures');

        MarketingSignature::create([
            'name' => trim((string) $validated['name']),
            'image_path' => $path,
            'image_name' => $image->getClientOriginalName(),
            'mime_type' => $image->getMimeType(),
            'is_active' => true,
        ]);

        $user->forceFill(['marketing_onboarding_completed_at' => now()])->save();

        return redirect()->route('marketing')
            ->with('marketing_success', 'Your workspace is ready. Welcome to MyLeadTrail.');
    }

    public function finishMarketingOnboarding()
    {
        $user = $this->onboardingUser();
        $user->forceFill(['marketing_onboarding_completed_at' => now()])->save();

        return redirect()->route('marketing')
            ->with('marketing_success', 'Your workspace is ready. Welcome to MyLeadTrail.');
    }

    private function needsMarketingOnboarding(?User $user): bool
    {
        return (bool) $user
            && $user->role !== 'moderator'
            && ! $user->marketing_onboarding_completed_at;
    }

    private function onboardingUser(): User
    {
        $user = Auth::user();
        abort_unless($user && $this->needsMarketingOnboarding($user), 403);

        return $user;
    }

    private function marketingPlan(): array
    {
        $planKey = Auth::user()?->account?->plan ?: 'free';

        return config('marketing.plans.'.$planKey, config('marketing.plans.free'));
    }

    private function marketingPlanLimit(string $key): ?int
    {
        // Accounts created before onboarding are allowed to finish importing
        // their existing setup. Once onboarding is completed or skipped,
        // every plan limit is enforced normally.
        if (Auth::user()?->role !== 'moderator' && ! Auth::user()?->marketing_onboarding_completed_at) {
            return null;
        }

        $limit = $this->marketingPlan()['limits'][$key] ?? null;

        return $limit === null ? null : (int) $limit;
    }

    private function marketingPlanLimitMessage(string $resource, int $limit): string
    {
        $planLabel = $this->marketingPlan()['label'] ?? 'current plan';

        return 'Your '.$planLabel.' plan supports up to '.$limit.' '.$resource.'. Upgrade your plan to add more.';
    }

    private function marketingSmtpRequiredMessage(): string
    {
        return 'Add at least one SMTP service in Settings → Email settings before creating or sending a campaign.';
    }

    private function marketingSignatureRequiredMessage(): string
    {
        return 'Add at least one email signature in Settings → Email settings before creating or sending a campaign.';
    }

    public function logoutMarketing(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('marketing.login');
    }

    public function storeMarketingModerator(Request $request)
    {
        $currentUser = Auth::user();
        abort_unless($currentUser?->canManageTeam(), 403);

        $moderatorLimit = $this->marketingPlanLimit('moderators');
        $moderatorCount = User::query()
            ->where('marketing_account_id', $currentUser->marketing_account_id)
            ->where('role', 'moderator')
            ->count();
        if ($moderatorLimit !== null && $moderatorCount >= $moderatorLimit) {
            return back()->withInput()->with('marketing_error', $this->marketingPlanLimitMessage('moderators', $moderatorLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'marketing_account_id' => $currentUser->marketing_account_id,
            'role' => 'moderator',
        ]);

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'team'])
            ->with('marketing_success', 'Moderator added to your account.');
    }

    public function deleteMarketingModerator(User $moderator)
    {
        $currentUser = Auth::user();
        abort_unless($currentUser?->canManageTeam(), 403);
        abort_unless($moderator->marketing_account_id === $currentUser->marketing_account_id && $moderator->role === 'moderator', 404);

        $moderator->delete();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'team'])
            ->with('marketing_success', 'Moderator removed from your account.');
    }

    public function updateMarketingProfile(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store('marketing-profile-photos');
        }

        $user->name = trim($validated['name']);
        $user->email = strtolower(trim($validated['email']));
        $user->save();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'account'])
            ->with('marketing_success', 'Account profile updated.');
    }

    public function updateMarketingPassword(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = $validated['password'];
        $user->save();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'account'])
            ->with('marketing_success', 'Password updated successfully.');
    }

    public function updateMarketingTheme(Request $request)
    {
        $user = Auth::user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'theme' => ['required', Rule::in(['light', 'dark'])],
        ]);

        $user->update(['theme' => $validated['theme']]);

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'account'])
            ->with('marketing_success', 'Theme preference saved.');
    }

    public function marketingProfileImage(User $user)
    {
        abort_unless(Auth::id() === $user->id && $user->profile_photo_path && Storage::exists($user->profile_photo_path), 404);

        return response()->file(Storage::path($user->profile_photo_path), [
            'Content-Type' => Storage::mimeType($user->profile_photo_path),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    private function uniqueMarketingAccountSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'workspace';
        $slug = $base;
        $suffix = 2;

        while (MarketingAccount::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function ensureBootstrapSuperAdmin(): ?User
    {
        if (User::query()->where('role', 'super_admin')->exists()) {
            return User::query()->where('role', 'super_admin')->first();
        }

        $credential = MarketingCredential::query()->first() ?: $this->resolveMarketingCredential();
        $legacyUsername = trim((string) ($credential?->username ?: config('marketing.auth.username')));
        $configuredEmail = trim((string) config('marketing.super_admin.email'));
        $email = filter_var($configuredEmail, FILTER_VALIDATE_EMAIL)
            ? strtolower($configuredEmail)
            : (filter_var($legacyUsername, FILTER_VALIDATE_EMAIL)
                ? strtolower($legacyUsername)
                : ($legacyUsername !== '' ? Str::slug($legacyUsername).'@local.invalid' : null));
        $username = $email === $legacyUsername || $legacyUsername === '' ? null : $legacyUsername;
        $password = (string) config('marketing.super_admin.password');

        if (! $credential && $password !== '' && $email) {
            $credential = MarketingCredential::create([
                'username' => $email,
                'password_hash' => Hash::make($password),
            ]);
        }

        if (! $email || ! $credential) {
            return null;
        }

        // A registered user can already have the configured bootstrap email. Reuse
        // that account instead of trying to insert a second user with the same email.
        $existingUser = User::query()->where('email', $email)->first();
        if ($existingUser) {
            if (! $existingUser->marketing_account_id) {
                $account = MarketingAccount::create([
                    'name' => config('marketing.super_admin.name', 'Super Admin').' workspace',
                    'slug' => $this->uniqueMarketingAccountSlug(config('marketing.super_admin.name', 'Super Admin')),
                ]);
                $existingUser->marketing_account_id = $account->id;
                $account->update(['owner_user_id' => $existingUser->id]);
            }

            $existingUser->role = 'super_admin';
            if ($existingUser->username === null && $username !== null) {
                $existingUser->username = $username;
            }
            $existingUser->save();
            $this->assignLegacyMarketingDataToAccount((int) $existingUser->marketing_account_id);

            return $existingUser;
        }

        try {
            $user = DB::transaction(function () use ($email, $username, $password, $credential) {
                $account = MarketingAccount::create([
                    'name' => config('marketing.super_admin.name', 'Super Admin').' workspace',
                    'slug' => $this->uniqueMarketingAccountSlug(config('marketing.super_admin.name', 'Super Admin')),
                ]);

                $user = new User([
                    'name' => config('marketing.super_admin.name', 'Super Admin'),
                    'username' => $username,
                    'email' => $email,
                    'marketing_account_id' => $account->id,
                    'role' => 'super_admin',
                ]);
                $user->password = $password !== '' ? $password : $credential->password_hash;
                $user->save();
                $account->update(['owner_user_id' => $user->id]);

                return $user;
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            // Two first-login requests can race after logout. If the other request
            // won the unique email insert, continue with the user it created.
            $duplicateEmail = (int) ($exception->errorInfo[1] ?? 0) === 1062
                && str_contains($exception->getMessage(), 'users_email_unique');
            $user = $duplicateEmail ? User::query()->where('email', $email)->first() : null;
            if (! $user) {
                throw $exception;
            }
        }

        $this->assignLegacyMarketingDataToAccount((int) $user->marketing_account_id);

        return $user;
    }

    private function assignLegacyMarketingDataToAccount(int $accountId): void
    {
        foreach ([
            'contacts', 'connected_websites', 'website_visits', 'website_clicks', 'marketing_credentials',
            'marketing_emails', 'marketing_email_opens', 'marketing_unsubscribes', 'marketing_templates',
            'marketing_followup_emails', 'marketing_followup_email_opens', 'marketing_email_group_contacts',
            'marketing_mail_services', 'marketing_signatures', 'marketing_lead_segments', 'marketing_leads',
            'marketing_campaigns', 'marketing_campaign_steps',
        ] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'marketing_account_id')) {
                DB::table($table)->whereNull('marketing_account_id')->update(['marketing_account_id' => $accountId]);
            }
        }
    }

    public function storeConnectedWebsite(Request $request)
    {
        $websiteLimit = $this->marketingPlanLimit('websites');

        if ($websiteLimit !== null && ConnectedWebsite::query()->where('is_active', true)->count() >= $websiteLimit) {
            return back()->withInput()->with('marketing_error', $this->marketingPlanLimitMessage('connected websites', $websiteLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'domain' => ['required', 'string', 'max:255'],
        ]);

        $domain = strtolower(trim((string) $validated['domain']));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);

        if (! filter_var('https://'.$domain, FILTER_VALIDATE_URL) || ! preg_match('/^[a-z0-9.-]+$/', $domain)) {
            return back()->withInput()->with('marketing_error', 'Enter a valid website domain, such as faisalimtiaz.com.');
        }

        if (ConnectedWebsite::query()->where('domain', $domain)->exists()) {
            return back()->withInput()->with('marketing_error', 'That website is already connected.');
        }

        $website = ConnectedWebsite::create([
            'name' => trim((string) $validated['name']),
            'domain' => $domain,
            'tracking_key' => Str::random(48),
            'is_active' => true,
        ]);

        // Adopt legacy events whose URL belongs to this domain so the first
        // connection does not make existing analytics disappear.
        $visitIds = WebsiteVisit::query()->whereNull('connected_website_id')->get()
            ->filter(fn ($visit) => $this->websiteAnalyticsHost($visit->url) === $domain)
            ->pluck('id');

        if ($visitIds->isNotEmpty()) {
            WebsiteVisit::query()->whereIn('id', $visitIds)->update(['connected_website_id' => $website->id]);
            WebsiteClick::query()->whereNull('connected_website_id')->whereIn('website_visit_id', $visitIds)
                ->update(['connected_website_id' => $website->id]);
        }

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites'])
            ->with('marketing_success', $website->name.' is connected and ready for analytics.');
    }

    public function deleteConnectedWebsite(ConnectedWebsite $website)
    {
        $website->delete();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'websites'])
            ->with('marketing_success', 'Website disconnected. Its historical analytics are kept but no longer assigned to a website.');
    }

    public function storeMarketingMailService(Request $request)
    {
        $mailServiceLimit = $this->marketingPlanLimit('mail_services');
        if ($mailServiceLimit !== null && MarketingMailService::query()->where('is_active', true)->count() >= $mailServiceLimit) {
            return back()->withInput()->with('marketing_error', $this->marketingPlanLimitMessage('SMTP services', $mailServiceLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'from_name' => ['nullable', 'string', 'max:100'],
            'from_email' => ['required', 'email', 'max:255'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['required', 'string', 'max:1000'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
        ]);

        $service = MarketingMailService::create([
            'name' => trim((string) $validated['name']),
            'from_name' => trim((string) ($validated['from_name'] ?? '')) ?: null,
            'from_email' => strtolower(trim((string) $validated['from_email'])),
            'smtp_host' => trim((string) $validated['smtp_host']),
            'smtp_port' => (int) $validated['smtp_port'],
            'smtp_username' => trim((string) ($validated['smtp_username'] ?? '')) ?: null,
            'smtp_password' => 'temporary',
            'smtp_encryption' => ($validated['smtp_encryption'] ?? 'tls') === 'none' ? null : ($validated['smtp_encryption'] ?? 'tls'),
            'is_active' => true,
        ]);
        $service->setSmtpPassword((string) $validated['smtp_password']);
        $service->save();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'services'])
            ->with('marketing_success', $service->name.' was added as an email service.');
    }

    public function updateMarketingMailService(Request $request, MarketingMailService $service)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'from_name' => ['nullable', 'string', 'max:100'],
            'from_email' => ['required', 'email', 'max:255'],
            'smtp_host' => ['required', 'string', 'max:255'],
            'smtp_port' => ['required', 'integer', 'between:1,65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:1000'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,none'],
        ]);

        $service->fill([
            'name' => trim((string) $validated['name']),
            'from_name' => trim((string) ($validated['from_name'] ?? '')) ?: null,
            'from_email' => strtolower(trim((string) $validated['from_email'])),
            'smtp_host' => trim((string) $validated['smtp_host']),
            'smtp_port' => (int) $validated['smtp_port'],
            'smtp_username' => trim((string) ($validated['smtp_username'] ?? '')) ?: null,
            'smtp_encryption' => ($validated['smtp_encryption'] ?? 'tls') === 'none' ? null : ($validated['smtp_encryption'] ?? 'tls'),
        ]);

        if (filled($validated['smtp_password'] ?? null)) {
            $service->setSmtpPassword((string) $validated['smtp_password']);
        }

        $service->save();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'services'])
            ->with('marketing_success', $service->name.' was updated.');
    }

    public function deleteMarketingMailService(MarketingMailService $service)
    {
        $service->delete();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'services'])
            ->with('marketing_success', 'Email service removed. Existing campaigns keep their recorded selection.');
    }

    public function storeMarketingSignature(Request $request)
    {
        $signatureLimit = $this->marketingPlanLimit('signatures');

        if ($signatureLimit !== null && MarketingSignature::query()->where('is_active', true)->count() >= $signatureLimit) {
            return back()->withInput()->with('marketing_error', $this->marketingPlanLimitMessage('email signatures', $signatureLimit));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'dimensions:min_width=100,min_height=20'],
        ]);

        $image = $request->file('image');
        $path = $image->store('marketing-signatures');

        MarketingSignature::create([
            'name' => trim((string) $validated['name']),
            'image_path' => $path,
            'image_name' => $image->getClientOriginalName(),
            'mime_type' => $image->getMimeType(),
            'is_active' => true,
        ]);

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'signatures'])
            ->with('marketing_success', 'Email signature added.');
    }

    public function updateMarketingSignature(Request $request, MarketingSignature $signature)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120', 'dimensions:min_width=100,min_height=20'],
        ]);

        $oldPath = $signature->image_path;
        $signature->name = trim((string) $validated['name']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $signature->image_path = $image->store('marketing-signatures');
            $signature->image_name = $image->getClientOriginalName();
            $signature->mime_type = $image->getMimeType();
        }

        $signature->save();

        if ($request->hasFile('image') && $oldPath && $oldPath !== $signature->image_path) {
            Storage::delete($oldPath);
        }

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'signatures'])
            ->with('marketing_success', $signature->name.' was updated.');
    }

    public function deleteMarketingSignature(MarketingSignature $signature)
    {
        if ($signature->image_path) {
            Storage::delete($signature->image_path);
        }

        $signature->delete();

        return redirect()->route('marketing', ['tab' => 'settings', 'settings_tab' => 'email', 'email_settings_tab' => 'signatures'])
            ->with('marketing_success', 'Email signature removed.');
    }

    public function marketingSignatureImage(MarketingSignature $signature)
    {
        abort_unless($signature->image_path && Storage::exists($signature->image_path), 404);

        return response()->file(Storage::path($signature->image_path), [
            'Content-Type' => $signature->mime_type ?: Storage::mimeType($signature->image_path),
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function resolveMarketingCredential(): ?MarketingCredential
    {
        $credential = MarketingCredential::query()->first();

        if ($credential) {
            return $credential;
        }

        $bootstrapUsername = trim((string) config('marketing.auth.username'));
        $bootstrapPassword = (string) config('marketing.auth.bootstrap_password');

        if ($bootstrapUsername === '' || $bootstrapPassword === '') {
            return null;
        }

        return MarketingCredential::create([
            'username' => $bootstrapUsername,
            'password_hash' => Hash::make($bootstrapPassword),
        ]);
    }

    public function marketing(Request $request)
    {
        if ($this->needsMarketingOnboarding(Auth::user())) {
            return redirect()->route('marketing.onboarding');
        }

        $marketingPlan = $this->marketingPlan();

        $activeTab = $request->query('tab', $request->routeIs('marketing.contact-form.show') ? 'contact-form-detail' : ($request->routeIs('marketing.contact-form') ? 'contact-form' : 'dashboard'));

        if ($activeTab === 'templates') {
            $activeTab = 'templates-list';
        }

        if ($activeTab === 'campaigns') {
            $activeTab = 'campaigns-create';
        }

        if (! in_array($activeTab, ['dashboard', 'send', 'campaigns-create', 'campaigns-list', 'campaigns-detail', 'sent-emails', 'sent-email-detail', 'followups', 'followups-create', 'followups-edit', 'contacts', 'contacts-all', 'active-clients', 'leads-generate', 'leads', 'email-groups', 'contact-form', 'contact-form-detail', 'templates-create', 'templates-list', 'templates-edit', 'analytics', 'performance', 'settings'], true)) {
            $activeTab = 'dashboard';
        }

        $databaseReady = true;
        $editingTemplate = null;
        $editingFollowupEmail = null;
        $selectedEmail = null;
        $selectedFollowupEmail = null;
        $selectedRecipientStatuses = collect();
        $recipientSort = 'email';
        $sentEmails = collect();
        $sentEmailSearch = is_string($request->query('sent_search')) ? trim($request->query('sent_search')) : '';
        $sentEmailStatusOptions = ['delivered', 'pending', 'failed'];
        $sentEmailStatus = in_array($request->query('sent_status'), $sentEmailStatusOptions, true) ? $request->query('sent_status') : '';
        $sentEmailOpenedOptions = ['opened', 'not-opened'];
        $sentEmailOpened = in_array($request->query('sent_opened'), $sentEmailOpenedOptions, true) ? $request->query('sent_opened') : '';
        $sentEmailFrom = is_string($request->query('sent_from')) ? $request->query('sent_from') : '';
        $sentEmailTo = is_string($request->query('sent_to')) ? $request->query('sent_to') : '';
        $sentEmailDateErrors = [];
        $followupEmails = collect();
        $websiteVisits = collect();
        $websiteClicks = collect();
        $topPages = collect();
        $topClicks = collect();
        $topLocations = collect();
        $analyticsTrend = collect();
        $trafficSources = collect();
        $deviceBreakdown = collect();
        $countryBreakdown = collect();
        $analyticsAvgSessionSeconds = 0;
        $dashboardPeriodOptions = [
            7 => 'Last 7 days',
            30 => 'Last 30 days',
            90 => 'Last 90 days',
            365 => 'Last 12 months',
        ];
        $dashboardPeriodDays = (int) $request->query('dashboard_period', 30);
        if (! array_key_exists($dashboardPeriodDays, $dashboardPeriodOptions)) {
            $dashboardPeriodDays = 30;
        }
        $dashboardPeriodLabel = $dashboardPeriodOptions[$dashboardPeriodDays];
        $analyticsPeriodLabel = $dashboardPeriodLabel;
        $activeVisitGroups = collect();
        $activeVisitorsCount = 0;
        $websiteVisitsTotal = 0;
        $websiteClicksTotal = 0;
        $connectedWebsites = collect();
        $connectedWebsiteLiveIds = collect();
        $selectedWebsite = null;
        $selectedWebsiteId = is_numeric($request->query('website_id')) ? (int) $request->query('website_id') : null;
        $settingsTab = in_array($request->query('settings_tab'), ['websites', 'email', 'team', 'account', 'billing', 'profile', 'notifications'], true)
            ? $request->query('settings_tab')
            : 'websites';
        $emailSettingsTab = in_array($request->query('email_settings_tab'), ['services', 'signatures'], true)
            ? $request->query('email_settings_tab')
            : 'services';
        $mailServices = collect();
        $signatures = collect();
        $teamMembers = collect();
        $contactFormEntries = collect();
        $contactFormCount = 0;
        $selectedContactFormEntry = null;
        $contactStatuses = collect();
        $activeClients = collect();
        $allContacts = collect();
        $allContactCategories = collect();
        $allContactsReady = true;
        $leadEmailHistory = [];
        $emailGroupContacts = collect();
        $emailGroupsReady = true;
        $emailGroupSearch = is_string($request->query('group_search')) ? trim($request->query('group_search')) : '';
        $emailGroupSource = in_array($request->query('group_source'), array_keys(MarketingEmailGroupContact::SOURCES), true) ? $request->query('group_source') : '';
        $emailGroupSortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name-asc' => ['name', 'asc'],
            'name-desc' => ['name', 'desc'],
            'email-asc' => ['email', 'asc'],
            'email-desc' => ['email', 'desc'],
            'project-asc' => ['project', 'asc'],
            'project-desc' => ['project', 'desc'],
            'source-asc' => ['source', 'asc'],
            'source-desc' => ['source', 'desc'],
        ];
        $emailGroupSort = in_array($request->query('group_sort'), array_keys($emailGroupSortOptions), true) ? $request->query('group_sort') : 'newest';
        $emailGroupPerPage = in_array($request->query('group_per_page'), ['25', '50', '100', '250', 'all'], true) ? $request->query('group_per_page') : '25';
        $marketingLeads = collect();
        $leadSegments = collect();
        $leadSegmentCounts = collect();
        $leadsReady = true;
        $leadTotal = 0;
        $newLeadTotal = 0;
        $leadSearch = is_string($request->query('lead_search')) ? trim($request->query('lead_search')) : '';
        $leadSegment = is_string($request->query('lead_segment')) ? trim($request->query('lead_segment')) : '';
        $leadStatus = array_key_exists($request->query('lead_status'), MarketingLead::STATUSES) ? $request->query('lead_status') : '';
        $leadSortOptions = [
            'newest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name-asc' => ['first_name', 'asc'],
            'name-desc' => ['first_name', 'desc'],
            'company-asc' => ['company', 'asc'],
            'company-desc' => ['company', 'desc'],
        ];
        $leadSort = array_key_exists($request->query('lead_sort'), $leadSortOptions) ? $request->query('lead_sort') : 'newest';
        $leadPerPage = in_array($request->query('lead_per_page'), ['25', '50', '100', '250', 'all'], true) ? $request->query('lead_per_page') : '25';
        $leadImportPreview = session('marketing_leads_import_preview');
        $leadSourceTab = in_array($request->query('lead_source_tab'), ['import', 'search'], true)
            ? $request->query('lead_source_tab')
            : 'import';
        $campaigns = collect();
        $campaignListStats = ['campaigns' => 0, 'sent' => 0, 'opened' => 0];
        $selectedCampaign = null;
        $selectedLeadEmails = collect();
        $campaignEmailRecords = collect();
        $campaignRecordPerPage = in_array($request->query('campaign_records_per_page'), ['25', '50', '100', '250', 'all'], true)
            ? $request->query('campaign_records_per_page')
            : '25';
        $campaignRecordStatus = in_array($request->query('campaign_record_status'), ['all', 'failed'], true)
            ? $request->query('campaign_record_status')
            : 'all';
        $campaignStats = ['opened' => 0, 'sent' => 0, 'remaining' => 0, 'open_rate' => 0, 'records' => 0];
        $campaignLeadSegments = collect();
        $campaignLeadTotal = 0;
        $campaignStep = max(1, min(4, (int) $request->query('campaign_step', 1)));
        $campaignsReady = true;
        $campaignSearch = is_string($request->query('campaign_search')) ? trim($request->query('campaign_search')) : '';
        $campaignStatusOptions = ['draft', 'queued', 'running', 'completed', 'failed'];
        $campaignStatus = in_array($request->query('campaign_status'), $campaignStatusOptions, true) ? $request->query('campaign_status') : '';
        $campaignAudienceTypes = ['all_leads', 'segment', 'manual'];
        $campaignAudienceType = in_array($request->query('campaign_audience_type'), $campaignAudienceTypes, true) ? $request->query('campaign_audience_type') : '';
        $campaignSegment = is_string($request->query('campaign_segment')) ? trim($request->query('campaign_segment')) : '';
        $campaignDateFrom = is_string($request->query('campaign_from')) ? $request->query('campaign_from') : '';
        $campaignDateTo = is_string($request->query('campaign_to')) ? $request->query('campaign_to') : '';
        $campaignSort = in_array($request->query('campaign_sort'), ['newest', 'oldest', 'name-asc', 'name-desc'], true) ? $request->query('campaign_sort') : 'newest';
        $campaignPerPage = in_array($request->query('campaign_per_page'), ['25', '50', '100'], true) ? $request->query('campaign_per_page') : '25';
        $performanceVisits = collect();
        $performanceReady = true;
        $performanceTotalVisits = 0;
        $performanceVisitorSessions = 0;
        $performanceTotalClicks = 0;
        $performanceTopPagePath = null;
        $performanceTopPageVisits = 0;
        $performanceTopClickedPagePath = null;
        $performanceTopClickedPageClicks = 0;
        $performanceFrom = is_string($request->query('performance_from')) ? $request->query('performance_from') : '';
        $performanceTo = is_string($request->query('performance_to')) ? $request->query('performance_to') : '';
        $performanceDateErrors = [];
        $dashboardEmailTrend = collect();
        $dashboardLeadCategories = collect();
        $dashboardCountryBreakdown = collect();
        $dashboardTopPages = collect();
        $dashboardEmailsSent = 0;
        $dashboardEstimatedOpens = 0;
        $dashboardReplies = 0;
        $dashboardLeadConversionRate = 0;
        $dashboardLeadTotal = 0;

        try {
            MarketingUnsubscribe::query()->limit(1)->exists();
            MarketingEmailOpen::query()->limit(1)->exists();
            MarketingTemplate::query()->limit(1)->exists();
            MarketingFollowupEmail::query()->limit(1)->exists();
            MarketingFollowupEmailOpen::query()->limit(1)->exists();
            Schema::hasColumn('marketing_emails', 'delivery_status') || throw new \RuntimeException('Marketing email delivery status column is missing.');
            Schema::hasColumn('marketing_templates', 'attachment_path') || throw new \RuntimeException('Marketing template attachment column is missing.');
            Schema::hasColumn('marketing_templates', 'subject_options') || throw new \RuntimeException('Marketing template subject options column is missing.');
            WebsiteVisit::query()->limit(1)->exists();
            WebsiteClick::query()->limit(1)->exists();
            $connectedWebsites = ConnectedWebsite::query()->latest()->get();
            if ($connectedWebsites->isNotEmpty()) {
                $connectedWebsiteLiveIds = WebsiteVisit::query()
                    ->whereIn('connected_website_id', $connectedWebsites->pluck('id'))
                    ->whereNotNull('connected_website_id')
                    ->distinct()
                    ->pluck('connected_website_id');
            }
            $mailServices = MarketingMailService::query()->where('is_active', true)->latest()->get();
            $signatures = MarketingSignature::query()->where('is_active', true)->latest()->get();
            if (Auth::user()?->marketing_account_id) {
                $teamMembers = User::query()
                    ->where('marketing_account_id', Auth::user()->marketing_account_id)
                    ->orderByRaw("role = 'super_admin' desc")
                    ->orderBy('name')
                    ->get();
            }
            $selectedWebsite = $request->has('website_id')
                ? $connectedWebsites->firstWhere('id', $selectedWebsiteId)
                : $connectedWebsites->firstWhere('is_active', true);
            $selectedWebsiteId = $selectedWebsite?->id;
            $analyticsWindowStart = now()->startOfDay()->subDays($dashboardPeriodDays - 1);
            Schema::hasColumn('website_visits', 'country') || throw new \RuntimeException('Website visit location columns are missing.');
            Schema::hasColumn('website_visits', 'last_seen_at') || throw new \RuntimeException('Website visit online status column is missing.');
            $marketingEmails = MarketingEmail::with(['opens', 'campaign', 'campaignStep.template', 'mailService', 'signature'])->latest('sent_at')->get();
            $templates = MarketingTemplate::latest()->get();
            $followupEmails = MarketingFollowupEmail::with(['opens', 'originalEmail.campaign', 'template', 'mailService', 'signature'])->latest('scheduled_at')->get();
            $sentEmails = $marketingEmails;

            $sentEmailDateValidator = Validator::make([
                'sent_from' => $sentEmailFrom,
                'sent_to' => $sentEmailTo,
            ], [
                'sent_from' => ['nullable', 'date_format:Y-m-d'],
                'sent_to' => ['nullable', 'date_format:Y-m-d', ...($sentEmailFrom !== '' ? ['after_or_equal:sent_from'] : [])],
            ], [
                'sent_from.date_format' => 'Choose a valid start date.',
                'sent_to.date_format' => 'Choose a valid end date.',
                'sent_to.after_or_equal' => 'The end date must be on or after the start date.',
            ]);

            if ($sentEmailDateValidator->fails()) {
                $sentEmailDateErrors = $sentEmailDateValidator->errors()->all();
            } else {
                $sentEmails = $marketingEmails->filter(function ($email) use ($sentEmailSearch, $sentEmailStatus, $sentEmailOpened, $sentEmailFrom, $sentEmailTo) {
                    $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');
                    $opened = $email->opens->contains(fn ($open) => filled($open->opened_at));
                    $sentAt = $email->sent_at ?: $email->created_at;
                    $sentDate = $sentAt?->toDateString();
                    $searchText = strtolower(implode(' ', array_filter([
                        $email->subject,
                        $email->campaign?->name,
                        ...collect($email->recipients ?: [])->all(),
                    ])));

                    return ($sentEmailSearch === '' || Str::contains($searchText, strtolower($sentEmailSearch)))
                        && ($sentEmailStatus === '' || $deliveryStatus === $sentEmailStatus)
                        && ($sentEmailOpened === '' || ($sentEmailOpened === 'opened' ? $opened : ! $opened))
                        && ($sentEmailFrom === '' || ($sentDate !== null && $sentDate >= $sentEmailFrom))
                        && ($sentEmailTo === '' || ($sentDate !== null && $sentDate <= $sentEmailTo));
                })->values();
            }
            $websiteVisitsTotal = WebsiteVisit::exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('visited_at', '>=', $analyticsWindowStart)
                ->count();
            $websiteClicksTotal = WebsiteClick::exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('clicked_at', '>=', $analyticsWindowStart)
                ->count();
            $activeVisits = WebsiteVisit::query()
                ->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->latest('last_seen_at')
                ->get()
                ->unique('session_id')
                ->values();
            $activeVisitorsCount = $activeVisits->count();
            $activeVisitGroups = $this->websiteAnalyticsActiveGroups($activeVisits);
            $websiteVisits = WebsiteVisit::withCount('clicks')->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->latest('visited_at')->take(50)->get();
            $websiteClicks = WebsiteClick::exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->latest('clicked_at')->take(50)->get();
            $topPages = WebsiteVisit::query()
                ->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('visited_at', '>=', $analyticsWindowStart)
                ->selectRaw('path, count(*) as visits_count')
                ->groupBy('path')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();
            $topClicks = WebsiteClick::query()
                ->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('clicked_at', '>=', $analyticsWindowStart)
                ->selectRaw('path, element_text, element, count(*) as clicks_count')
                ->groupBy('path', 'element_text', 'element')
                ->orderByDesc('clicks_count')
                ->take(8)
                ->get();
            $topLocations = WebsiteVisit::query()
                ->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('visited_at', '>=', $analyticsWindowStart)
                ->selectRaw('country, region, city, count(*) as visits_count')
                ->whereNotNull('country')
                ->groupBy('country', 'region', 'city')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();

            $analyticsVisits = WebsiteVisit::query()
                ->exceptOwnTraffic()
                ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
                ->where('visited_at', '>=', $analyticsWindowStart)
                ->get(['visited_at', 'last_seen_at', 'session_id', 'referrer', 'device_type', 'country']);

            $analyticsDays = collect(range($dashboardPeriodDays - 1, 0))->map(fn ($daysAgo) => now()->startOfDay()->subDays($daysAgo));
            $analyticsTrend = $analyticsDays->map(function (Carbon $day) use ($analyticsVisits) {
                $dayKey = $day->toDateString();
                $dayVisits = $analyticsVisits->filter(fn ($visit) => optional($visit->visited_at)->toDateString() === $dayKey);

                return [
                    'label' => $day->format('M j'),
                    'visitors' => $dayVisits->pluck('session_id')->filter()->unique()->count(),
                    'pageviews' => $dayVisits->count(),
                ];
            })->values();

            $sourceCounts = [
                'Organic search' => 0,
                'Email campaigns' => 0,
                'Direct' => 0,
                'Referrals' => 0,
                'Social' => 0,
            ];
            foreach ($analyticsVisits as $visit) {
                $referrer = strtolower((string) $visit->referrer);
                $source = match (true) {
                    $referrer === '' => 'Direct',
                    Str::contains($referrer, ['google.', 'bing.', 'yahoo.', 'duckduckgo.']) => 'Organic search',
                    Str::contains($referrer, ['utm_source=email', 'mail.google.', 'mail.yahoo.', 'outlook.']) => 'Email campaigns',
                    Str::contains($referrer, ['facebook.', 'instagram.', 'linkedin.', 'twitter.', 'x.com', 'tiktok.']) => 'Social',
                    default => 'Referrals',
                };
                $sourceCounts[$source]++;
            }
            $sourceTotal = max(array_sum($sourceCounts), 1);
            $trafficSources = collect($sourceCounts)->map(fn ($count, $name) => [
                'name' => $name,
                'count' => $count,
                'percentage' => (int) round(($count / $sourceTotal) * 100),
            ])->sortByDesc('count')->values();

            $deviceCounts = $analyticsVisits->groupBy(fn ($visit) => ucfirst(strtolower((string) ($visit->device_type ?: 'Unknown'))))
                ->map->count()
                ->sortDesc();
            $deviceTotal = max($deviceCounts->sum(), 1);
            $deviceBreakdown = $deviceCounts->map(fn ($count, $name) => [
                'name' => $name,
                'count' => $count,
                'percentage' => (int) round(($count / $deviceTotal) * 100),
            ])->values();

            $countryCounts = $analyticsVisits->filter(fn ($visit) => filled($visit->country))
                ->groupBy('country')
                ->map->count()
                ->sortDesc();
            $countryTotal = max($countryCounts->sum(), 1);
            $countryBreakdown = $countryCounts->take(5)->map(fn ($count, $name) => [
                'name' => $name,
                'count' => $count,
                'percentage' => (int) round(($count / $countryTotal) * 100),
            ])->values();

            $sessionDurations = $analyticsVisits
                ->filter(fn ($visit) => $visit->visited_at && $visit->last_seen_at)
                ->map(fn ($visit) => max(0, $visit->visited_at->diffInSeconds($visit->last_seen_at)))
                ->filter(fn ($seconds) => $seconds > 0)
                ->values();
            $analyticsAvgSessionSeconds = $sessionDurations->isNotEmpty() ? (int) round($sessionDurations->avg()) : 0;
            $contactFormEntries = Contact::latest()->get();
            $contactFormCount = $contactFormEntries->count();
            $selectedContactFormEntry = $activeTab === 'contact-form-detail'
                ? Contact::find($request->route('contact'))
                : null;

            if ($activeTab === 'contact-form-detail' && ! $selectedContactFormEntry) {
                $activeTab = 'contact-form';
            }

            $selectedEmail = $activeTab === 'sent-email-detail'
                ? MarketingEmail::with([
                    'opens',
                    'campaign',
                    'campaignStep.template',
                    'mailService',
                    'signature',
                ])->find($request->query('email'))
                : null;
            $selectedFollowupEmail = $activeTab === 'followups-create'
                ? MarketingEmail::find($request->query('email'))
                : null;
            $editingFollowupEmail = $activeTab === 'followups-edit'
                ? MarketingFollowupEmail::with(['opens', 'originalEmail', 'template'])->find($request->query('followup'))
                : null;
            $editingTemplate = $activeTab === 'templates-edit'
                ? MarketingTemplate::find($request->query('template'))
                : null;

            if ($activeTab === 'sent-email-detail' && ! $selectedEmail) {
                $activeTab = 'sent-emails';
            }

            if ($activeTab === 'followups-create' && ! $selectedFollowupEmail) {
                $activeTab = 'sent-emails';
            }

            if ($activeTab === 'followups-edit' && (! $editingFollowupEmail || $editingFollowupEmail->status === 'sent')) {
                $activeTab = 'followups';
            }

            if ($activeTab === 'templates-edit' && ! $editingTemplate) {
                $activeTab = 'templates-list';
            }

            if ($selectedEmail) {
                $opensByEmail = $selectedEmail->opens->keyBy('email');

                $recipientSort = in_array($request->query('sort'), ['email', 'opened', 'not-opened', 'last-opened'], true)
                    ? $request->query('sort')
                    : 'email';

                $selectedRecipientStatuses = collect($selectedEmail->recipients)
                    ->map(function ($recipient) use ($selectedEmail, $opensByEmail) {
                        $open = $opensByEmail->get($recipient);

                        return [
                            'email' => $recipient,
                            'delivery_status' => $selectedEmail->delivery_status ?: ($selectedEmail->sent_at ? 'delivered' : 'failed'),
                            'opened_at' => optional($open)->opened_at,
                            'last_opened_at' => optional($open)->last_opened_at,
                            'open_count' => optional($open)->open_count ?: 0,
                        ];
                    })
                    ->when($recipientSort === 'email', fn ($statuses) => $statuses->sortBy('email')->values())
                    ->when($recipientSort === 'opened', fn ($statuses) => $statuses->sortByDesc('opened_at')->values())
                    ->when($recipientSort === 'not-opened', fn ($statuses) => $statuses->sortBy('opened_at')->values())
                    ->when($recipientSort === 'last-opened', fn ($statuses) => $statuses->sortByDesc('last_opened_at')->values());
            } else {
                $recipientSort = 'email';
            }

            $contacts = $marketingEmails
                ->pluck('recipients')
                ->flatten()
                ->filter()
                ->map(fn ($recipient) => strtolower(trim((string) $recipient)))
                ->unique()
                ->sort()
                ->values();

            $contactStatuses = $marketingEmails
                ->flatMap(function ($email) {
                    $status = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'failed');

                    return collect($email->recipients ?: [])
                        ->filter()
                        ->map(fn ($recipient) => [
                            'recipient' => strtolower(trim((string) $recipient)),
                            'delivered' => $status === 'delivered',
                        ]);
                })
                ->groupBy('recipient')
                ->map(fn ($deliveryResults) => $deliveryResults->pluck('delivered')->contains(true));

            $dashboardWindowStart = now()->startOfDay()->subDays($dashboardPeriodDays - 1);
            $dashboardPeriodEmails = $marketingEmails->filter(fn ($email) => optional($email->sent_at)->greaterThanOrEqualTo($dashboardWindowStart));
            $dashboardEmailsSent = (int) $dashboardPeriodEmails->sum(fn ($email) => $email->sent_count ?: $email->recipient_count);
            $dashboardEstimatedOpens = (int) $dashboardPeriodEmails->sum(fn ($email) => $email->opens->whereNotNull('opened_at')->count());

            $dashboardEmailTrend = collect(range($dashboardPeriodDays - 1, 0))->map(function ($daysAgo) use ($marketingEmails, $dashboardWindowStart) {
                $day = $dashboardWindowStart->copy()->addDays(29 - $daysAgo);
                $dayEmails = $marketingEmails->filter(fn ($email) => optional($email->sent_at)->toDateString() === $day->toDateString());

                return [
                    'label' => $day->format('M j'),
                    'sent' => (int) $dayEmails->sum(fn ($email) => $email->sent_count ?: $email->recipient_count),
                    'opened' => (int) $dayEmails->sum(fn ($email) => $email->opens->filter(fn ($open) => optional($open->opened_at)->toDateString() === $day->toDateString())->count()),
                ];
            })->values();

            if (Schema::hasTable('marketing_leads')) {
                $dashboardLeads = MarketingLead::query()
                    ->where('created_at', '>=', $dashboardWindowStart)
                    ->get(['status', 'segment']);
                $dashboardLeadTotal = $dashboardLeads->count();
                $dashboardReplies = $dashboardLeads->where('status', 'replied')->count();
                $dashboardLeadConversionRate = $dashboardLeadTotal > 0 ? round(($dashboardReplies / $dashboardLeadTotal) * 100, 1) : 0;
                $dashboardLeadCategories = $dashboardLeads
                    ->groupBy(fn ($lead) => trim((string) $lead->segment) ?: 'Uncategorized')
                    ->map(function ($leads, $name) {
                        $total = $leads->count();
                        $replied = $leads->where('status', 'replied')->count();
                        $conversion = $total > 0 ? round(($replied / $total) * 100, 1) : 0;

                        return [
                            'name' => $name,
                            'total' => $total,
                            'conversion' => $conversion,
                            'trend' => $replied > 0 ? '+'.$replied.' replied' : 'No replies yet',
                            'trend_positive' => $replied > 0,
                        ];
                    })
                    ->sortByDesc('conversion')
                    ->take(5)
                    ->values();
            }

            $dashboardCountryBreakdown = $countryBreakdown->take(4)->values();
            $dashboardTopPages = $topPages->take(4)->values();
        } catch (\Throwable $exception) {
            $databaseReady = false;
            $activeTab = in_array($activeTab, ['templates-edit', 'sent-email-detail', 'followups-create', 'followups-edit'], true)
                ? ($activeTab === 'templates-edit' ? 'templates-list' : ($activeTab === 'followups-edit' ? 'followups' : 'sent-emails'))
                : $activeTab;
            $marketingEmails = collect();
            $templates = collect();
            $contacts = collect();
            $followupEmails = collect();
            $websiteVisits = collect();
            $websiteClicks = collect();
            $topPages = collect();
            $topClicks = collect();
            $topLocations = collect();
            $activeVisitGroups = collect();
            $activeVisitorsCount = 0;
            $websiteVisitsTotal = 0;
            $websiteClicksTotal = 0;
            $contactFormEntries = collect();
            $contactFormCount = 0;
            $selectedContactFormEntry = null;
            $contactStatuses = collect();
            $dashboardEmailTrend = collect();
            $dashboardLeadCategories = collect();
            $dashboardCountryBreakdown = collect();
            $dashboardTopPages = collect();
        }

        if ($databaseReady && $activeTab === 'active-clients') {
            $activeClients = $marketingEmails->flatMap(function ($email) use ($templates) {
                $matchedTemplate = $email->template_name ? null : $this->matchMarketingTemplate($email, $templates);

                return $email->opens->whereNotNull('opened_at')->map(fn ($open) => [
                    'email' => strtolower(trim($open->email)),
                    'campaign_name' => $email->campaign?->name ?: ($email->marketing_campaign_id ? 'Campaign unavailable' : 'Direct email'),
                    'template_name' => $email->template_name ?: ($matchedTemplate ?: 'Template not recorded'),
                    'template_inferred' => $matchedTemplate !== null,
                    'subject' => $email->subject,
                    'sent_at' => $email->sent_at,
                    'opened_at' => $open->opened_at,
                    'is_followup' => false,
                ]);
            })->concat($followupEmails->flatMap(function ($followup) use ($templates) {
                $templateName = $followup->template_name ?: $followup->template?->name;
                $matchedTemplate = $templateName ? null : $this->matchMarketingTemplate($followup, $templates);

                return $followup->opens->whereNotNull('opened_at')->map(fn ($open) => [
                    'email' => strtolower(trim($open->email)),
                    'campaign_name' => $followup->originalEmail?->campaign?->name ?: ($followup->originalEmail?->marketing_campaign_id ? 'Campaign unavailable' : 'Direct email'),
                    'template_name' => $templateName ?: ($matchedTemplate ?: 'Template not recorded'),
                    'template_inferred' => $matchedTemplate !== null,
                    'subject' => $followup->subject,
                    'sent_at' => $followup->sent_at,
                    'opened_at' => $open->opened_at,
                    'is_followup' => true,
                ]);
            }))->sortByDesc('opened_at')->values();
        }

        if ($databaseReady && $activeTab === 'contacts-all') {
            try {
                Schema::hasTable('marketing_leads') || throw new \RuntimeException('Leads storage is missing.');
                Schema::hasTable('contacts') || throw new \RuntimeException('Contacts storage is missing.');

                $historyByEmail = [];
                foreach ($marketingEmails as $email) {
                    foreach ($email->recipients ?: [] as $recipient) {
                        $key = strtolower(trim((string) $recipient));
                        if ($key === '') {
                            continue;
                        }
                        $opens = $email->opens->filter(fn ($open) => strtolower(trim((string) $open->email)) === $key && $open->opened_at);
                        $firstOpen = $opens->sortBy('opened_at')->first();
                        $historyByEmail[$key][] = [
                            'subject' => $email->subject,
                            'body' => $email->body,
                            'type' => 'Campaign email',
                            'campaign' => $email->campaign?->name ?: ($email->marketing_campaign_id ? 'Campaign unavailable' : 'Direct email'),
                            'status' => ucfirst($email->delivery_status ?: ($email->sent_at ? 'delivered' : 'pending')),
                            'sent_at' => optional($email->sent_at)->format('M d, Y h:i A') ?: 'Not sent',
                            'sent_timestamp' => optional($email->sent_at)->timestamp ?: 0,
                            'opened_at' => optional($firstOpen?->opened_at)->format('M d, Y h:i A') ?: 'Not opened',
                            'open_count' => $opens->sum(fn ($open) => max(1, (int) ($open->open_count ?: 0))),
                            'from_email' => $email->mailService?->from_email,
                        ];
                    }
                }
                foreach ($followupEmails as $followup) {
                    foreach ($followup->recipients ?: [] as $recipient) {
                        $key = strtolower(trim((string) $recipient));
                        if ($key === '') {
                            continue;
                        }
                        $opens = $followup->opens->filter(fn ($open) => strtolower(trim((string) $open->email)) === $key && $open->opened_at);
                        $firstOpen = $opens->sortBy('opened_at')->first();
                        $historyByEmail[$key][] = [
                            'subject' => $followup->subject,
                            'body' => $followup->body,
                            'type' => 'Follow-up email',
                            'campaign' => $followup->originalEmail?->campaign?->name ?: 'Direct follow-up',
                            'status' => ucfirst($followup->status ?: ($followup->sent_at ? 'sent' : 'scheduled')),
                            'sent_at' => optional($followup->sent_at)->format('M d, Y h:i A') ?: 'Not sent',
                            'sent_timestamp' => optional($followup->sent_at)->timestamp ?: 0,
                            'opened_at' => optional($firstOpen?->opened_at)->format('M d, Y h:i A') ?: 'Not opened',
                            'open_count' => $opens->sum(fn ($open) => max(1, (int) ($open->open_count ?: 0))),
                            'from_email' => $followup->mailService?->from_email,
                        ];
                    }
                }

                $directory = [];
                $addHistory = function (array $record) use (&$historyByEmail): array {
                    $history = collect($historyByEmail[$record['email']] ?? [])->sortByDesc('sent_timestamp')->values();
                    $latest = $history->first();
                    $record['email_history'] = $history->all();
                    $record['date_contacted'] = $latest['sent_at'] ?? null;
                    $record['date_contacted_value'] = $latest && $latest['sent_timestamp'] ? date('Y-m-d', $latest['sent_timestamp']) : '';
                    $record['opened'] = $history->contains(fn ($email) => (int) $email['open_count'] > 0);

                    return $record;
                };

                foreach (MarketingLead::query()->latest()->get() as $lead) {
                    $email = strtolower(trim((string) $lead->email));
                    if ($email === '') {
                        continue;
                    }
                    $directory[$email] = $addHistory([
                        'key' => 'lead-'.$lead->id,
                        'name' => trim($lead->first_name.' '.$lead->last_name) ?: $email,
                        'email' => $email,
                        'company' => $lead->company ?: '—',
                        'category' => $lead->segment ?: 'Uncategorized',
                        'status' => MarketingLead::STATUSES[$lead->status] ?? ucfirst((string) $lead->status),
                        'source' => 'Lead',
                        'added_at' => optional($lead->created_at)->format('M d, Y h:i A') ?: 'Not recorded',
                        'added_at_value' => optional($lead->created_at)->toDateString() ?: '',
                        'details' => [],
                    ]);
                }

                foreach (Contact::query()->latest()->get() as $contact) {
                    $email = strtolower(trim((string) $contact->email));
                    if ($email === '') {
                        continue;
                    }
                    if (isset($directory[$email])) {
                        $directory[$email]['source'] = 'Lead + Contact form';
                        $directory[$email]['details']['contact_form'] = [
                            'name' => trim($contact->firstname.' '.$contact->lastname),
                            'budget' => $contact->budget,
                            'details' => $contact->details,
                            'added_at' => optional($contact->created_at)->format('M d, Y h:i A'),
                        ];
                        continue;
                    }
                    $directory[$email] = $addHistory([
                        'key' => 'contact-'.$contact->id,
                        'name' => trim($contact->firstname.' '.$contact->lastname) ?: $email,
                        'email' => $email,
                        'company' => '—',
                        'category' => 'Contact form',
                        'status' => 'Contact form submission',
                        'source' => 'Contact form',
                        'added_at' => optional($contact->created_at)->format('M d, Y h:i A') ?: 'Not recorded',
                        'added_at_value' => optional($contact->created_at)->toDateString() ?: '',
                        'details' => [
                            'contact_form' => [
                                'name' => trim($contact->firstname.' '.$contact->lastname),
                                'budget' => $contact->budget,
                                'details' => $contact->details,
                                'added_at' => optional($contact->created_at)->format('M d, Y h:i A'),
                            ],
                        ],
                    ]);
                }

                $allContacts = collect($directory)->sortByDesc('added_at_value')->values();
                $allContactCategories = $allContacts->pluck('category')->filter()->unique()->sort()->values();
            } catch (\Throwable $exception) {
                report($exception);
                $allContactsReady = false;
            }
        }

        if ($activeTab === 'email-groups') {
            try {
                Schema::hasColumn('marketing_email_group_contacts', 'project') || throw new \RuntimeException('Email groups project column is missing.');
                $query = MarketingEmailGroupContact::query();

                if ($emailGroupSearch !== '') {
                    $query->where(function ($query) use ($emailGroupSearch) {
                        $query->where('name', 'like', '%'.$emailGroupSearch.'%')
                            ->orWhere('email', 'like', '%'.$emailGroupSearch.'%')
                            ->orWhere('project', 'like', '%'.$emailGroupSearch.'%');
                    });
                }

                if ($emailGroupSource !== '') {
                    $query->where('source', $emailGroupSource);
                }

                [$sortColumn, $sortDirection] = $emailGroupSortOptions[$emailGroupSort];
                $perPage = $emailGroupPerPage === 'all' ? max((clone $query)->count(), 1) : (int) $emailGroupPerPage;
                $emailGroupContacts = $query->orderBy($sortColumn, $sortDirection)->orderBy('id', $sortDirection)
                    ->paginate($perPage, ['*'], 'page', $emailGroupPerPage === 'all' ? 1 : null)->withQueryString();
            } catch (\Throwable $exception) {
                report($exception);
                $emailGroupsReady = false;
            }
        }

        if (in_array($activeTab, ['leads-generate', 'leads'], true)) {
            try {
                Schema::hasTable('marketing_leads') || throw new \RuntimeException('Leads storage is missing.');
                Schema::hasTable('marketing_lead_segments') || throw new \RuntimeException('Lead categories storage is missing.');

                $leadSegments = MarketingLeadSegment::query()->orderBy('name')->get();
                $leadQuery = MarketingLead::query();

                if ($leadSearch !== '') {
                    $leadQuery->where(function ($query) use ($leadSearch) {
                        $query->where('first_name', 'like', '%'.$leadSearch.'%')
                            ->orWhere('last_name', 'like', '%'.$leadSearch.'%')
                            ->orWhere('email', 'like', '%'.$leadSearch.'%')
                            ->orWhere('company', 'like', '%'.$leadSearch.'%');
                    });
                }

                if ($leadSegment !== '') {
                    $leadQuery->where('segment', $leadSegment);
                }

                if ($leadStatus !== '') {
                    $leadQuery->where('status', $leadStatus);
                }

                $leadTotal = MarketingLead::query()->count();
                $newLeadTotal = MarketingLead::query()->where('status', 'new')->count();
                $leadSegmentCounts = $leadSegments->map(fn ($segment) => [
                    'name' => $segment->name,
                    'count' => MarketingLead::query()->where('segment', $segment->name)->count(),
                ]);
                [$leadSortColumn, $leadSortDirection] = $leadSortOptions[$leadSort];
                $leadPageSize = $leadPerPage === 'all' ? max((clone $leadQuery)->count(), 1) : (int) $leadPerPage;
                $marketingLeads = $leadQuery
                    ->orderBy($leadSortColumn, $leadSortDirection)
                    ->orderBy('id', $leadSortDirection)
                    ->paginate($leadPageSize, ['*'], 'page', $leadPerPage === 'all' ? 1 : null)
                    ->withQueryString();
                $leadEmailHistory = $this->marketingLeadEmailHistory($marketingEmails, $followupEmails);
            } catch (\Throwable $exception) {
                report($exception);
                $leadsReady = false;
            }
        }

        if (in_array($activeTab, ['campaigns-create', 'campaigns-list', 'campaigns-detail'], true)) {
            try {
                Schema::hasTable('marketing_campaigns') || throw new \RuntimeException('Campaign storage is missing.');
                Schema::hasTable('marketing_campaign_steps') || throw new \RuntimeException('Campaign sequence storage is missing.');
                $campaignLeadSegments = MarketingLeadSegment::query()->orderBy('name')->get();
                $campaignLeadTotal = MarketingLead::query()->count();
                if ($activeTab === 'campaigns-list') {
                    $campaignQuery = MarketingCampaign::query();
                    if ($campaignSearch !== '') {
                        $campaignQuery->where(function ($query) use ($campaignSearch) {
                            $query->where('name', 'like', '%'.$campaignSearch.'%')
                                ->orWhere('description', 'like', '%'.$campaignSearch.'%');
                        });
                    }
                    if ($campaignStatus !== '') {
                        $campaignQuery->where('status', $campaignStatus);
                    }
                    if ($campaignAudienceType !== '') {
                        $campaignQuery->where('audience_type', $campaignAudienceType);
                    }
                    if ($campaignSegment !== '') {
                        $campaignQuery->where('audience_segment', $campaignSegment);
                    }
                    if ($campaignDateFrom !== '') {
                        $campaignQuery->whereDate('created_at', '>=', $campaignDateFrom);
                    }
                    if ($campaignDateTo !== '') {
                        $campaignQuery->whereDate('created_at', '<=', $campaignDateTo);
                    }
                    [$campaignSortColumn, $campaignSortDirection] = match ($campaignSort) {
                        'oldest' => ['created_at', 'asc'],
                        'name-asc' => ['name', 'asc'],
                        'name-desc' => ['name', 'desc'],
                        default => ['created_at', 'desc'],
                    };
                    $campaigns = $campaignQuery->orderBy($campaignSortColumn, $campaignSortDirection)
                        ->orderBy('id', $campaignSortDirection)
                        ->paginate((int) $campaignPerPage, ['*'], 'page')
                        ->withQueryString();
                    $campaignEmails = $marketingEmails->filter(fn ($email) => filled($email->marketing_campaign_id));
                    $campaignListStats = [
                        'campaigns' => MarketingCampaign::query()->count(),
                        'sent' => (int) $campaignEmails->sum('sent_count'),
                        'opened' => (int) $campaignEmails->sum(fn ($email) => $email->opens->whereNotNull('opened_at')->count()),
                    ];
                } elseif ($activeTab === 'campaigns-create') {
                    $campaigns = MarketingCampaign::query()->latest()->take(20)->get();
                    $campaignId = is_numeric($request->query('campaign_id')) ? (int) $request->query('campaign_id') : null;
                    $selectedCampaign = $campaignId
                        ? MarketingCampaign::with('steps')->find($campaignId)
                        : null;
                    $selectedLeadIds = session('marketing_selected_lead_ids', []);
                    if (is_array($selectedLeadIds) && $selectedLeadIds !== []) {
                        $selectedLeadEmails = MarketingLead::query()
                            ->whereIn('id', $selectedLeadIds)
                            ->pluck('email')
                            ->values();
                    }
                } else {
                    $campaignId = is_numeric($request->query('campaign_id')) ? (int) $request->query('campaign_id') : null;
                    $selectedCampaign = $campaignId
                        ? MarketingCampaign::with('steps')->find($campaignId)
                        : null;
                    if (! $selectedCampaign) {
                        throw new \RuntimeException('Campaign was not found.');
                    }
                    $campaignEmailQuery = MarketingEmail::query()
                        ->select(['id', 'marketing_campaign_id', 'marketing_campaign_step_id', 'recipients', 'subject', 'delivery_status', 'sent_at', 'created_at'])
                        ->with(['campaignStep:id,step_order'])
                        ->withCount(['opens as opened_count' => fn ($query) => $query->whereNotNull('opened_at')])
                        ->where('marketing_campaign_id', $selectedCampaign->id)
                        ->latest('created_at');
                    $campaignRecordTotal = (clone $campaignEmailQuery)->count();
                    if ($campaignRecordStatus === 'failed') {
                        $campaignEmailQuery->where('delivery_status', 'failed');
                    }
                    $campaignRecordFilterTotal = (clone $campaignEmailQuery)->count();
                    $campaignEmailRecords = $campaignRecordPerPage === 'all'
                        ? $campaignEmailQuery->get()
                        : $campaignEmailQuery->paginate((int) $campaignRecordPerPage, ['*'], 'campaign_records_page')->withQueryString();
                    $openedRecipients = MarketingEmailOpen::query()
                        ->whereNotNull('opened_at')
                        ->whereIn('marketing_email_id', MarketingEmail::query()
                            ->select('id')
                            ->where('marketing_campaign_id', $selectedCampaign->id))
                        ->distinct()
                        ->pluck('email')
                        ->map(fn ($email) => strtolower(trim((string) $email)))
                        ->filter()
                        ->unique()
                        ->values();
                    $campaignStats = [
                        'opened' => $openedRecipients->count(),
                        'sent' => (int) $selectedCampaign->sent_count,
                        'remaining' => (int) $selectedCampaign->pending_emails,
                        'failed' => MarketingEmail::query()
                            ->where('marketing_campaign_id', $selectedCampaign->id)
                            ->where('delivery_status', 'failed')
                            ->count(),
                        'open_rate' => $selectedCampaign->sent_count > 0
                            ? round(($openedRecipients->count() / $selectedCampaign->sent_count) * 100, 1)
                            : 0,
                        'records' => $campaignRecordTotal,
                        'first_failure' => MarketingEmail::query()
                            ->where('marketing_campaign_id', $selectedCampaign->id)
                            ->where('delivery_status', 'failed')
                            ->whereNotNull('delivery_error')
                            ->where('delivery_error', '!=', '')
                            ->latest('updated_at')
                            ->value('delivery_error'),
                    ];
                }
            } catch (\Throwable $exception) {
                report($exception);
                $campaignsReady = false;
            }
        }

        if ($activeTab === 'performance') {
            $dateValidator = Validator::make([
                'performance_from' => $performanceFrom,
                'performance_to' => $performanceTo,
            ], [
                'performance_from' => ['nullable', 'date_format:Y-m-d'],
                'performance_to' => ['nullable', 'date_format:Y-m-d', ...($performanceFrom !== '' ? ['after_or_equal:performance_from'] : [])],
            ], [
                'performance_from.date_format' => 'Choose a valid start date.',
                'performance_to.date_format' => 'Choose a valid end date.',
                'performance_to.after_or_equal' => 'The end date must be on or after the start date.',
            ]);

            if ($dateValidator->fails()) {
                $performanceDateErrors = $dateValidator->errors()->all();
            } else {
                try {
                    // History includes every stored visit, including visits without location data.
                    $query = WebsiteVisit::query()
                        ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id));

                    if ($performanceFrom !== '') {
                        $query->where('visited_at', '>=', $performanceFrom.' 00:00:00');
                    }

                    if ($performanceTo !== '') {
                        $endExclusive = Carbon::createFromFormat('!Y-m-d', $performanceTo)->addDay()->format('Y-m-d').' 00:00:00';
                        $query->where('visited_at', '<', $endExclusive);
                    }

                    $performanceTotalVisits = (clone $query)->count();
                    $performanceVisitorSessions = (clone $query)->distinct()->count('session_id');
                    $performanceVisitIds = (clone $query)->select('id');
                    $performanceTotalClicks = WebsiteClick::query()
                        ->whereIn('website_visit_id', $performanceVisitIds)
                        ->count();
                    $performanceTopPage = (clone $query)
                        ->select('path')
                        ->selectRaw('COUNT(*) as visit_count')
                        ->groupBy('path')
                        ->orderByDesc('visit_count')
                        ->orderBy('path')
                        ->first();
                    $performanceTopPagePath = $performanceTopPage?->path ?: ($performanceTopPage ? '/' : null);
                    $performanceTopPageVisits = (int) ($performanceTopPage?->visit_count ?? 0);
                    $performanceTopClickedPage = WebsiteClick::query()
                        ->whereIn('website_visit_id', $performanceVisitIds)
                        ->select('path')
                        ->selectRaw('COUNT(*) as click_count')
                        ->groupBy('path')
                        ->orderByDesc('click_count')
                        ->orderBy('path')
                        ->first();
                    $performanceTopClickedPagePath = $performanceTopClickedPage?->path ?: ($performanceTopClickedPage ? '/' : null);
                    $performanceTopClickedPageClicks = (int) ($performanceTopClickedPage?->click_count ?? 0);
                    $performanceVisits = $query->withCount('clicks')->latest('visited_at')->latest('id')->paginate(50)->withQueryString();
                } catch (\Throwable $exception) {
                    report($exception);
                    $performanceReady = false;
                }
            }
        }

        return view('marketing', [
            'activeTab' => $activeTab,
            'marketingPlan' => $marketingPlan,
            'marketingPlanLimits' => $marketingPlan['limits'] ?? [],
            'databaseReady' => $databaseReady,
            'emailsSent' => $marketingEmails->sum('recipient_count'),
            'emailsOpened' => $marketingEmails->sum(fn ($email) => $email->opens->whereNotNull('opened_at')->count()),
            'contactsCount' => $contacts->count(),
            'contacts' => $contacts,
            'contactStatuses' => $contactStatuses,
            'activeClients' => $activeClients,
            'allContacts' => $allContacts,
            'allContactCategories' => $allContactCategories,
            'allContactsReady' => $allContactsReady,
            'leadEmailHistory' => $leadEmailHistory,
            'emailGroupContacts' => $emailGroupContacts,
            'emailGroupsReady' => $emailGroupsReady,
            'emailGroupSearch' => $emailGroupSearch,
            'emailGroupSource' => $emailGroupSource,
            'emailGroupSort' => $emailGroupSort,
            'emailGroupPerPage' => $emailGroupPerPage,
            'marketingLeads' => $marketingLeads,
            'leadSegments' => $leadSegments,
            'leadSegmentCounts' => $leadSegmentCounts,
            'leadsReady' => $leadsReady,
            'leadTotal' => $leadTotal,
            'newLeadTotal' => $newLeadTotal,
            'leadSearch' => $leadSearch,
            'leadSegment' => $leadSegment,
            'leadStatus' => $leadStatus,
            'leadSort' => $leadSort,
            'leadPerPage' => $leadPerPage,
            'leadImportPreview' => $leadImportPreview,
            'leadSourceTab' => $leadSourceTab,
            'campaigns' => $campaigns,
            'campaignListStats' => $campaignListStats,
            'selectedCampaign' => $selectedCampaign,
            'selectedLeadEmails' => $selectedLeadEmails,
            'campaignEmailRecords' => $campaignEmailRecords,
            'campaignRecordPerPage' => $campaignRecordPerPage,
            'campaignRecordStatus' => $campaignRecordStatus,
            'campaignRecordFilterTotal' => $campaignRecordFilterTotal ?? 0,
            'campaignStats' => $campaignStats,
            'campaignLeadSegments' => $campaignLeadSegments,
            'campaignLeadTotal' => $campaignLeadTotal,
            'campaignStep' => $campaignStep,
            'campaignsReady' => $campaignsReady,
            'campaignSearch' => $campaignSearch,
            'campaignStatusOptions' => $campaignStatusOptions,
            'campaignStatus' => $campaignStatus,
            'campaignAudienceTypes' => $campaignAudienceTypes,
            'campaignAudienceType' => $campaignAudienceType,
            'campaignSegment' => $campaignSegment,
            'campaignDateFrom' => $campaignDateFrom,
            'campaignDateTo' => $campaignDateTo,
            'campaignSort' => $campaignSort,
            'campaignPerPage' => $campaignPerPage,
            'contactFormCount' => $contactFormCount,
            'contactFormEntries' => $contactFormEntries,
            'selectedContactFormEntry' => $selectedContactFormEntry,
            'recentEmails' => $marketingEmails->take(5),
            'pendingEmails' => $marketingEmails->where('delivery_status', 'pending')->values(),
            'sentEmails' => $sentEmails,
            'sentEmailTotal' => $marketingEmails->count(),
            'sentEmailSearch' => $sentEmailSearch,
            'sentEmailStatus' => $sentEmailStatus,
            'sentEmailStatusOptions' => $sentEmailStatusOptions,
            'sentEmailOpened' => $sentEmailOpened,
            'sentEmailOpenedOptions' => $sentEmailOpenedOptions,
            'sentEmailFrom' => $sentEmailFrom,
            'sentEmailTo' => $sentEmailTo,
            'sentEmailDateErrors' => $sentEmailDateErrors,
            'selectedEmail' => $selectedEmail,
            'selectedFollowupEmail' => $selectedFollowupEmail,
            'selectedRecipientStatuses' => $selectedRecipientStatuses,
            'recipientSort' => $recipientSort,
            'templates' => $templates,
            'followupEmails' => $followupEmails,
            'editingTemplate' => $editingTemplate,
            'editingFollowupEmail' => $editingFollowupEmail,
            'websiteVisits' => $websiteVisits,
            'websiteClicks' => $websiteClicks,
            'topPages' => $topPages,
            'topClicks' => $topClicks,
            'topLocations' => $topLocations,
            'analyticsTrend' => $analyticsTrend,
            'trafficSources' => $trafficSources,
            'deviceBreakdown' => $deviceBreakdown,
            'countryBreakdown' => $countryBreakdown,
            'analyticsAvgSessionSeconds' => $analyticsAvgSessionSeconds,
            'analyticsPeriodLabel' => $analyticsPeriodLabel,
            'activeVisitGroups' => $activeVisitGroups,
            'activeVisitorsCount' => $activeVisitorsCount,
            'websiteVisitsCount' => $websiteVisitsTotal,
            'websiteVisitsTotal' => $websiteVisitsTotal,
            'websiteClicksCount' => $websiteClicksTotal,
            'websiteClicksTotal' => $websiteClicksTotal,
            'connectedWebsites' => $connectedWebsites,
            'connectedWebsiteLiveIds' => $connectedWebsiteLiveIds,
            'selectedWebsite' => $selectedWebsite,
            'selectedWebsiteId' => $selectedWebsiteId,
            'settingsTab' => $settingsTab,
            'emailSettingsTab' => $emailSettingsTab,
            'mailServices' => $mailServices,
            'signatures' => $signatures,
            'teamMembers' => $teamMembers,
            'performanceVisits' => $performanceVisits,
            'performanceReady' => $performanceReady,
            'performanceTotalVisits' => $performanceTotalVisits,
            'performanceVisitorSessions' => $performanceVisitorSessions,
            'performanceTotalClicks' => $performanceTotalClicks,
            'performanceTopPagePath' => $performanceTopPagePath,
            'performanceTopPageVisits' => $performanceTopPageVisits,
            'performanceTopClickedPagePath' => $performanceTopClickedPagePath,
            'performanceTopClickedPageClicks' => $performanceTopClickedPageClicks,
            'performanceFrom' => $performanceFrom,
            'performanceTo' => $performanceTo,
            'performanceDateErrors' => $performanceDateErrors,
            'dashboardEmailTrend' => $dashboardEmailTrend,
            'dashboardLeadCategories' => $dashboardLeadCategories,
            'dashboardCountryBreakdown' => $dashboardCountryBreakdown,
            'dashboardTopPages' => $dashboardTopPages,
            'dashboardEmailsSent' => $dashboardEmailsSent,
            'dashboardEstimatedOpens' => $dashboardEstimatedOpens,
            'dashboardReplies' => $dashboardReplies,
            'dashboardLeadConversionRate' => $dashboardLeadConversionRate,
            'dashboardLeadTotal' => $dashboardLeadTotal,
            'dashboardPeriod' => $dashboardPeriodDays,
            'dashboardPeriodLabel' => $dashboardPeriodLabel,
            'dashboardPeriodOptions' => $dashboardPeriodOptions,
            'templateOptions' => $templates->mapWithKeys(fn ($template) => [
                $template->id => [
                    'subject' => $template->subject,
                    'subjects' => $template->subject_options ?: [$template->subject],
                    'content' => $template->content,
                    'content_format' => $template->content_format,
                    'attachment_name' => $template->attachment_name,
                ],
            ]),
        ]);
    }

    private function matchMarketingTemplate(MarketingEmail|MarketingFollowupEmail $email, Collection $templates): ?string
    {
        $normalizeBody = fn ($body) => trim(str_replace(["\r\n", "\r"], "\n", (string) $body));
        $matches = $templates->filter(function ($template) use ($email, $normalizeBody) {
            $subjects = collect($template->subject_options ?: [])->push($template->subject);

            return $subjects->contains(fn ($subject) => trim((string) $subject) === trim($email->subject))
                && $normalizeBody($template->content) === $normalizeBody($email->body);
        });

        // A subject alone cannot reliably identify a historical template.
        return $matches->count() === 1 ? $matches->first()->name : null;
    }

    private function marketingLeadEmailHistory(Collection $marketingEmails, Collection $followupEmails): array
    {
        $history = [];
        $add = function (string $emailAddress, array $record) use (&$history): void {
            $emailAddress = strtolower(trim($emailAddress));
            if ($emailAddress === '' || ! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $history[$emailAddress][] = $record;
        };

        foreach ($marketingEmails as $email) {
            foreach (collect($email->recipients ?: []) as $recipient) {
                $recipient = strtolower(trim((string) $recipient));
                $open = $email->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === $recipient);
                $add($recipient, [
                    'type' => 'Initial email',
                    'campaign_name' => $email->campaign?->name ?: ($email->marketing_campaign_id ? 'Campaign unavailable' : 'Direct email'),
                    'subject' => $email->subject,
                    'body' => $email->body,
                    'template_name' => $email->template_name ?: 'Custom email',
                    'delivery_status' => $email->delivery_status ?: 'pending',
                    'sent_at' => $email->sent_at,
                    'opened_at' => $open?->opened_at,
                    'from_email' => $email->mailService?->from_email,
                    'signature_name' => $email->signature?->name,
                    'attachment_name' => $email->attachment_name,
                ]);
            }
        }

        foreach ($followupEmails as $followup) {
            foreach (collect($followup->recipients ?: []) as $recipient) {
                $recipient = strtolower(trim((string) $recipient));
                $open = $followup->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === $recipient);
                $add($recipient, [
                    'type' => 'Follow-up email',
                    'campaign_name' => $followup->originalEmail?->campaign?->name ?: ($followup->originalEmail?->marketing_campaign_id ? 'Campaign unavailable' : 'Direct email'),
                    'subject' => $followup->subject,
                    'body' => $followup->body,
                    'template_name' => $followup->template_name ?: ($followup->template?->name ?: 'Custom email'),
                    'delivery_status' => $followup->status ?: 'pending',
                    'sent_at' => $followup->sent_at,
                    'opened_at' => $open?->opened_at,
                    'from_email' => $followup->mailService?->from_email,
                    'signature_name' => $followup->signature?->name,
                    'attachment_name' => $followup->attachment_name,
                ]);
            }
        }

        foreach ($history as &$records) {
            usort($records, fn (array $left, array $right) => ($right['sent_at']?->getTimestamp() ?? 0) <=> ($left['sent_at']?->getTimestamp() ?? 0));
        }

        return $history;
    }

    public function trackWebsiteVisit(Request $request)
    {
        try {
            $connectedWebsite = $this->connectedWebsiteForRequest($request);
            $ipAddress = $this->websiteAnalyticsIpAddress($request);
            $userAgent = (string) $request->userAgent();
            $location = $this->websiteAnalyticsLocation($ipAddress);
            $machine = $this->websiteAnalyticsMachine($userAgent);

            $visit = WebsiteVisit::create([
                'marketing_account_id' => $connectedWebsite?->marketing_account_id,
                'connected_website_id' => $connectedWebsite?->id,
                'session_id' => $this->websiteAnalyticsSessionId($request),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'url' => (string) $request->input('url'),
                'path' => (string) $request->input('path', '/'),
                'referrer' => (string) $request->input('referrer'),
                'country' => $location['country'],
                'region' => $location['region'],
                'city' => $location['city'],
                'postal' => $location['postal'],
                'timezone' => $location['timezone'],
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'organization' => $location['organization'],
                'browser' => $machine['browser'],
                'browser_version' => $machine['browser_version'],
                'operating_system' => $machine['operating_system'],
                'device_type' => $machine['device_type'],
                'screen_width' => $request->integer('screen_width') ?: null,
                'screen_height' => $request->integer('screen_height') ?: null,
                'viewport_width' => $request->integer('viewport_width') ?: null,
                'viewport_height' => $request->integer('viewport_height') ?: null,
                'visited_at' => now(),
                'last_seen_at' => now(),
            ]);

            return response()->json(['id' => $visit->id]);
        } catch (\Throwable $exception) {
            return response()->json(['id' => null], 204);
        }
    }

    public function trackWebsiteHeartbeat(Request $request)
    {
        try {
            $sessionId = $this->websiteAnalyticsSessionId($request);
            $visit = WebsiteVisit::query()
                ->where('session_id', $sessionId)
                ->when($request->integer('visit_id'), fn ($query, $visitId) => $query->where('id', $visitId))
                ->latest('visited_at')
                ->first();

            if ($visit) {
                $visit->update([
                    'url' => (string) $request->input('url', $visit->url),
                    'path' => (string) $request->input('path', $visit->path),
                    'last_seen_at' => now(),
                ]);
            }
        } catch (\Throwable $exception) {
        }

        return response()->noContent();
    }

    public function marketingLiveVisitors(Request $request)
    {
        $websiteId = $request->integer('website_id');
        $selectedWebsite = $websiteId
            ? ConnectedWebsite::query()->whereKey($websiteId)->first()
            : ConnectedWebsite::query()->where('is_active', true)->first();

        $activeVisits = WebsiteVisit::query()
            ->exceptOwnTraffic()
            ->when($selectedWebsite, fn ($query) => $query->where('connected_website_id', $selectedWebsite->id))
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->latest('last_seen_at')
            ->get(['session_id']);

        return response()->json([
            'active_visitors' => $activeVisits->unique('session_id')->count(),
            'updated_at' => now()->toISOString(),
        ]);
    }

    public function trackWebsiteClick(Request $request)
    {
        try {
            $visit = $request->integer('visit_id') ? WebsiteVisit::find($request->integer('visit_id')) : null;
            $connectedWebsite = $this->connectedWebsiteForRequest($request);

            WebsiteClick::create([
                'marketing_account_id' => $visit?->marketing_account_id ?: $connectedWebsite?->marketing_account_id,
                'connected_website_id' => $visit?->connected_website_id ?: $connectedWebsite?->id,
                'website_visit_id' => $request->integer('visit_id') ?: null,
                'session_id' => $this->websiteAnalyticsSessionId($request),
                'ip_address' => $this->websiteAnalyticsIpAddress($request),
                'url' => (string) $request->input('url'),
                'path' => (string) $request->input('path', '/'),
                'element' => Str::limit((string) $request->input('element'), 255, ''),
                'element_text' => Str::limit((string) $request->input('element_text'), 500, ''),
                'x' => $request->integer('x') ?: null,
                'y' => $request->integer('y') ?: null,
                'clicked_at' => now(),
            ]);
        } catch (\Throwable $exception) {
        }

        return response()->noContent();
    }

    private function connectedWebsiteForRequest(Request $request): ?ConnectedWebsite
    {
        $trackingKey = trim((string) $request->input('website_key'));

        if ($trackingKey !== '') {
            return ConnectedWebsite::query()->where('tracking_key', $trackingKey)->where('is_active', true)->first();
        }

        $host = $this->websiteAnalyticsHost((string) $request->input('url'));

        return $host === null
            ? null
            : ConnectedWebsite::query()->where('domain', $host)->where('is_active', true)->first();
    }

    private function websiteAnalyticsHost(?string $url): ?string
    {
        $host = parse_url((string) $url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        return preg_replace('#^www\.#', '', strtolower($host));
    }

    public function deleteContactForm(Contact $contact)
    {
        $contact->delete();

        return redirect()
            ->route('marketing.contact-form')
            ->with('marketing_success', 'Contact form submission deleted successfully.');
    }

    public function storeMarketingCampaignAudience(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:500'],
            'audience_type' => ['required', Rule::in(['all_leads', 'segment', 'manual'])],
            'audience_segment' => ['nullable', 'string', 'max:80'],
            'audience_status' => ['nullable', Rule::in(array_keys(MarketingLead::STATUSES))],
            'manual_recipients' => ['nullable', 'string'],
            'stop_on_reply' => ['nullable', 'boolean'],
        ]);

        if ($validated['audience_type'] === 'segment' && trim((string) ($validated['audience_segment'] ?? '')) === '') {
                return back()->withInput()->withErrors(['audience_segment' => 'Choose a lead category.']);
        }

        $manualRecipients = collect(explode(',', (string) ($validated['manual_recipients'] ?? '')))
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();
        $invalidManual = $manualRecipients->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));
        if ($validated['audience_type'] === 'manual' && $manualRecipients->isEmpty()) {
            return back()->withInput()->withErrors(['manual_recipients' => 'Add at least one valid recipient email.']);
        }
        if ($invalidManual->isNotEmpty()) {
            return back()->withInput()->withErrors(['manual_recipients' => 'Invalid email address: '.$invalidManual->first()]);
        }

        $campaign = ! empty($validated['campaign_id'])
            ? MarketingCampaign::query()->find($validated['campaign_id'])
            : null;
        if (! $campaign) {
            $campaign = new MarketingCampaign();
        }

        $campaign->fill([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
            'audience_type' => $validated['audience_type'],
            'audience_segment' => $validated['audience_type'] === 'segment' ? trim((string) $validated['audience_segment']) : null,
            'audience_status' => $validated['audience_status'] ?? null,
            'manual_recipients' => $validated['audience_type'] === 'manual' ? $manualRecipients->all() : null,
            'stop_on_reply' => $request->boolean('stop_on_reply', true),
        ]);
        $campaign->recipient_count = 0;
        $campaign->save();
        $campaign->recipient_count = $campaign->audienceEmails()->count();
        $campaign->save();
        session()->forget('marketing_selected_lead_ids');

        return redirect()->route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 2])
            ->with('marketing_success', 'Audience saved. Prepare your first email.');
    }

    public function updateMarketingCampaignContent(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer'],
            'template_id' => ['nullable', 'integer'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'marketing_mail_service_id' => ['nullable', 'integer'],
            'marketing_signature_id' => ['nullable', 'integer'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);
        $campaign = MarketingCampaign::query()->findOrFail($validated['campaign_id']);
        $saveDraft = $request->boolean('save_draft');
        if (! $saveDraft && ! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->withInput()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }
        if (! $saveDraft && ! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->withInput()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }
        $template = ! empty($validated['template_id']) ? MarketingTemplate::query()->find($validated['template_id']) : null;
        $mailService = ! empty($validated['marketing_mail_service_id'])
            ? MarketingMailService::query()->where('is_active', true)->find($validated['marketing_mail_service_id'])
            : null;
        $signature = ! empty($validated['marketing_signature_id'])
            ? MarketingSignature::query()->where('is_active', true)->find($validated['marketing_signature_id'])
            : null;
        $content = (string) $validated['content'];
        $contentIsHtml = $template?->content_format === 'html' || strip_tags($content) !== $content;
        if ($request->filled('marketing_mail_service_id') && ! $mailService) {
            return back()->withInput()->with('marketing_error', 'Choose an active email service.');
        }
        if ($request->filled('marketing_signature_id') && ! $signature) {
            return back()->withInput()->with('marketing_error', 'Choose an active email signature.');
        }
        if (! $saveDraft && ! $mailService) {
            return back()->withInput()->with('marketing_error', 'Select an SMTP service before continuing.');
        }
        if (! $saveDraft && ! $signature) {
            return back()->withInput()->with('marketing_error', 'Select an email signature before continuing.');
        }

        $step = MarketingCampaignStep::query()->firstOrNew([
            'marketing_campaign_id' => $campaign->id,
            'step_order' => 1,
        ]);
        $step->fill([
            'marketing_account_id' => $campaign->marketing_account_id,
            'step_type' => 'email',
            'wait_days' => 0,
            'subject' => $validated['subject'],
            'body' => $contentIsHtml ? MarketingHtml::sanitize($content) : $content,
            'content_format' => $contentIsHtml ? 'html' : 'plain',
            'marketing_template_id' => $template?->id,
            'marketing_mail_service_id' => $mailService?->id,
            'marketing_signature_id' => $signature?->id,
        ]);
        if ($request->hasFile('attachment')) {
            $step->attachment_path = $request->file('attachment')->store('marketing-attachments');
            $step->attachment_name = $request->file('attachment')->getClientOriginalName();
        } elseif ($template?->attachment_path) {
            $step->attachment_path = $template->attachment_path;
            $step->attachment_name = $template->attachment_name;
        }
        $step->save();

        return redirect()->route('marketing', [
            'tab' => 'campaigns',
            'campaign_id' => $campaign->id,
            'campaign_step' => $saveDraft ? 2 : 3,
        ])->with('marketing_success', $saveDraft ? 'Campaign draft saved.' : 'Email content saved. Build your sequence.');
    }

    public function updateMarketingCampaignSequence(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer'],
            'sequence' => ['required', 'array', 'min:1', 'max:8'],
            'sequence.*.wait_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'sequence.*.subject' => ['required', 'string', 'max:255'],
            'sequence.*.content' => ['required', 'string'],
            'sequence.*.template_id' => ['nullable', 'integer'],
            'sequence.*.marketing_mail_service_id' => ['nullable', 'integer'],
            'sequence.*.marketing_signature_id' => ['nullable', 'integer'],
        ]);
        $sequenceLimit = $this->marketingPlanLimit('sequence_steps');
        if ($sequenceLimit !== null && count($validated['sequence']) > $sequenceLimit) {
            return back()->withInput()->with('marketing_error', $this->marketingPlanLimitMessage('sequence steps per campaign', $sequenceLimit));
        }
        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->withInput()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }
        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->withInput()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }
        $campaign = MarketingCampaign::with('steps')->findOrFail($validated['campaign_id']);
        $firstStep = $campaign->steps->firstWhere('step_order', 1);
        if (! $firstStep) {
            return redirect()->route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 2])
                ->with('marketing_error', 'Prepare the first email before creating a sequence.');
        }

        foreach (array_values($validated['sequence']) as $index => $sequenceStep) {
            $order = $index + 1;
            $template = ! empty($sequenceStep['template_id']) ? MarketingTemplate::query()->find($sequenceStep['template_id']) : null;
            $mailService = ! empty($sequenceStep['marketing_mail_service_id'])
                ? MarketingMailService::query()->where('is_active', true)->find($sequenceStep['marketing_mail_service_id'])
                : null;
            $signature = ! empty($sequenceStep['marketing_signature_id'])
                ? MarketingSignature::query()->where('is_active', true)->find($sequenceStep['marketing_signature_id'])
                : null;
            $step = $order === 1 ? $firstStep : MarketingCampaignStep::query()->firstOrNew([
                'marketing_campaign_id' => $campaign->id,
                'step_order' => $order,
            ]);
            $step->fill([
                'marketing_account_id' => $campaign->marketing_account_id,
                'step_type' => 'email',
                'wait_days' => $order === 1 ? 0 : (int) ($sequenceStep['wait_days'] ?? 0),
                'subject' => $sequenceStep['subject'],
                'body' => $sequenceStep['content'],
                'content_format' => $template?->content_format === 'html' ? 'html' : ($order === 1 ? $firstStep->content_format : 'plain'),
                'marketing_template_id' => $template?->id ?: ($order === 1 ? $firstStep->marketing_template_id : null),
                'marketing_mail_service_id' => $mailService?->id ?: ($order === 1 ? $firstStep->marketing_mail_service_id : null),
                'marketing_signature_id' => $signature?->id ?: ($order === 1 ? $firstStep->marketing_signature_id : null),
            ]);
            $step->save();
        }

        MarketingCampaignStep::query()
            ->where('marketing_campaign_id', $campaign->id)
            ->where('step_order', '>', count($validated['sequence']))
            ->delete();
        $campaign->update(['sequence_count' => count($validated['sequence']), 'status' => 'draft']);

        return redirect()->route('marketing', ['tab' => 'campaigns', 'campaign_id' => $campaign->id, 'campaign_step' => 4])
            ->with('marketing_success', 'Sequence saved. Choose when to start the campaign.');
    }

    public function startMarketingCampaign(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => ['required', 'integer'],
            'scheduled_at' => ['nullable', 'date'],
        ]);
        $campaign = MarketingCampaign::with('steps')->findOrFail($validated['campaign_id']);
        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }
        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }
        if ($campaign->steps->isEmpty()) {
            return back()->with('marketing_error', 'Add at least one email step before starting the campaign.');
        }

        $scheduledAt = ! empty($validated['scheduled_at']) ? Carbon::parse($validated['scheduled_at']) : now();
        $campaign->update([
            'status' => 'queued',
            'scheduled_at' => $scheduledAt,
            'recipient_count' => $campaign->audienceEmails()->count(),
            'sequence_count' => $campaign->steps->count(),
            'started_at' => null,
            'completed_at' => null,
            'pending_emails' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
            'last_error' => null,
        ]);
        if ($scheduledAt->isFuture()) {
            StartMarketingCampaignJob::dispatch($campaign->id)
                ->onConnection('database')
                ->delay($scheduledAt);
        } else {
            StartMarketingCampaignJob::dispatch($campaign->id)->onConnection('database');
        }

        return redirect()->route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $campaign->id])
            ->with('marketing_success', $scheduledAt->isFuture() ? 'Campaign scheduled.' : 'Campaign started and queued for delivery.');
    }

    public function restartMarketingCampaign(MarketingCampaign $campaign): RedirectResponse
    {
        $campaign->load('steps');
        $recipients = $campaign->audienceEmails()->values();
        if ($recipients->isEmpty()) {
            return back()->with('marketing_error', 'This campaign has no eligible recipients to restart.');
        }
        if ($campaign->steps->isEmpty()) {
            return back()->with('marketing_error', 'This campaign has no email steps to restart.');
        }

        $copiedAttachmentPaths = [];
        $copyAttachment = function (?string $path) use (&$copiedAttachmentPaths): ?string {
            if (! $path || ! Storage::exists($path)) {
                return $path;
            }

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $newPath = 'marketing-attachments/'.Str::uuid().($extension ? '.'.$extension : '');
            if (! Storage::copy($path, $newPath)) {
                throw new \RuntimeException('Campaign attachment could not be copied.');
            }

            $copiedAttachmentPaths[] = $newPath;

            return $newPath;
        };

        try {
            $restartedCampaign = DB::transaction(function () use ($campaign, $recipients, $copyAttachment): MarketingCampaign {
                $restartedCampaign = MarketingCampaign::create([
                    'marketing_account_id' => $campaign->marketing_account_id,
                    'name' => $campaign->name,
                    'description' => $campaign->description,
                    'status' => 'queued',
                    'audience_type' => $campaign->audience_type,
                    'audience_segment' => $campaign->audience_segment,
                    'audience_status' => $campaign->audience_status,
                    'manual_recipients' => $campaign->manual_recipients,
                    'recipient_count' => $recipients->count(),
                    'sequence_count' => $campaign->steps->count(),
                    'stop_on_reply' => $campaign->stop_on_reply,
                    'scheduled_at' => now(),
                    'pending_emails' => 0,
                    'sent_count' => 0,
                    'failed_count' => 0,
                    'current_step' => 0,
                ]);

                foreach ($campaign->steps as $step) {
                    MarketingCampaignStep::create([
                        'marketing_account_id' => $step->marketing_account_id,
                        'marketing_campaign_id' => $restartedCampaign->id,
                        'step_order' => $step->step_order,
                        'step_type' => $step->step_type,
                        'wait_days' => $step->wait_days,
                        'subject' => $step->subject,
                        'body' => $step->body,
                        'content_format' => $step->content_format,
                        'marketing_template_id' => $step->marketing_template_id,
                        'marketing_mail_service_id' => $step->marketing_mail_service_id,
                        'marketing_signature_id' => $step->marketing_signature_id,
                        'attachment_path' => $copyAttachment($step->attachment_path),
                        'attachment_name' => $step->attachment_name,
                    ]);
                }

                return $restartedCampaign;
            });
        } catch (\Throwable $exception) {
            foreach ($copiedAttachmentPaths as $copiedAttachmentPath) {
                Storage::delete($copiedAttachmentPath);
            }

            throw $exception;
        }

        StartMarketingCampaignJob::dispatch($restartedCampaign->id)->onConnection('database');

        return redirect()->route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $restartedCampaign->id])
            ->with('marketing_success', 'A new campaign was created and queued for delivery.');
    }

    public function deleteMarketingCampaign(MarketingCampaign $campaign): RedirectResponse
    {
        $emailIds = MarketingEmail::query()
            ->where('marketing_campaign_id', $campaign->id)
            ->pluck('id');
        $attachmentPaths = MarketingEmail::query()
            ->whereIn('id', $emailIds)
            ->whereNotNull('attachment_path')
            ->pluck('attachment_path')
            ->merge(
                MarketingCampaignStep::query()
                    ->where('marketing_campaign_id', $campaign->id)
                    ->whereNotNull('attachment_path')
                    ->pluck('attachment_path')
            )
            ->filter()
            ->unique()
            ->values();

        DB::transaction(function () use ($campaign, $emailIds): void {
            if ($emailIds->isNotEmpty()) {
                MarketingEmailOpen::query()->whereIn('marketing_email_id', $emailIds)->delete();
                MarketingFollowupEmail::query()->whereIn('marketing_email_id', $emailIds)->delete();
                MarketingEmail::query()->whereIn('id', $emailIds)->delete();
            }

            MarketingCampaignStep::query()
                ->where('marketing_campaign_id', $campaign->id)
                ->delete();
            $campaign->delete();
        });

        foreach ($attachmentPaths as $attachmentPath) {
            Storage::delete($attachmentPath);
        }

        return redirect()->route('marketing', ['tab' => 'campaigns-list'])
            ->with('marketing_success', 'Campaign and all delivery records were deleted.');
    }

    public function marketingCampaignEmailDetails(MarketingEmail $email)
    {
        $email->load(['opens', 'campaign', 'campaignStep.template', 'mailService', 'signature']);
        $openedRecords = $email->opens->whereNotNull('opened_at')->sortBy('opened_at')->values();
        $deliveryStatus = $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'pending');
        $emailIsHtml = $email->content_format === 'html' || strip_tags((string) $email->body) !== (string) $email->body;
        $templateName = $email->template_name ?: ($email->campaignStep?->template?->name ?: 'Custom email');

        return response()->json([
            'subject' => (string) $email->subject,
            'recipient_count' => (int) ($email->recipient_count ?: count($email->recipients ?: [])),
            'delivery_status' => $deliveryStatus,
            'sent_at' => ($email->sent_at ?: $email->created_at)?->format('M d, Y h:i A'),
            'opened_count' => $openedRecords->count(),
            'first_opened_at' => $openedRecords->first()?->opened_at?->format('M d, Y h:i A'),
            'campaign_name' => $email->campaign?->name ?: 'Campaign',
            'step' => $email->campaignStep?->step_order,
            'template_name' => $templateName,
            'body_html' => MarketingHtml::render((string) $email->body, $emailIsHtml),
            'format' => $emailIsHtml ? 'HTML email' : 'Plain text',
            'from_email' => $email->mailService?->from_email ?: 'Not recorded',
            'mail_service' => $email->mailService?->name ?: 'Default mail settings',
            'signature' => $email->signature?->name ?: 'No signature recorded',
            'attachment' => $email->attachment_name ?: 'None',
            'delivery_error' => $email->delivery_error ?: 'No failure reason was recorded.',
            'retry_url' => route('marketing.campaigns.email.retry', $email->id),
            'recipients' => collect($email->recipients ?: [])->map(function ($recipient) use ($email) {
                $open = $email->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === strtolower(trim((string) $recipient)));

                return [
                    'email' => $recipient,
                    'status' => $email->delivery_status ?: ($email->sent_at ? 'delivered' : 'pending'),
                    'opened_at' => $open?->opened_at?->format('M d, Y h:i A'),
                    'open_count' => $open?->open_count ?: 0,
                ];
            })->values(),
        ]);
    }

    public function marketingCampaignProgress(int $campaign)
    {
        $selectedCampaign = MarketingCampaign::query()->findOrFail($campaign);
        $campaignEmailRecords = MarketingEmail::query()
            ->select(['id', 'marketing_campaign_id', 'delivery_status', 'delivery_error', 'sent_at', 'created_at'])
            ->withCount(['opens as opened_count' => fn ($query) => $query->whereNotNull('opened_at')])
            ->where('marketing_campaign_id', $selectedCampaign->id)
            ->get();
        $openedRecipients = MarketingEmailOpen::query()
            ->whereNotNull('opened_at')
            ->whereIn('marketing_email_id', $campaignEmailRecords->pluck('id'))
            ->distinct()
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values();
        $firstOpenedAtByEmail = MarketingEmailOpen::query()
            ->selectRaw('marketing_email_id, MIN(opened_at) as first_opened_at')
            ->whereNotNull('opened_at')
            ->whereIn('marketing_email_id', $campaignEmailRecords->pluck('id'))
            ->groupBy('marketing_email_id')
            ->pluck('first_opened_at', 'marketing_email_id');
        $sent = (int) $selectedCampaign->sent_count;
        $failed = $campaignEmailRecords->where('delivery_status', 'failed')->count();
        $remaining = (int) $selectedCampaign->pending_emails;
        $expectedRecords = (int) $selectedCampaign->recipient_count * (int) $selectedCampaign->sequence_count;
        $progressTotal = max(1, $sent + $failed + $remaining, $selectedCampaign->status === 'queued' ? $expectedRecords : 0);
        $processed = $sent + $failed;

        return response()->json([
            'status' => $selectedCampaign->status,
            'current_step' => $selectedCampaign->current_step,
            'opened' => $openedRecipients->count(),
            'sent' => $sent,
            'failed' => $failed,
            'remaining' => $remaining,
            'records' => $campaignEmailRecords->count(),
            'progress' => min(100, (int) round(($processed / $progressTotal) * 100)),
            'open_rate' => $sent > 0 ? round(($openedRecipients->count() / $sent) * 100, 1) : 0,
            'first_failure' => $campaignEmailRecords
                ->first(fn ($email) => $email->delivery_status === 'failed' && filled($email->delivery_error))
                ?->delivery_error,
            'records_detail' => $campaignEmailRecords->map(function ($email) use ($firstOpenedAtByEmail) {
                return [
                    'id' => $email->id,
                    'delivery_status' => $email->delivery_status ?: 'pending',
                    'sent_at' => optional($email->sent_at)->format('M d, Y h:i A') ?: 'Pending',
                    'opened_at' => $firstOpenedAtByEmail->get($email->id)
                        ? Carbon::parse($firstOpenedAtByEmail->get($email->id))->format('M d, Y h:i A')
                        : 'Not opened',
                    'open_count' => (int) ($email->opened_count ?? 0),
                    'retry_url' => route('marketing.campaigns.email.retry', $email->id),
                ];
            })->values(),
        ]);
    }

    public function retryMarketingCampaignEmails(Request $request, int $campaign): RedirectResponse
    {
        $selectedCampaign = MarketingCampaign::query()->findOrFail($campaign);

        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }

        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }

        $failedEmails = MarketingEmail::with(['opens'])
            ->where('marketing_campaign_id', $selectedCampaign->id)
            ->where('delivery_status', 'failed')
            ->get();
        $retries = [];

        foreach ($failedEmails as $marketingEmail) {
            $recipient = collect($marketingEmail->recipients ?: [])->first();
            if (! $recipient) {
                continue;
            }

            $tracking = $marketingEmail->opens->first();
            if (! $tracking) {
                $tracking = $marketingEmail->opens()->create([
                    'email' => $recipient,
                    'tracking_id' => (string) Str::uuid(),
                ]);
            }

            $marketingEmail->update([
                'delivery_status' => 'pending',
                'sent_count' => 0,
                'failed_count' => 0,
                'sent_at' => null,
                'delivery_error' => null,
            ]);

            $retries[] = [$marketingEmail->id, $recipient, $tracking->tracking_id];
        }

        if ($retries === []) {
            return back()->with('marketing_error', 'There are no failed emails available to retry.');
        }

        $selectedCampaign->update([
            'status' => 'running',
            'completed_at' => null,
            'pending_emails' => (int) $selectedCampaign->pending_emails + count($retries),
            'failed_count' => max(0, (int) $selectedCampaign->failed_count - count($retries)),
        ]);

        foreach ($retries as [$emailId, $recipient, $trackingId]) {
            SendMarketingEmailJob::dispatch($emailId, $recipient, $trackingId)->onConnection('database');
        }

        return redirect()
            ->route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $selectedCampaign->id])
            ->with('marketing_success', count($retries).' failed email'.(count($retries) === 1 ? '' : 's').' queued for retry.');
    }

    public function retrySelectedMarketingCampaignEmails(Request $request, int $campaign): RedirectResponse
    {
        $selectedCampaign = MarketingCampaign::query()->findOrFail($campaign);
        $validated = $request->validate([
            'email_ids' => ['required', 'array', 'min:1'],
            'email_ids.*' => ['integer'],
        ]);

        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }

        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }

        $selectedIds = collect($validated['email_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $failedEmails = MarketingEmail::with(['opens'])
            ->where('marketing_campaign_id', $selectedCampaign->id)
            ->where('delivery_status', 'failed')
            ->whereIn('id', $selectedIds)
            ->get();
        $retries = [];

        foreach ($failedEmails as $marketingEmail) {
            $recipient = collect($marketingEmail->recipients ?: [])->first();
            if (! $recipient) {
                continue;
            }

            $tracking = $marketingEmail->opens->first();
            if (! $tracking) {
                $tracking = $marketingEmail->opens()->create([
                    'email' => $recipient,
                    'tracking_id' => (string) Str::uuid(),
                ]);
            }

            $marketingEmail->update([
                'delivery_status' => 'pending',
                'sent_count' => 0,
                'failed_count' => 0,
                'sent_at' => null,
                'delivery_error' => null,
            ]);

            $retries[] = [$marketingEmail->id, $recipient, $tracking->tracking_id];
        }

        if ($retries === []) {
            return back()->with('marketing_error', 'None of the selected records are available to retry.');
        }

        $selectedCampaign->update([
            'status' => 'running',
            'completed_at' => null,
            'pending_emails' => (int) $selectedCampaign->pending_emails + count($retries),
            'failed_count' => max(0, (int) $selectedCampaign->failed_count - count($retries)),
        ]);

        foreach ($retries as [$emailId, $recipient, $trackingId]) {
            SendMarketingEmailJob::dispatch($emailId, $recipient, $trackingId)->onConnection('database');
        }

        return redirect()
            ->route('marketing', ['tab' => 'campaigns-detail', 'campaign_id' => $selectedCampaign->id])
            ->with('marketing_success', count($retries).' selected failed email'.(count($retries) === 1 ? '' : 's').' queued for retry.');
    }

    public function retryMarketingCampaignEmail(Request $request, int $email): RedirectResponse
    {
        $marketingEmail = MarketingEmail::with(['campaign', 'opens'])
            ->findOrFail($email);

        if ($marketingEmail->delivery_status !== 'failed') {
            return back()->with('marketing_error', 'Only failed campaign emails can be retried.');
        }

        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSmtpRequiredMessage());
        }

        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            return back()->with('marketing_error', $this->marketingSignatureRequiredMessage());
        }

        $campaign = $marketingEmail->campaign;
        if ($campaign && ! in_array($campaign->status, ['queued', 'running', 'completed', 'failed'], true)) {
            return back()->with('marketing_error', 'This campaign is not available for retry.');
        }

        $recipient = collect($marketingEmail->recipients ?: [])->first();
        if (! $recipient) {
            return back()->with('marketing_error', 'The failed email has no recipient to retry.');
        }

        $tracking = $marketingEmail->opens->first();
        if (! $tracking) {
            $tracking = $marketingEmail->opens()->create([
                'email' => $recipient,
                'tracking_id' => (string) Str::uuid(),
            ]);
        }

        $marketingEmail->update([
            'delivery_status' => 'pending',
            'sent_count' => 0,
            'failed_count' => 0,
            'sent_at' => null,
            'delivery_error' => null,
        ]);

        if ($campaign) {
            $campaign->update([
                'status' => 'running',
                'completed_at' => null,
                'pending_emails' => (int) $campaign->pending_emails + 1,
                'failed_count' => max(0, (int) $campaign->failed_count - 1),
            ]);
        }

        SendMarketingEmailJob::dispatch($marketingEmail->id, $recipient, $tracking->tracking_id)->onConnection('database');

        return redirect()
            ->route('marketing', $campaign
                ? ['tab' => 'campaigns-detail', 'campaign_id' => $campaign->id]
                : ['tab' => 'sent-email-detail', 'email' => $marketingEmail->id])
            ->with('marketing_success', 'Failed email queued for retry.');
    }

    public function sendMarketingEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'template_id' => ['nullable', 'integer'],
            'marketing_mail_service_id' => ['nullable', 'integer', 'exists:marketing_mail_services,id'],
            'marketing_signature_id' => ['nullable', 'integer', 'exists:marketing_signatures,id'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $recipients = collect(explode(',', (string) $request->input('recipients')))
            ->map(fn ($email) => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values();

        $invalidRecipients = $recipients->reject(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL));

        if ($recipients->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('recipients', 'Add at least one valid email address.');
            });
        }

        if ($invalidRecipients->isNotEmpty()) {
            $validator->after(function ($validator) use ($invalidRecipients) {
                $validator->errors()->add('recipients', 'Invalid email address: '.$invalidRecipients->first());
            });
        }

        $validator->validate();

        try {
            MarketingEmail::query()->limit(1)->exists();
            MarketingUnsubscribe::query()->limit(1)->exists();
            MarketingEmailOpen::query()->limit(1)->exists();
            MarketingMailService::query()->limit(1)->exists();
            MarketingSignature::query()->limit(1)->exists();
            Schema::hasColumn('marketing_emails', 'delivery_status') || throw new \RuntimeException('Marketing email delivery status column is missing.');
            $hasTemplateName = Schema::hasColumn('marketing_emails', 'template_name');
            Schema::hasColumn('marketing_templates', 'attachment_path') || throw new \RuntimeException('Marketing template attachment column is missing.');
            Schema::hasColumn('marketing_templates', 'subject_options') || throw new \RuntimeException('Marketing template subject options column is missing.');
        } catch (\Throwable $exception) {
            report($exception);
            $message = 'Marketing storage is not ready yet. Please run php artisan migrate on the server after your database credentials are configured.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 503);
            }

            return back()
                ->withInput()
                ->with('marketing_error', $message);
        }

        if (! MarketingMailService::query()->where('is_active', true)->exists()) {
            $message = $this->marketingSmtpRequiredMessage();

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->with('marketing_error', $message);
        }

        if (! MarketingSignature::query()->where('is_active', true)->exists()) {
            $message = $this->marketingSignatureRequiredMessage();

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withInput()->with('marketing_error', $message);
        }

        $selectedTemplate = $request->filled('template_id')
            ? MarketingTemplate::find($request->integer('template_id'))
            : null;
        $selectedMailService = $request->filled('marketing_mail_service_id')
            ? MarketingMailService::query()->where('is_active', true)->find($request->integer('marketing_mail_service_id'))
            : null;
        $selectedSignature = $request->filled('marketing_signature_id')
            ? MarketingSignature::query()->where('is_active', true)->find($request->integer('marketing_signature_id'))
            : null;

        if ($request->filled('marketing_mail_service_id') && ! $selectedMailService) {
            return back()->withInput()->with('marketing_error', 'Choose an active email service.');
        }

        if ($request->filled('marketing_signature_id') && ! $selectedSignature) {
            return back()->withInput()->with('marketing_error', 'Choose an active email signature.');
        }

        $unsubscribed = MarketingUnsubscribe::query()
            ->whereIn('email', $recipients)
            ->pluck('email');

        $recipients = $recipients->diff($unsubscribed)->values();

        if ($recipients->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'All selected recipients have unsubscribed from marketing emails.'], 422);
            }

            return back()
                ->withInput()
                ->with('marketing_error', 'All selected recipients have unsubscribed from marketing emails.');
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        } elseif ($selectedTemplate && $selectedTemplate->attachment_path) {
            $attachmentPath = $selectedTemplate->attachment_path;
            $attachmentName = $selectedTemplate->attachment_name;
        }

        $emailAttributes = [
            'marketing_mail_service_id' => $selectedMailService?->id,
            'marketing_signature_id' => $selectedSignature?->id,
            'recipients' => $recipients->all(),
            'recipient_count' => $recipients->count(),
            'subject' => $request->input('subject'),
            'body' => $selectedTemplate?->content_format === 'html'
                ? MarketingHtml::sanitize((string) $request->input('content'))
                : $request->input('content'),
            'content_format' => $selectedTemplate?->content_format === 'html' ? 'html' : 'plain',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'delivery_status' => 'pending',
            'sent_count' => 0,
            'failed_count' => 0,
        ];

        if ($hasTemplateName) {
            $emailAttributes['template_name'] = $selectedTemplate?->name ?: 'Custom email';
        }

        $marketingEmail = MarketingEmail::create($emailAttributes);

        $openTrackers = $recipients->mapWithKeys(function ($recipient) use ($marketingEmail) {
            $tracker = MarketingEmailOpen::create([
                'marketing_email_id' => $marketingEmail->id,
                'email' => $recipient,
                'tracking_id' => (string) Str::uuid(),
            ]);

            return [$recipient => $tracker];
        });

        foreach ($recipients as $recipient) {
            SendMarketingEmailJob::dispatch(
                $marketingEmail->id,
                $recipient,
                $openTrackers[$recipient]->tracking_id,
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $marketingEmail->id,
                'recipient_count' => $recipients->count(),
            ]);
        }

        return redirect()
            ->route('marketing', ['tab' => 'dashboard'])
            ->with('marketing_success', 'Sending email to '.$recipients->count().' contact'.($recipients->count() === 1 ? '.' : 's.'));
    }

    public function storeMarketingEmailGroupContact(Request $request)
    {
        try {
            Schema::hasTable('marketing_email_group_contacts') || throw new \RuntimeException('Email groups storage is missing.');
            Schema::hasColumn('marketing_email_group_contacts', 'project') || throw new \RuntimeException('Email groups project column is missing.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('marketing', ['tab' => 'email-groups'])
                ->withInput()
                ->with('marketing_error', 'Email groups storage is not ready yet. Run php artisan migrate on the server to enable it.');
        }

        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'project' => ['nullable', 'string', 'max:255'],
            'email' => ['bail', 'required', 'string', 'email', 'max:254', 'unique:marketing_email_group_contacts,email'],
            'source' => ['required', Rule::in(array_keys(MarketingEmailGroupContact::SOURCES))],
        ]);

        MarketingEmailGroupContact::create($attributes);

        return redirect()->route('marketing', ['tab' => 'email-groups'])
            ->with('marketing_success', 'Contact added to email groups.');
    }

    public function storeMarketingLeadSegment(Request $request)
    {
        try {
            Schema::hasTable('marketing_lead_segments') || throw new \RuntimeException('Lead categories storage is missing.');
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('marketing', ['tab' => 'leads'])
                ->withInput()
                ->with('marketing_error', 'Leads storage is not ready yet. Run php artisan migrate on the server to enable it.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);
        $segmentLimit = $this->marketingPlanLimit('segments');
        if ($segmentLimit !== null && MarketingLeadSegment::query()->count() >= $segmentLimit) {
            return redirect()->route('marketing', ['tab' => 'leads'])
                ->withInput()
                ->with('marketing_error', $this->marketingPlanLimitMessage('lead categories', $segmentLimit));
        }
        $name = trim($validated['name']);
        $accountId = Auth::user()?->marketing_account_id;
        $duplicateQuery = MarketingLeadSegment::query()->where('name', $name);
        $accountId ? $duplicateQuery->where('marketing_account_id', $accountId) : $duplicateQuery->whereNull('marketing_account_id');

        if ($duplicateQuery->exists()) {
            return redirect()->route('marketing', ['tab' => 'leads'])
                ->withInput()
                ->withErrors(['name' => 'This category already exists.']);
        }

        MarketingLeadSegment::create(['name' => $name, 'color' => 'violet']);

        return redirect()->route('marketing', ['tab' => 'leads'])
            ->with('marketing_success', 'Category added.');
    }

    public function bulkMarketingLeads(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['campaign', 'segment', 'delete'])],
            'lead_ids' => ['required', 'array', 'min:1'],
            'lead_ids.*' => ['integer'],
            'segment' => ['nullable', 'string', 'max:80'],
        ]);

        $leads = MarketingLead::query()
            ->whereIn('id', $validated['lead_ids'])
            ->get();

        if ($leads->isEmpty()) {
            return redirect()->route('marketing', ['tab' => 'leads'])
                ->with('marketing_error', 'Select at least one lead to continue.');
        }

        if ($validated['action'] === 'campaign') {
            session()->put('marketing_selected_lead_ids', $leads->modelKeys());

            return redirect()->route('marketing', ['tab' => 'campaigns-create', 'campaign_step' => 1])
                ->with('marketing_success', $leads->count().' selected lead'.($leads->count() === 1 ? '' : 's').' ready for a new campaign.');
        }

        if ($validated['action'] === 'segment') {
            $segment = trim((string) ($validated['segment'] ?? ''));
            if ($segment === '' || ! MarketingLeadSegment::query()->where('name', $segment)->exists()) {
                return redirect()->route('marketing', ['tab' => 'leads'])
                    ->with('marketing_error', 'Choose an existing category before assigning leads.');
            }

            MarketingLead::query()
                ->whereIn('id', $leads->modelKeys())
                ->update(['segment' => $segment]);

            return redirect()->route('marketing', ['tab' => 'leads'])
                ->with('marketing_success', $leads->count().' lead'.($leads->count() === 1 ? '' : 's').' assigned to category '.$segment.'.');
        }

        MarketingLead::query()
            ->whereIn('id', $leads->modelKeys())
            ->delete();

        return redirect()->route('marketing', ['tab' => 'leads'])
            ->with('marketing_success', $leads->count().' lead'.($leads->count() === 1 ? '' : 's').' deleted.');
    }

    public function downloadMarketingLeadsSample()
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['first_name', 'last_name', 'email', 'company', 'status'], ',', '"', '');
            fclose($handle);
        }, 'lead-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function previewMarketingLeadsImport(Request $request)
    {
        $importReturnParams = $request->input('return_tab') === 'leads-generate'
            ? ['tab' => 'leads-generate', 'lead_source_tab' => 'import']
            : ['tab' => 'leads'];

        try {
            Schema::hasTable('marketing_leads') || throw new \RuntimeException('Leads storage is missing.');
            $validated = $request->validate([
                'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            ]);
            $file = $validated['file'];
            $parsed = $this->parseMarketingLeadsCsv($file);
            $path = 'marketing-imports/'.Str::uuid().'.json';
            Storage::disk('local')->put($path, json_encode($parsed, JSON_THROW_ON_ERROR));
            session()->put('marketing_leads_import_preview', [
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'headers' => $parsed['headers'],
                'rows' => array_slice($parsed['rows'], 0, 8),
                'row_count' => count($parsed['rows']),
                'return_tab' => $request->input('return_tab') === 'leads-generate' ? 'leads-generate' : 'leads',
            ]);

            return redirect()->route('marketing', $importReturnParams)
                ->with('marketing_success', 'CSV preview ready. Map the columns and import your leads.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('marketing', $importReturnParams)
                ->withInput()
                ->with('marketing_error', 'The CSV could not be read. Make sure it has a header row and valid CSV data.');
        }
    }

    public function resetMarketingLeadsImport(Request $request): RedirectResponse
    {
        $preview = session('marketing_leads_import_preview');
        if (is_array($preview) && is_string($preview['path'] ?? null)) {
            Storage::disk('local')->delete($preview['path']);
        }

        session()->forget('marketing_leads_import_preview');

        return redirect()->route('marketing', [
            'tab' => 'leads-generate',
            'lead_source_tab' => 'import',
        ])->with('marketing_success', 'CSV import reset. Choose a new file to continue.');
    }

    public function importMarketingLeads(Request $request)
    {
        $preview = session('marketing_leads_import_preview');
        $importReturnParams = (is_array($preview) && ($preview['return_tab'] ?? null) === 'leads-generate')
            ? ['tab' => 'leads-generate', 'lead_source_tab' => 'import']
            : ['tab' => 'leads'];
        if (! is_array($preview) || ! is_string($preview['path'] ?? null)) {
            return redirect()->route('marketing', $importReturnParams)
                ->with('marketing_error', 'Your CSV preview has expired. Please upload the file again.');
        }

        try {
            $payload = json_decode(Storage::disk('local')->get($preview['path']), true, 512, JSON_THROW_ON_ERROR);
            $headers = collect($payload['headers'] ?? [])
                ->map(fn ($header) => $this->normalizeMarketingCsvHeader($header))
                ->values()
                ->all();
            foreach (['first_name_column', 'last_name_column', 'company_column', 'email_column', 'status_column'] as $column) {
                if ($request->filled($column)) {
                    $request->merge([$column => $this->normalizeMarketingCsvHeader($request->input($column))]);
                }
            }
            $columnRule = ['nullable', 'string', Rule::in($headers)];
            $validated = $request->validate([
                'first_name_column' => $columnRule,
                'last_name_column' => $columnRule,
                'company_column' => $columnRule,
                'email_column' => ['required', 'string', Rule::in($headers)],
                'status_column' => $columnRule,
                'segment' => ['nullable', 'string', 'max:80'],
                'new_category' => ['nullable', 'string', 'max:80'],
            ]);
            $segment = trim((string) ($validated['segment'] ?? ''));
            $categoryToCreate = null;
            if ($segment === '__create__') {
                $newCategory = trim((string) ($validated['new_category'] ?? ''));
                if ($newCategory === '') {
                    return redirect()->route('marketing', $importReturnParams)
                        ->withInput()
                        ->withErrors(['new_category' => 'Enter a name for the new category.'])
                        ->with('marketing_error', 'Add a category name before importing your leads.');
                }

                $categoryLimit = $this->marketingPlanLimit('segments');
                if ($categoryLimit !== null && MarketingLeadSegment::query()->count() >= $categoryLimit) {
                    return redirect()->route('marketing', $importReturnParams)
                        ->withInput()
                        ->with('marketing_error', $this->marketingPlanLimitMessage('lead categories', $categoryLimit));
                }

                $accountId = Auth::user()?->marketing_account_id;
                $duplicateCategoryQuery = MarketingLeadSegment::query()->where('name', $newCategory);
                $accountId ? $duplicateCategoryQuery->where('marketing_account_id', $accountId) : $duplicateCategoryQuery->whereNull('marketing_account_id');
                if ($duplicateCategoryQuery->exists()) {
                    return redirect()->route('marketing', $importReturnParams)
                        ->withInput()
                        ->withErrors(['new_category' => 'This category already exists.'])
                        ->with('marketing_error', 'Choose the existing category or enter a different name.');
                }

                $categoryToCreate = [
                    'marketing_account_id' => $accountId,
                    'name' => $newCategory,
                    'color' => 'violet',
                ];
                $segment = $newCategory;
            }

            if ($segment !== '' && $categoryToCreate === null && ! MarketingLeadSegment::query()->where('name', $segment)->exists()) {
                return redirect()->route('marketing', $importReturnParams)
                    ->withInput()
                    ->with('marketing_error', 'Choose an existing category or leave the category unassigned.');
            }

            $rows = $payload['rows'] ?? [];
            $headerIndex = array_flip($headers);
            $valueFrom = function (array $row, ?string $column) use ($headerIndex): string {
                if (! $column || ! array_key_exists($column, $headerIndex)) {
                    return '';
                }

                return trim((string) ($row[$headerIndex[$column]] ?? ''));
            };
            $importRows = [];
            $skipped = 0;
            foreach ($rows as $row) {
                $email = strtolower($valueFrom($row, $validated['email_column']));
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skipped++;
                    continue;
                }

                $status = strtolower($valueFrom($row, $validated['status_column'] ?? null));
                $attributes = [
                    'first_name' => $valueFrom($row, $validated['first_name_column'] ?? null),
                    'last_name' => $valueFrom($row, $validated['last_name_column'] ?? null),
                    'company' => $valueFrom($row, $validated['company_column'] ?? null),
                ];
                if (array_key_exists($status, MarketingLead::STATUSES)) {
                    $attributes['status'] = $status;
                }
                if ($segment !== '') {
                    $attributes['segment'] = $segment;
                }

                // Keep the last occurrence of a duplicate email in the CSV.
                $importRows[$email] = $attributes + ['email' => $email];
            }

            $incomingEmails = collect(array_keys($importRows));
            $existingIncomingEmails = MarketingLead::query()
                ->whereIn('email', $incomingEmails->all())
                ->pluck('email')
                ->map(fn ($email) => strtolower((string) $email));
            $newLeadCount = $incomingEmails->diff($existingIncomingEmails)->count();
            $leadLimit = $this->marketingPlanLimit('leads');
            if ($leadLimit !== null && MarketingLead::query()->count() + $newLeadCount > $leadLimit) {
                $available = max(0, $leadLimit - MarketingLead::query()->count());

                return redirect()->route('marketing', $importReturnParams)
                    ->withInput()
                    ->with('marketing_error', 'Your '.$this->marketingPlan()['label'].' plan has room for '.$available.' more lead'.($available === 1 ? '' : 's').'. Upgrade to import more.');
            }
            $existingLeads = MarketingLead::query()
                ->whereIn('email', $incomingEmails->all())
                ->get()
                ->keyBy(fn ($lead) => strtolower((string) $lead->email));
            $accountId = Auth::user()?->marketing_account_id;
            $timestamp = now();
            $upsertRows = [];

            foreach ($importRows as $email => $attributes) {
                $existingLead = $existingLeads->get($email);
                $upsertRows[] = [
                    'marketing_account_id' => $existingLead?->marketing_account_id ?: $accountId,
                    'first_name' => $attributes['first_name'] !== '' ? $attributes['first_name'] : ($existingLead?->first_name ?? null),
                    'last_name' => $attributes['last_name'] !== '' ? $attributes['last_name'] : ($existingLead?->last_name ?? null),
                    'company' => $attributes['company'] !== '' ? $attributes['company'] : ($existingLead?->company ?? null),
                    'email' => $email,
                    'status' => $attributes['status'] ?? ($existingLead?->status ?? 'new'),
                    'segment' => $attributes['segment'] ?? ($existingLead?->segment ?? null),
                    'created_at' => $existingLead?->created_at ?? $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            DB::transaction(function () use ($categoryToCreate, $upsertRows): void {
                if ($categoryToCreate !== null) {
                    MarketingLeadSegment::create($categoryToCreate);
                }

                foreach (array_chunk($upsertRows, 500) as $chunk) {
                    MarketingLead::query()->upsert(
                        $chunk,
                        ['marketing_account_id', 'email'],
                        ['first_name', 'last_name', 'company', 'status', 'segment', 'updated_at']
                    );
                }
            });

            $imported = count($upsertRows);

            Storage::disk('local')->delete($preview['path']);
            session()->forget('marketing_leads_import_preview');

            $message = $imported.' lead'.($imported === 1 ? '' : 's').' imported successfully.';
            if ($skipped > 0) {
                $message .= ' '.$skipped.' row'.($skipped === 1 ? '' : 's').' skipped because the email was invalid or missing.';
            }

            return redirect()->route('marketing', $importReturnParams)->with('marketing_success', $message);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('marketing', $importReturnParams)
                ->with('marketing_error', 'The lead import could not be completed. Please upload the CSV again.');
        }
    }

    private function parseMarketingLeadsCsv($file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open uploaded CSV.');
        }

        $rawHeaders = fgetcsv($handle);
        if (! is_array($rawHeaders) || count(array_filter($rawHeaders, fn ($header) => trim((string) $header) !== '')) === 0) {
            fclose($handle);
            throw new \RuntimeException('CSV header row is missing.');
        }

        $headers = [];
        foreach ($rawHeaders as $index => $header) {
            $label = $this->normalizeMarketingCsvHeader($header) ?: 'Column '.($index + 1);
            $candidate = $label;
            $suffix = 2;
            while (in_array($candidate, $headers, true)) {
                $candidate = $label.' '.$suffix++;
            }
            $headers[] = $candidate;
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            $rows[] = array_pad(array_slice($row, 0, count($headers)), count($headers), '');
        }
        fclose($handle);

        if ($rows === []) {
            throw new \RuntimeException('CSV has no data rows.');
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    private function normalizeMarketingCsvHeader($header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);

        return trim((string) $header);
    }

    public function marketingProgress(MarketingEmail $email)
    {
        $email->loadMissing('opens');
        $openedRecords = $email->opens->whereNotNull('opened_at')->sortBy('opened_at')->values();

        return response()->json([
            'id' => $email->id,
            'recipient_count' => $email->recipient_count,
            'sent_count' => $email->sent_count,
            'failed_count' => $email->failed_count,
            'delivery_status' => $email->delivery_status,
            'delivery_error' => $email->delivery_error,
            'sent_at' => optional($email->sent_at)->toISOString(),
            'opened_count' => $openedRecords->count(),
            'first_opened_at' => optional($openedRecords->first()?->opened_at)->format('M d, Y h:i A'),
            'recipients' => collect($email->recipients ?: [])->map(function ($recipient) use ($email) {
                $open = $email->opens->first(fn ($item) => strtolower(trim((string) $item->email)) === strtolower(trim((string) $recipient)));

                return [
                    'email' => $recipient,
                    'opened_at' => $open?->opened_at?->format('M d, Y h:i A'),
                    'open_count' => $open?->open_count ?: 0,
                ];
            })->values(),
        ]);
    }

    public function storeMarketingFollowup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'marketing_email_id' => ['required', 'integer', 'exists:marketing_emails,id'],
            'template_id' => ['required', 'integer', 'exists:marketing_templates,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $validator->validate();

        try {
            MarketingFollowupEmail::query()->limit(1)->exists();
            Schema::hasColumn('marketing_followup_emails', 'template_name') || throw new \RuntimeException('Marketing follow-up template name column is missing.');
            Schema::hasColumn('marketing_followup_emails', 'scheduled_at') || throw new \RuntimeException('Marketing follow-up storage is missing.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('marketing_error', 'Follow-up storage is not ready yet. Please run the migration after your database credentials are configured.');
        }

        $originalEmail = MarketingEmail::findOrFail($request->integer('marketing_email_id'));
        $template = MarketingTemplate::findOrFail($request->integer('template_id'));
        $subjects = collect($template->subject_options ?: [$template->subject])
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->values();

        MarketingFollowupEmail::create([
            'template_name' => $template->name,
            'marketing_mail_service_id' => $originalEmail->marketing_mail_service_id,
            'marketing_signature_id' => $originalEmail->marketing_signature_id,
            'marketing_email_id' => $originalEmail->id,
            'marketing_template_id' => $template->id,
            'recipients' => $originalEmail->recipients ?: [],
            'recipient_count' => $originalEmail->recipient_count,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
            'content_format' => $template->content_format,
            'attachment_path' => $template->attachment_path,
            'attachment_name' => $template->attachment_name,
            'status' => 'pending',
            'scheduled_at' => $request->date('scheduled_at'),
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email scheduled.');
    }

    public function updateMarketingFollowup(Request $request, MarketingFollowupEmail $followup)
    {
        if ($followup->status === 'sent') {
            return redirect()
                ->route('marketing', ['tab' => 'followups'])
                ->with('marketing_error', 'Sent follow-up emails cannot be edited.');
        }

        $validator = Validator::make($request->all(), [
            'template_id' => ['required', 'integer', 'exists:marketing_templates,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ]);

        $validator->validate();

        $template = MarketingTemplate::findOrFail($request->integer('template_id'));
        $subjects = collect($template->subject_options ?: [$template->subject])
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->values();

        $followup->update([
            'template_name' => $template->name,
            'marketing_template_id' => $template->id,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
            'content_format' => $template->content_format,
            'attachment_path' => $template->attachment_path,
            'attachment_name' => $template->attachment_name,
            'status' => 'pending',
            'scheduled_at' => $request->date('scheduled_at'),
            'sent_at' => null,
            'delivery_error' => null,
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email updated.');
    }

    public function deleteMarketingFollowup(MarketingFollowupEmail $followup)
    {
        $followup->delete();

        return redirect()
            ->route('marketing', ['tab' => 'followups'])
            ->with('marketing_success', 'Follow-up email removed.');
    }

    public function storeMarketingTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subjects' => ['required', 'array', 'max:5'],
            'subjects.*' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $subjects = $this->normalizeMarketingTemplateSubjects($request->input('subjects', []));

        if ($subjects->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('subjects.0', 'Add at least one email title.');
            });
        }

        $validator->validate();

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-template-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        $content = MarketingHtml::sanitize((string) $request->input('content'));

        try {
            MarketingTemplate::create([
                'name' => $request->input('name'),
                'subject' => $subjects->first(),
                'subject_options' => $subjects->all(),
                'content' => $content,
                'content_format' => 'html',
                'attachment_path' => $attachmentPath,
                'attachment_name' => $attachmentName,
            ]);
        } catch (\Throwable $exception) {
            if ($attachmentPath) {
                Storage::delete($attachmentPath);
            }

            return back()
                ->withInput()
                ->with('marketing_error', 'Template could not be saved. Please run the migration after your database credentials are configured.');
        }

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template saved.');
    }

    public function updateMarketingTemplate(Request $request, MarketingTemplate $template)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'subjects' => ['required', 'array', 'max:5'],
            'subjects.*' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
        ]);

        $subjects = $this->normalizeMarketingTemplateSubjects($request->input('subjects', []));

        if ($subjects->isEmpty()) {
            $validator->after(function ($validator) {
                $validator->errors()->add('subjects.0', 'Add at least one email title.');
            });
        }

        $validator->validate();

        $attachmentPath = $template->attachment_path;
        $attachmentName = $template->attachment_name;
        $originalAttachmentPath = $attachmentPath;
        $oldAttachmentPath = null;

        if ($request->boolean('remove_attachment')) {
            $oldAttachmentPath = $attachmentPath;
            $attachmentPath = null;
            $attachmentName = null;
        }

        if ($request->hasFile('attachment')) {
            $oldAttachmentPath = $originalAttachmentPath;
            $attachmentPath = $request->file('attachment')->store('marketing-template-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        $template->update([
            'name' => $request->input('name'),
            'subject' => $subjects->first(),
            'subject_options' => $subjects->all(),
            'content' => MarketingHtml::sanitize((string) $request->input('content')),
            'content_format' => 'html',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        if ($oldAttachmentPath) {
            Storage::delete($oldAttachmentPath);
        }

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template updated.');
    }

    public function deleteMarketingTemplate(MarketingTemplate $template)
    {
        if ($template->attachment_path) {
            Storage::delete($template->attachment_path);
        }

        $template->delete();

        return redirect()
            ->route('marketing', ['tab' => 'templates-list'])
            ->with('marketing_success', 'Template deleted.');
    }

    public function trackMarketingOpen(Request $request, string $trackingId)
    {
        try {
            // This endpoint is intentionally public. Do not apply the account scope
            // from the viewer's session because email clients and image proxies do
            // not carry the marketing login cookie.
            $tracker = MarketingEmailOpen::withoutGlobalScopes()
                ->where('tracking_id', $trackingId)
                ->first();

            if ($tracker && ! $this->isAutomatedMarketingOpenRequest($request)) {
                $tracker->forceFill([
                    'opened_at' => $tracker->opened_at ?: now(),
                    'last_opened_at' => now(),
                    'open_count' => $tracker->open_count + 1,
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ])->save();
            }
        } catch (\Throwable $exception) {
        }

        $pixel = base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', (string) strlen($pixel))
            ->header('Content-Disposition', 'inline; filename="pixel.gif"')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function trackMarketingFollowupOpen(Request $request, string $trackingId)
    {
        try {
            $tracker = MarketingFollowupEmailOpen::withoutGlobalScopes()
                ->where('tracking_id', $trackingId)
                ->first();

            if ($tracker && ! $this->isAutomatedMarketingOpenRequest($request)) {
                $tracker->forceFill([
                    'opened_at' => $tracker->opened_at ?: now(),
                    'last_opened_at' => now(),
                    'open_count' => $tracker->open_count + 1,
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ])->save();
            }
        } catch (\Throwable $exception) {
        }

        $pixel = base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', (string) strlen($pixel))
            ->header('Content-Disposition', 'inline; filename="pixel.gif"')
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function isAutomatedMarketingOpenRequest(Request $request): bool
    {
        $userAgent = strtolower(trim((string) $request->userAgent()));

        if ($userAgent === '') {
            return true;
        }

        return (bool) preg_match('/proofpoint|mimecast|barracuda|trend micro|spamassassin|scanner|crawler|spider|headless|facebookexternalhit|slackbot|discordbot|linkedinbot|skypeuripreview|curl\\/|wget\\/|python-requests|go-http-client/i', $userAgent);
    }

    private function normalizeMarketingTemplateSubjects($subjects)
    {
        if (! is_array($subjects)) {
            $subjects = [];
        }

        return collect($subjects)
            ->map(fn ($subject) => trim((string) $subject))
            ->filter()
            ->unique()
            ->take(5)
            ->values();
    }

    private function websiteAnalyticsSessionId(Request $request): string
    {
        return Str::limit((string) $request->input('session_id', 'unknown'), 80, '');
    }

    private function websiteAnalyticsIpAddress(Request $request): ?string
    {
        $browserPublicIp = trim((string) $request->input('public_ip'));
        $forwardedIps = collect(explode(',', (string) $request->header('Forwarded')))
            ->map(function ($part) {
                if (preg_match('/for="?([^";,\s]+)"?/i', $part, $matches)) {
                    return trim($matches[1], '[]"');
                }

                return null;
            });

        $candidates = collect([
            $browserPublicIp,
            $request->header('CF-Connecting-IP'),
            $request->header('X-Real-IP'),
            $request->header('X-Client-IP'),
            $request->header('X-Cluster-Client-IP'),
            ...explode(',', (string) $request->header('X-Forwarded-For')),
            ...$forwardedIps,
            $request->ip(),
        ]);

        $ips = $candidates
            ->map(fn ($ip) => trim((string) $ip))
            ->filter(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP))
            ->values();

        return $ips->first(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
            ?: $ips->first()
            ?: null;
    }

    private function websiteAnalyticsActiveGroups($activeVisits)
    {
        return $activeVisits
            ->groupBy(fn ($visit) => implode('|', [
                $visit->country ?: 'Unknown country',
                $visit->city ?: '',
                $visit->path ?: '/',
            ]))
            ->map(function ($visits) {
                $first = $visits->first();
                $location = collect([$first->city, $first->region, $first->country])
                    ->filter()
                    ->join(', ');

                return [
                    'count' => $visits->count(),
                    'location' => $location ?: 'Unknown location',
                    'page' => $this->websiteAnalyticsPageLabel($first->path ?: '/'),
                    'last_seen_at' => $visits->max('last_seen_at'),
                ];
            })
            ->sortByDesc('last_seen_at')
            ->values();
    }

    private function websiteAnalyticsPageLabel(string $path): string
    {
        return match ($path) {
            '/', '' => 'homepage',
            '/aboutme', 'aboutme' => 'about page',
            default => trim($path, '/') ?: 'homepage',
        };
    }

    private function websiteAnalyticsLocation(?string $ipAddress): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'postal' => null,
            'timezone' => null,
            'latitude' => null,
            'longitude' => null,
            'organization' => null,
        ];

        if ($ipAddress && ! filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return $empty;
        }

        try {
            if (! $ipAddress || ! filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $empty;
            }

            $geoipLocation = $this->websiteAnalyticsTorannGeoipLocation($ipAddress);

            if ($geoipLocation['country'] || $geoipLocation['city']) {
                return $geoipLocation;
            }

            $directLocation = $this->websiteAnalyticsIpApiLocation($ipAddress);

            if ($directLocation['country'] || $directLocation['city']) {
                return $directLocation;
            }

            $position = Location::get($ipAddress);

            if (! $position) {
                return $empty;
            }

            return [
                'country' => Str::limit((string) ($position->countryName ?? $position->countryCode ?? ''), 255, '') ?: null,
                'region' => Str::limit((string) ($position->regionName ?? ''), 255, '') ?: null,
                'city' => Str::limit((string) ($position->cityName ?? ''), 255, '') ?: null,
                'postal' => Str::limit((string) ($position->zipCode ?? ''), 255, '') ?: null,
                'timezone' => Str::limit((string) ($position->timezone ?? ''), 255, '') ?: null,
                'latitude' => is_numeric($position->latitude ?? null) ? $position->latitude : null,
                'longitude' => is_numeric($position->longitude ?? null) ? $position->longitude : null,
                'organization' => null,
            ];
        } catch (\Throwable $exception) {
            return $empty;
        }
    }

    private function websiteAnalyticsTorannGeoipLocation(string $ipAddress): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'postal' => null,
            'timezone' => null,
            'latitude' => null,
            'longitude' => null,
            'organization' => null,
        ];

        try {
            $location = app('geoip')->getLocation($ipAddress);

            if (! $location || ($location->default ?? false)) {
                return $empty;
            }

            return [
                'country' => Str::limit((string) ($location->country ?? $location->iso_code ?? ''), 255, '') ?: null,
                'region' => Str::limit((string) ($location->state_name ?? $location->state ?? ''), 255, '') ?: null,
                'city' => Str::limit((string) ($location->city ?? ''), 255, '') ?: null,
                'postal' => Str::limit((string) ($location->postal_code ?? ''), 255, '') ?: null,
                'timezone' => Str::limit((string) ($location->timezone ?? ''), 255, '') ?: null,
                'latitude' => is_numeric($location->lat ?? null) ? $location->lat : null,
                'longitude' => is_numeric($location->lon ?? null) ? $location->lon : null,
                'organization' => null,
            ];
        } catch (\Throwable $exception) {
            return $empty;
        }
    }

    private function websiteAnalyticsIpApiLocation(string $ipAddress): array
    {
        $empty = [
            'country' => null,
            'region' => null,
            'city' => null,
            'postal' => null,
            'timezone' => null,
            'latitude' => null,
            'longitude' => null,
            'organization' => null,
        ];

        try {
            $response = Http::timeout(3)
                ->connectTimeout(3)
                ->acceptJson()
                ->get('http://ip-api.com/json/'.$ipAddress, [
                    'fields' => 'status,message,country,regionName,city,zip,lat,lon,timezone,org',
                ]);

            if (! $response->ok() || $response->json('status') !== 'success') {
                return $empty;
            }

            return [
                'country' => Str::limit((string) $response->json('country'), 255, '') ?: null,
                'region' => Str::limit((string) $response->json('regionName'), 255, '') ?: null,
                'city' => Str::limit((string) $response->json('city'), 255, '') ?: null,
                'postal' => Str::limit((string) $response->json('zip'), 255, '') ?: null,
                'timezone' => Str::limit((string) $response->json('timezone'), 255, '') ?: null,
                'latitude' => is_numeric($response->json('lat')) ? $response->json('lat') : null,
                'longitude' => is_numeric($response->json('lon')) ? $response->json('lon') : null,
                'organization' => Str::limit((string) $response->json('org'), 255, '') ?: null,
            ];
        } catch (\Throwable $exception) {
            return $empty;
        }
    }

    private function websiteAnalyticsMachine(string $userAgent): array
    {
        $browser = $this->websiteAnalyticsBrowser($userAgent);

        return [
            'browser' => $browser['name'],
            'browser_version' => $browser['version'],
            'operating_system' => $this->websiteAnalyticsOperatingSystem($userAgent),
            'device_type' => $this->websiteAnalyticsDeviceType($userAgent),
        ];
    }

    private function websiteAnalyticsBrowser(string $userAgent): array
    {
        $browsers = [
            'Edg' => 'Microsoft Edge',
            'OPR' => 'Opera',
            'Chrome' => 'Chrome',
            'Firefox' => 'Firefox',
            'Version' => 'Safari',
            'Safari' => 'Safari',
        ];

        foreach ($browsers as $token => $name) {
            if (preg_match('/'.$token.'\/([0-9.]+)/', $userAgent, $matches)) {
                if ($token === 'Safari' && str_contains($userAgent, 'Chrome')) {
                    continue;
                }

                return ['name' => $name, 'version' => $matches[1] ?? null];
            }
        }

        return ['name' => 'Unknown browser', 'version' => null];
    }

    private function websiteAnalyticsOperatingSystem(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Windows NT 10') => 'Windows 10/11',
            str_contains($userAgent, 'Windows') => 'Windows',
            str_contains($userAgent, 'Mac OS X') => 'macOS',
            str_contains($userAgent, 'Android') => 'Android',
            str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') => 'iOS/iPadOS',
            str_contains($userAgent, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };
    }

    private function websiteAnalyticsDeviceType(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'iPad') || str_contains($userAgent, 'Tablet') => 'Tablet',
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'Android') => 'Mobile',
            default => 'Desktop',
        };
    }

    private function marketingTrackingUrl(string $trackingId): string
    {
        return route('marketing.open', ['trackingId' => $trackingId], true);
    }

    private function marketingDebugImageUrl(): string
    {
        $path = $this->marketingDebugImagePath();

        return route('marketing.debug-image', ['v' => file_exists($path) ? filemtime($path) : time()], true);
    }

    public function marketingDebugImage()
    {
        $path = $this->marketingDebugImagePath();

        abort_unless(file_exists($path), 404);

        return response()->file($path, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function marketingDebugImagePath(): string
    {
        return public_path('assets/faisalimtiaz/email-logo.png');
    }

    public function unsubscribeMarketingEmail(Request $request)
    {
        $email = strtolower(trim((string) $request->query('email')));

        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 404);

        MarketingUnsubscribe::updateOrCreate(
            ['email' => $email],
            ['unsubscribed_at' => now()]
        );

        if ($request->isMethod('post')) {
            return response('', 204);
        }

        return view('marketing-unsubscribed', ['email' => $email]);
    }

    public function blogIndex()
    {
        $posts = collect($this->blogPosts())
            ->sortByDesc('published_at')
            ->all();

        return view('blog', [
            'posts' => $posts,
        ]);
    }

    public function blogPost(Request $request)
    {
        $posts = $this->blogPosts();
        $slug = (string) $request->route('slug');

        abort_unless(array_key_exists($slug, $posts), 404);

        return view('blog-post', [
            'post' => $posts[$slug],
            'posts' => $posts,
        ]);
    }

    private function blogPosts(): array
    {
        $guides = collect(config('blog.posts', []))->map(fn ($post, $slug) => [
            ...$post,
            'canonical' => url('blog/'.$slug),
        ])->all();

        return $guides + [
            'homepage-that-converts-visitors-into-leads' => [
                'title' => 'How to structure a homepage that turns visitors into leads',
                'meta_description' => 'Learn how to structure a homepage so it explains what you do, who you help, and why visitors should contact you.',
                'canonical' => url('blog/homepage-that-converts-visitors-into-leads'),
                'eyebrow' => 'Homepage strategy',
                'primaryKeyword' => 'homepage that converts visitors into leads',
                'h1' => 'How to structure a homepage that turns visitors into leads',
                'intro' => 'A strong homepage does three things fast: it says what the business does, who it helps, and what visitors should do next. If the page is too broad, people leave before they ever reach the contact form.',
                'summary' => 'A practical homepage structure for service businesses that want more enquiries from organic traffic.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['homepage SEO', 'conversion copy', 'lead generation'],
                'sections' => [
                    [
                        'title' => 'Start with one clear outcome',
                        'text' => 'The homepage should not try to explain every service in equal detail. Pick the main offer you want to sell most often, then make the hero, intro copy, and first call to action support that outcome.',
                        'bullets' => [
                            'Say what you do in one sentence.',
                            'Name the audience you want to attract.',
                            'Show the next step as soon as possible.',
                        ],
                    ],
                    [
                        'title' => 'Place trust before friction',
                        'text' => 'Visitors usually need a small amount of proof before they hand over their email address or project details. A good homepage adds trust near the CTA instead of hiding it in a separate section.',
                        'bullets' => [
                            'Show years of experience or project count.',
                            'Use a few credible proof points, not vague praise.',
                            'Keep the form short and easy on mobile.',
                        ],
                    ],
                    [
                        'title' => 'Send readers to the right service page',
                        'text' => 'The homepage should act like a guide, not a dead end. Link to the service pages that match the visitor’s intent so the journey from search to enquiry stays natural.',
                        'bullets' => [
                            'Homepage to service page.',
                            'Service page to contact form.',
                            'Blog article back to relevant money pages.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What is the most important part of a homepage?', 'a' => 'The most important part is the first screen: it should clearly say what you do and who you help.'],
                    ['q' => 'How many CTAs should a homepage have?', 'a' => 'One primary CTA is usually enough, with one secondary option for people who are not ready to enquire yet.'],
                    ['q' => 'Should a homepage cover every service?', 'a' => 'No. It should focus on the main offer and send people to dedicated service pages for details.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Contact page', 'href' => url('/#contact')],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'website-vs-web-app' => [
                'title' => 'Website vs web app: which one does your business need?',
                'meta_description' => 'Not sure whether you need a website or a web app? Here is a simple way to decide based on your business goals and workflow.',
                'canonical' => url('blog/website-vs-web-app'),
                'eyebrow' => 'Planning',
                'primaryKeyword' => 'website vs web app',
                'h1' => 'Website vs web app: which one does your business need?',
                'intro' => 'Many projects start with the wrong label. Some businesses only need a strong website, while others need custom functionality that behaves more like a web app. Choosing the right format saves time and budget.',
                'summary' => 'A simple decision guide for choosing between a website and a custom web app.',
                'published_at' => '2026-07-06',
                'reading_time' => '5 min read',
                'tags' => ['web app', 'website', 'project planning'],
                'sections' => [
                    [
                        'title' => 'Choose a website when the goal is clarity',
                        'text' => 'If people mainly need to read about your service, see your work, and contact you, a well-structured website is often the right choice. It should be fast, readable, and persuasive.',
                        'bullets' => [
                            'Best for service businesses.',
                            'Best when content and trust matter most.',
                            'Best when the workflow is simple.',
                        ],
                    ],
                    [
                        'title' => 'Choose a web app when users need to do work inside the product',
                        'text' => 'If people need to log in, manage data, track tasks, or interact with custom workflows, you are probably building a web app. That usually means more backend logic, more states, and more testing.',
                        'bullets' => [
                            'Dashboards and admin panels.',
                            'Customer portals and internal tools.',
                            'Workflow-heavy products.',
                        ],
                    ],
                    [
                        'title' => 'Start with the business outcome',
                        'text' => 'The right answer is usually not the technology label. It is the business outcome. If the result you want is more enquiries, a website may be enough. If the result is a tool people use daily, a web app makes more sense.',
                        'bullets' => [
                            'Outcome first, technology second.',
                            'Scope the first release carefully.',
                            'Build only what supports the goal.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What is the difference between a website and a web app?', 'a' => 'A website mainly presents information, while a web app lets users do tasks inside the product.'],
                    ['q' => 'Can a business have both?', 'a' => 'Yes. Many businesses start with a website and later add a web app or portal.'],
                    ['q' => 'How do I know which one I need?', 'a' => 'Start with the user action you want: contact, book, buy, manage, or log in.'],
                ],
                'related' => [
                    ['label' => 'Custom web application development', 'route' => 'services.web-application-development'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'why-react-native-is-a-good-fit-for-startup-apps' => [
                'title' => 'Why React Native is a good fit for startup apps',
                'meta_description' => 'See when React Native makes sense for startup apps, especially when you need faster delivery, shared code, and a practical launch path.',
                'canonical' => url('blog/why-react-native-is-a-good-fit-for-startup-apps'),
                'eyebrow' => 'Mobile apps',
                'primaryKeyword' => 'React Native for startup apps',
                'h1' => 'Why React Native is a good fit for startup apps',
                'intro' => 'For many startup teams, the first release needs to be practical, fast, and easier to support. React Native can be a smart choice when you want one codebase, faster iteration, and a smoother path to launch.',
                'summary' => 'A guide to when React Native makes sense for early-stage mobile products.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['React Native', 'startup apps', 'mobile strategy'],
                'sections' => [
                    [
                        'title' => 'When the shared codebase matters',
                        'text' => 'If your team wants to ship to both Android and iOS without building two separate projects, React Native can reduce duplication and make early iterations easier.',
                        'bullets' => [
                            'Faster first release.',
                            'Single team for both platforms.',
                            'Easier feature parity across devices.',
                        ],
                    ],
                    [
                        'title' => 'When it works especially well',
                        'text' => 'React Native is a good fit for apps that need accounts, dashboards, booking flows, notifications, API-driven content, or other standard product patterns. It is often strongest when the product logic matters more than platform-specific polish.',
                        'bullets' => [
                            'MVPs and startup launches.',
                            'Client-facing apps with standard UI patterns.',
                            'Products that need backend integration.',
                        ],
                    ],
                    [
                        'title' => 'What to plan before development starts',
                        'text' => 'The best results come from a tight first release scope. Decide what must be in version one, what can wait, and how the app will connect to the backend before the work begins.',
                        'bullets' => [
                            'Core screens only.',
                            'Clear API plan.',
                            'Launch checklist and post-launch support.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'Is React Native good for startups?', 'a' => 'Yes. It can help startups ship faster with one codebase for Android and iOS.'],
                    ['q' => 'When is React Native not the best choice?', 'a' => 'If the app needs heavy platform-specific work or very specialized native features, native development may be a better fit.'],
                    ['q' => 'What should I prepare before starting?', 'a' => 'Prepare the key screens, main features, backend needs, and launch scope.'],
                ],
                'related' => [
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'when-laravel-is-the-right-backend-choice' => [
                'title' => 'When Laravel is the right backend choice',
                'meta_description' => 'Learn when Laravel is a strong backend choice for custom websites, dashboards, and business systems that need structure and maintainability.',
                'canonical' => url('blog/when-laravel-is-the-right-backend-choice'),
                'eyebrow' => 'Backend planning',
                'primaryKeyword' => 'Laravel backend choice',
                'h1' => 'When Laravel is the right backend choice',
                'intro' => 'Laravel is often the right answer when a project needs custom business logic, a clean structure, and a backend that can grow with the product. It is especially useful when the site is more than a brochure.',
                'summary' => 'A practical guide to deciding when Laravel is the right backend stack.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['Laravel', 'backend', 'web app architecture'],
                'sections' => [
                    [
                        'title' => 'Use Laravel when the project has real application logic',
                        'text' => 'If the site needs authentication, roles, dashboards, forms, stored data, or custom workflows, Laravel can keep the backend organized and easier to maintain.',
                        'bullets' => [
                            'User accounts and permissions.',
                            'Custom admin areas.',
                            'Business logic that grows over time.',
                        ],
                    ],
                    [
                        'title' => 'Why teams choose it for long-term work',
                        'text' => 'Laravel gives structure to the codebase, which helps when a project is expected to change. That structure can make future updates simpler than patching everything together later.',
                        'bullets' => [
                            'Cleaner project structure.',
                            'Easier maintenance.',
                            'Better fit for evolving products.',
                        ],
                    ],
                    [
                        'title' => 'What to decide early',
                        'text' => 'Before development, decide what data the app stores, who can access it, what actions users can take, and which integrations are essential for the first release.',
                        'bullets' => [
                            'Data model.',
                            'Roles and permissions.',
                            'Integrations and launch scope.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What makes Laravel a good backend choice?', 'a' => 'It works well for structured applications that need custom logic, admin tools, and long-term maintainability.'],
                    ['q' => 'Can Laravel power both websites and web apps?', 'a' => 'Yes. It can support marketing sites, dashboards, portals, and custom application features.'],
                    ['q' => 'What should I plan before starting?', 'a' => 'Map the users, data, actions, and integrations so the build starts with a clear scope.'],
                ],
                'related' => [
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'Custom web application development', 'route' => 'services.web-application-development'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'seo-basics-for-service-businesses' => [
                'title' => 'SEO basics for service businesses that need organic leads',
                'meta_description' => 'A simple SEO guide for service businesses covering page structure, metadata, internal linking, and the basics that support organic leads.',
                'canonical' => url('blog/seo-basics-for-service-businesses'),
                'eyebrow' => 'SEO basics',
                'primaryKeyword' => 'SEO for service businesses',
                'h1' => 'SEO basics for service businesses that need organic leads',
                'intro' => 'SEO for a service business is not just about keywords. It is about helping search engines understand what you do, helping users trust the page, and making it easy for the right visitor to take the next step.',
                'summary' => 'A plain-language SEO guide for small businesses that want more qualified leads.',
                'published_at' => '2026-07-06',
                'reading_time' => '7 min read',
                'tags' => ['SEO', 'service business', 'organic leads'],
                'sections' => [
                    [
                        'title' => 'Start with one page per main service',
                        'text' => 'A common SEO mistake is trying to target too many services on one page. Separate service pages make the site easier to understand and easier to rank for specific topics.',
                        'bullets' => [
                            'One primary keyword per page.',
                            'Unique intro and supporting content.',
                            'Clear links back to the homepage and related services.',
                        ],
                    ],
                    [
                        'title' => 'Make the metadata useful, not generic',
                        'text' => 'Title tags, meta descriptions, and canonical tags should match the visible page content. That helps search engines and gives people a clearer reason to click.',
                        'bullets' => [
                            'Use descriptive title tags.',
                            'Write meta descriptions that explain the outcome.',
                            'Keep canonical URLs clean and consistent.',
                        ],
                    ],
                    [
                        'title' => 'Use internal links to guide authority',
                        'text' => 'When blog content links to service pages, it creates a better path from informational search to commercial pages. That flow matters if the goal is leads, not just traffic.',
                        'bullets' => [
                            'Blog article to service page.',
                            'Service page to contact form.',
                            'Homepage to the most important service pages.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What is the first SEO step for a service business?', 'a' => 'Start with clear service pages and make sure each page targets one main topic.'],
                    ['q' => 'Do blog articles help service SEO?', 'a' => 'Yes, if they support the service pages with useful internal links and relevant topics.'],
                    ['q' => 'What matters more, content or technical SEO?', 'a' => 'Both matter. Good content helps users, and clean technical setup helps search engines understand the site.'],
                ],
                'related' => [
                    ['label' => 'SEO services', 'route' => 'services.seo-services'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'improve-website-speed-without-a-full-redesign' => [
                'title' => 'How to improve website speed without a full redesign',
                'meta_description' => 'Learn practical ways to improve website speed without rebuilding the entire site, including images, scripts, layout weight, and hosting basics.',
                'canonical' => url('blog/improve-website-speed-without-a-full-redesign'),
                'eyebrow' => 'Performance',
                'primaryKeyword' => 'improve website speed',
                'h1' => 'How to improve website speed without a full redesign',
                'intro' => 'A slow site can hurt conversions and SEO, but you do not always need to rebuild the whole thing. In many cases, a focused set of fixes can make the site feel much faster and easier to use.',
                'summary' => 'A practical guide to speed improvements that support both SEO and conversion.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['website speed', 'Core Web Vitals', 'performance'],
                'sections' => [
                    [
                        'title' => 'Fix the heavy assets first',
                        'text' => 'Large images and unnecessary files are common speed problems. Compressing images, using the right formats, and loading only what is needed can make a visible difference quickly.',
                        'bullets' => [
                            'Compress hero and portfolio images.',
                            'Avoid oversized background assets.',
                            'Remove files that do not support the page.',
                        ],
                    ],
                    [
                        'title' => 'Reduce script and layout overhead',
                        'text' => 'Too many scripts, widgets, or complex visual effects can slow the page down. Simplifying these parts often helps more than adding another plugin or animation.',
                        'bullets' => [
                            'Trim third-party scripts.',
                            'Load only the features you need.',
                            'Keep the first screen lightweight.',
                        ],
                    ],
                    [
                        'title' => 'Speed matters because it affects trust',
                        'text' => 'A site that loads quickly feels more reliable and easier to use. That can improve both SEO signals and the odds that a visitor stays long enough to contact you.',
                        'bullets' => [
                            'Better mobile experience.',
                            'Lower bounce risk.',
                            'More confidence before the form.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'Do I need a redesign to improve speed?', 'a' => 'Not always. Often the biggest gains come from image, script, and layout cleanup.'],
                    ['q' => 'Why does speed matter for SEO?', 'a' => 'Speed affects user experience and can help search engines evaluate the page more positively.'],
                    ['q' => 'What should I fix first?', 'a' => 'Start with the largest files and the heaviest scripts on the page.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'SEO services', 'route' => 'services.seo-services'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'what-to-include-on-a-service-page' => [
                'title' => 'What to include on a service page so it ranks and converts',
                'meta_description' => 'See the sections every service page should include if you want better rankings, clearer messaging, and more enquiries.',
                'canonical' => url('blog/what-to-include-on-a-service-page'),
                'eyebrow' => 'Service pages',
                'primaryKeyword' => 'service page SEO',
                'h1' => 'What to include on a service page so it ranks and converts',
                'intro' => 'A good service page does more than describe the service. It helps the visitor understand the offer, trust the provider, and take action without needing a second page to explain the basics.',
                'summary' => 'A clear layout for service pages that need stronger SEO and more enquiries.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['service page SEO', 'conversion', 'on-page SEO'],
                'sections' => [
                    [
                        'title' => 'Open with the outcome',
                        'text' => 'The top of the page should explain the service in plain language and show the result the client can expect. That keeps the page focused and helps both humans and search engines understand the topic fast.',
                        'bullets' => [
                            'Clear headline.',
                            'Short intro.',
                            'One primary CTA.',
                        ],
                    ],
                    [
                        'title' => 'Answer the questions that block a decision',
                        'text' => 'People want to know who the service is for, how long it takes, how pricing works, and what support looks like after launch. If those answers are visible on the page, the page becomes more useful.',
                        'bullets' => [
                            'Audience.',
                            'Timeline.',
                            'Pricing approach.',
                            'Post-launch support.',
                        ],
                    ],
                    [
                        'title' => 'Link to related services and the homepage',
                        'text' => 'Internal links help search engines understand the site structure and help visitors continue the journey. A service page should connect to the homepage, related offers, and the contact section.',
                        'bullets' => [
                            'Service to service links.',
                            'Service to contact flow.',
                            'Service to homepage links.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What should every service page include?', 'a' => 'A clear offer, audience, outcome, process, proof, FAQ, and CTA.'],
                    ['q' => 'How many keywords should a service page target?', 'a' => 'Usually one primary keyword with a few supporting keywords.'],
                    ['q' => 'Should service pages link to each other?', 'a' => 'Yes, as long as the links are natural and useful to the reader.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'react-native-launch-checklist-for-startups' => [
                'title' => 'React Native launch checklist for startup teams',
                'meta_description' => 'Use this React Native launch checklist to prepare your startup app for development, testing, and a smoother release.',
                'canonical' => url('blog/react-native-launch-checklist-for-startups'),
                'eyebrow' => 'Mobile app launch',
                'primaryKeyword' => 'React Native launch checklist',
                'h1' => 'React Native launch checklist for startup teams',
                'intro' => 'A smooth app launch depends on more than code. Startups need the screens, features, integrations, and support plan mapped out before development starts so the first release does not become the first rewrite.',
                'summary' => 'A practical launch checklist for founders building a React Native app.',
                'published_at' => '2026-07-06',
                'reading_time' => '5 min read',
                'tags' => ['React Native', 'launch checklist', 'startup app'],
                'sections' => [
                    [
                        'title' => 'Define the release scope',
                        'text' => 'The first release should solve one clear problem. List the essential screens and features, then move everything else into a later phase.',
                        'bullets' => [
                            'Core user journeys.',
                            'Must-have screens.',
                            'Later-phase features.',
                        ],
                    ],
                    [
                        'title' => 'Prepare the backend and data flow',
                        'text' => 'Mobile apps depend on clean data flow. Decide what the app sends, what it receives, and how the backend should respond before the build begins.',
                        'bullets' => [
                            'API endpoints.',
                            'Authentication.',
                            'Data validation.',
                        ],
                    ],
                    [
                        'title' => 'Plan the launch support',
                        'text' => 'The launch is not the end of the project. Build time for testing, fixes, app store submission, and post-launch improvements into the plan.',
                        'bullets' => [
                            'Test on real devices.',
                            'Prepare store assets.',
                            'Leave room for post-launch fixes.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What should be in a React Native launch plan?', 'a' => 'Core screens, backend endpoints, testing, store assets, and post-launch support.'],
                    ['q' => 'Why do startups need a checklist?', 'a' => 'It keeps the first release focused and reduces surprises during development.'],
                    ['q' => 'Can launch support continue after release?', 'a' => 'Yes. Post-launch fixes and improvements are part of a healthy release plan.'],
                ],
                'related' => [
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'react-native-app-cost' => [
                'title' => 'How much does a React Native app cost?',
                'meta_description' => 'Understand what affects React Native app cost, from scope and design to backend work, testing, and launch support.',
                'canonical' => url('blog/react-native-app-cost'),
                'eyebrow' => 'App budgeting',
                'primaryKeyword' => 'React Native app cost',
                'h1' => 'How much does a React Native app cost?',
                'intro' => 'React Native app cost depends on what the app needs to do, how polished it needs to feel, and how much backend work is involved. A simple app and a feature-rich product can land in very different budgets.',
                'summary' => 'A practical breakdown of the main cost drivers behind React Native app projects.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['React Native', 'app cost', 'budgeting'],
                'sections' => [
                    [
                        'title' => 'The main cost drivers',
                        'text' => 'The fastest way to estimate cost is to look at scope. More screens, more roles, more integrations, and more custom logic all increase the amount of design, development, and testing required.',
                        'bullets' => [
                            'Number of screens and user flows.',
                            'Backend and API complexity.',
                            'Design polish and animations.',
                        ],
                    ],
                    [
                        'title' => 'Where teams usually overspend',
                        'text' => 'Budgets often stretch when teams try to build too many features in version one. A tighter first release usually gives a better result and makes the spending easier to justify.',
                        'bullets' => [
                            'Extra features that can wait.',
                            'Multiple user types too early.',
                            'Polish before core functionality.',
                        ],
                    ],
                    [
                        'title' => 'How to scope a realistic budget',
                        'text' => 'Start by defining the minimum version that solves the main problem. Then separate must-have features from later enhancements so the budget reflects the actual launch goal.',
                        'bullets' => [
                            'Define version one clearly.',
                            'List later-phase ideas separately.',
                            'Reserve time for testing and post-launch fixes.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What makes a React Native app more expensive?', 'a' => 'More screens, custom logic, backend work, and polished UI all raise the cost.'],
                    ['q' => 'Can I launch with a small budget?', 'a' => 'Yes, if the first release solves one clear problem and avoids unnecessary extras.'],
                    ['q' => 'Should I plan for post-launch costs?', 'a' => 'Yes. Updates, fixes, and support are part of a realistic app budget.'],
                ],
                'related' => [
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'how-long-app-development-takes' => [
                'title' => 'How long app development takes from idea to launch',
                'meta_description' => 'See the typical phases of app development and what can speed up or delay a mobile app launch.',
                'canonical' => url('blog/how-long-app-development-takes'),
                'eyebrow' => 'App timeline',
                'primaryKeyword' => 'how long app development takes',
                'h1' => 'How long app development takes from idea to launch',
                'intro' => 'App timelines vary by scope, but most delays come from unclear requirements, changing features, or backend decisions that were not made early. A simple plan makes the timeline easier to trust.',
                'summary' => 'A clear explanation of the phases that shape a mobile app timeline.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['app timeline', 'mobile development', 'planning'],
                'sections' => [
                    [
                        'title' => 'Discovery and scope setting',
                        'text' => 'The first phase is deciding what the app should do and what belongs in version one. Good discovery shortens the rest of the project because fewer decisions are left open.',
                        'bullets' => [
                            'User journeys.',
                            'Core features.',
                            'Technical constraints.',
                        ],
                    ],
                    [
                        'title' => 'Design and development',
                        'text' => 'Once the scope is clear, the team can design the screens and build the product in a predictable order. The more stable the scope, the easier it is to keep the schedule moving.',
                        'bullets' => [
                            'Wireframes and UI design.',
                            'Frontend and backend build.',
                            'Testing during development.',
                        ],
                    ],
                    [
                        'title' => 'Testing, fixes, and launch',
                        'text' => 'The final stretch usually includes device testing, bug fixes, store submission, and launch adjustments. Leaving room for this phase helps the app ship more smoothly.',
                        'bullets' => [
                            'QA on real devices.',
                            'App store review time.',
                            'Post-launch fixes and updates.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What affects app development time the most?', 'a' => 'Scope, design complexity, backend integrations, and how quickly decisions are made.'],
                    ['q' => 'Can a simple app launch faster?', 'a' => 'Yes. A focused first release is usually much faster than a feature-heavy one.'],
                    ['q' => 'Should I include testing time in the schedule?', 'a' => 'Absolutely. Testing and fixes are part of the timeline, not an extra.'],
                ],
                'related' => [
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'seo-for-small-businesses' => [
                'title' => 'SEO for small businesses: a practical starting plan',
                'meta_description' => 'A simple SEO plan for small businesses covering service pages, local intent, internal links, and the basics that drive qualified traffic.',
                'canonical' => url('blog/seo-for-small-businesses'),
                'eyebrow' => 'Small business SEO',
                'primaryKeyword' => 'SEO for small businesses',
                'h1' => 'SEO for small businesses: a practical starting plan',
                'intro' => 'Small business SEO works best when it focuses on the pages customers actually need. Instead of chasing everything at once, build a site that clearly explains your services and helps nearby or high-intent visitors contact you.',
                'summary' => 'A practical SEO starter plan for small businesses that want more qualified traffic.',
                'published_at' => '2026-07-06',
                'reading_time' => '7 min read',
                'tags' => ['small business SEO', 'local SEO', 'organic traffic'],
                'sections' => [
                    [
                        'title' => 'Build the right pages first',
                        'text' => 'A small business site should make each main service easy to find. One strong page per core service is usually more effective than one broad page trying to cover everything.',
                        'bullets' => [
                            'Homepage with a clear offer.',
                            'Dedicated service pages.',
                            'Contact page with a simple form.',
                        ],
                    ],
                    [
                        'title' => 'Match search intent',
                        'text' => 'People searching for a small business often want a local provider, a clear price approach, or a fast way to ask a question. The page should answer those points in plain language.',
                        'bullets' => [
                            'Who the service is for.',
                            'How the process works.',
                            'How to get in touch.',
                        ],
                    ],
                    [
                        'title' => 'Use content to support the services',
                        'text' => 'Helpful articles can answer common questions and send readers to the right service page. That turns content into a support system for leads instead of isolated blog traffic.',
                        'bullets' => [
                            'Blog to service links.',
                            'Service to contact links.',
                            'Natural anchors that fit the topic.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What is the first SEO task for a small business?', 'a' => 'Create clear service pages and make sure each one targets a specific search intent.'],
                    ['q' => 'Do small businesses need blog content?', 'a' => 'Yes, if the articles answer real customer questions and point back to the service pages.'],
                    ['q' => 'Is local SEO important for small businesses?', 'a' => 'Usually yes, especially when the business serves people in a specific location or region.'],
                ],
                'related' => [
                    ['label' => 'SEO services', 'route' => 'services.seo-services'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'website-vs-app-for-startups' => [
                'title' => 'Website vs app for startups: which should you build first?',
                'meta_description' => 'A startup-focused comparison of websites and apps so you can choose the first build based on budget, speed, and validation goals.',
                'canonical' => url('blog/website-vs-app-for-startups'),
                'eyebrow' => 'Startup planning',
                'primaryKeyword' => 'website vs app for startups',
                'h1' => 'Website vs app for startups: which should you build first?',
                'intro' => 'Startups do not usually have unlimited time or budget, so the first build should match the main goal. Sometimes a website is the fastest way to validate demand, and sometimes an app is the better product if users must return often.',
                'summary' => 'A startup decision guide for choosing between a website and a mobile app first.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['startup strategy', 'website', 'mobile app'],
                'sections' => [
                    [
                        'title' => 'Choose a website when validation comes first',
                        'text' => 'If the startup needs to test messaging, collect leads, or sell a simple service, a website is usually faster and cheaper to launch.',
                        'bullets' => [
                            'Faster to ship.',
                            'Lower initial budget.',
                            'Good for lead generation and early validation.',
                        ],
                    ],
                    [
                        'title' => 'Choose an app when the product experience is the business',
                        'text' => 'If the core value lives inside the product and users need repeat sessions, logins, or ongoing task management, an app may be the better first investment.',
                        'bullets' => [
                            'Daily or repeated use.',
                            'User accounts and workflows.',
                            'Push notifications or device features.',
                        ],
                    ],
                    [
                        'title' => 'Make the first release smaller than the full idea',
                        'text' => 'Most startups benefit from a narrow version one. Start with the smallest version that proves the value, then expand once you have real feedback.',
                        'bullets' => [
                            'One clear problem to solve.',
                            'Only the essential features.',
                            'A path to iterate after launch.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'Should every startup build an app?', 'a' => 'No. Many startups should begin with a website or landing page to validate demand first.'],
                    ['q' => 'When does an app make more sense than a website?', 'a' => 'When the product depends on repeat use, logged-in experiences, or device-specific features.'],
                    ['q' => 'Can a startup begin with both?', 'a' => 'Yes, but only if the budget and timeline support both without weakening the launch.'],
                ],
                'related' => [
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'maintenance-and-support-after-launch' => [
                'title' => 'Maintenance and support after launch: what should be included?',
                'meta_description' => 'Learn what website and app maintenance should include after launch, from bug fixes and updates to content changes and support.',
                'canonical' => url('blog/maintenance-and-support-after-launch'),
                'eyebrow' => 'Post-launch support',
                'primaryKeyword' => 'maintenance and support after launch',
                'h1' => 'Maintenance and support after launch: what should be included?',
                'intro' => 'Launch day is not the end of the project. Good support keeps the site or app stable, improves the user experience, and gives you a clear way to handle fixes and updates after release.',
                'summary' => 'A simple guide to what post-launch maintenance should cover for digital projects.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['maintenance', 'support', 'post-launch'],
                'sections' => [
                    [
                        'title' => 'Cover the basics first',
                        'text' => 'The most useful support usually starts with bug fixes, security updates, and small changes that keep the product working as expected.',
                        'bullets' => [
                            'Fix issues reported by users.',
                            'Keep dependencies up to date.',
                            'Handle small content or layout changes.',
                        ],
                    ],
                    [
                        'title' => 'Plan for real-world usage',
                        'text' => 'Once users start using the product, you learn what should change. Support should leave room for refinements based on feedback, not just emergency fixes.',
                        'bullets' => [
                            'Priority bug triage.',
                            'UX improvements.',
                            'Feature adjustments after feedback.',
                        ],
                    ],
                    [
                        'title' => 'Set expectations before launch',
                        'text' => 'A good support plan explains what is included, how requests are handled, and how often the product will be checked. That keeps the relationship clear after release.',
                        'bullets' => [
                            'Response window.',
                            'Included updates.',
                            'Ongoing support scope.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'Do websites and apps need ongoing maintenance?', 'a' => 'Yes. Both need updates, fixes, and occasional improvements after launch.'],
                    ['q' => 'What should support include?', 'a' => 'Bug fixes, updates, small content changes, and a clear process for new requests.'],
                    ['q' => 'Is post-launch support only for emergencies?', 'a' => 'No. It should also cover improvements and routine maintenance.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'laravel-dashboard-features-small-businesses-need' => [
                'title' => 'Laravel dashboard features small businesses actually need',
                'meta_description' => 'A practical guide to the Laravel dashboard features small businesses need most, including roles, data views, reports, and admin actions.',
                'canonical' => url('blog/laravel-dashboard-features-small-businesses-need'),
                'eyebrow' => 'Dashboards',
                'primaryKeyword' => 'Laravel dashboard features',
                'h1' => 'Laravel dashboard features small businesses actually need',
                'intro' => 'A dashboard should make operations simpler, not more complicated. For small businesses, the most valuable Laravel dashboards are usually the ones that help a team see data, act quickly, and keep control of day-to-day work.',
                'summary' => 'A guide to the dashboard features most small businesses should build first.',
                'published_at' => '2026-07-06',
                'reading_time' => '6 min read',
                'tags' => ['Laravel', 'dashboard', 'small business'],
                'sections' => [
                    [
                        'title' => 'Start with the core actions',
                        'text' => 'The first dashboard version should focus on the actions the team needs every day. That usually means viewing records, updating statuses, and searching the most important data.',
                        'bullets' => [
                            'Simple overview cards.',
                            'Search and filters.',
                            'Update and review actions.',
                        ],
                    ],
                    [
                        'title' => 'Add permissions and roles early',
                        'text' => 'Many business systems need different access levels. Roles help keep the dashboard secure and prevent every user from seeing every action.',
                        'bullets' => [
                            'Admin access.',
                            'Manager access.',
                            'Limited staff access.',
                        ],
                    ],
                    [
                        'title' => 'Keep the UI focused on work',
                        'text' => 'A dashboard should be fast to scan and simple to use. If every screen tries to do too much, the team spends more time finding actions than actually completing them.',
                        'bullets' => [
                            'Fewer distractions.',
                            'Clear call-to-action buttons.',
                            'Readable tables and filters.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'What should a small business dashboard include?', 'a' => 'Core data views, search, filters, roles, and the actions your team uses most often.'],
                    ['q' => 'Is every feature needed in version one?', 'a' => 'No. Start with the most important workflows and add the rest later.'],
                    ['q' => 'Why use Laravel for dashboards?', 'a' => 'Laravel works well for structured business logic, roles, and maintainable admin systems.'],
                ],
                'related' => [
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'Custom web application development', 'route' => 'services.web-application-development'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
            'internal-linking-for-service-business-blogs' => [
                'title' => 'Internal linking for service business blogs: a simple strategy',
                'meta_description' => 'Learn a simple internal linking strategy for service business blogs that helps readers move from articles to service pages and contact forms.',
                'canonical' => url('blog/internal-linking-for-service-business-blogs'),
                'eyebrow' => 'Internal linking',
                'primaryKeyword' => 'internal linking for service business blogs',
                'h1' => 'Internal linking for service business blogs: a simple strategy',
                'intro' => 'A service business blog should not send readers in random directions. It should guide people from education into the pages that explain your offers, build trust, and capture enquiries.',
                'summary' => 'A clear internal linking approach for blogs that support service SEO.',
                'published_at' => '2026-07-06',
                'reading_time' => '5 min read',
                'tags' => ['internal linking', 'blog SEO', 'service pages'],
                'sections' => [
                    [
                        'title' => 'Link from articles to the most relevant service page',
                        'text' => 'Every article should connect to the service page that matches the search intent. That keeps the journey relevant and helps authority flow toward the page that can turn visitors into leads.',
                        'bullets' => [
                            'One article, one main service link.',
                            'Use natural anchor text.',
                            'Avoid forcing unrelated links.',
                        ],
                    ],
                    [
                        'title' => 'Use supporting links to build context',
                        'text' => 'A blog post can also link to the homepage, related services, and contact flow. These supporting links make the site easier to navigate and help users understand the broader offer.',
                        'bullets' => [
                            'Homepage.',
                            'Related services.',
                            'Contact section.',
                        ],
                    ],
                    [
                        'title' => 'Keep the site structure simple',
                        'text' => 'The best internal linking strategy is easy to follow. If a visitor can move from article to service page to contact page without confusion, you have built a useful path for both users and search engines.',
                        'bullets' => [
                            'Article to service page.',
                            'Service page to CTA.',
                            'Clear menu and footer links.',
                        ],
                    ],
                ],
                'faqs' => [
                    ['q' => 'Why does internal linking matter?', 'a' => 'It helps users navigate and helps search engines understand which pages are most important.'],
                    ['q' => 'How many links should a blog post have?', 'a' => 'Enough to be useful, but not so many that the page feels forced or cluttered.'],
                    ['q' => 'What should the anchor text look like?', 'a' => 'Use natural wording that describes the page people will land on.'],
                ],
                'related' => [
                    ['label' => 'SEO services', 'route' => 'services.seo-services'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Blog home', 'route' => 'blog'],
                ],
            ],
        ];
    }

    private function servicePages(): array
    {
        return [
            'mobile-app-development' => [
                'title' => 'Mobile App Development Services | Faisal Imtiaz',
                'meta_description' => 'Mobile app development services for startups and businesses: plan, build, and support Android and iOS apps with remote, practical delivery.',
                'canonical' => url('services/mobile-app-development'),
                'eyebrow' => 'Mobile App Development',
                'primaryKeyword' => 'mobile app development services',
                'h1' => 'Mobile app development services for startups and businesses.',
                'intro' => 'I help international founders and businesses plan, build, launch, and improve mobile products for Android, iOS, or both. I can work from a new product brief, an existing app, or an established design and backend team.',
                'supporting_keywords' => ['Android and iOS apps', 'Cross-platform app development', 'App MVP development', 'Backend/API integration'],
                'audience' => 'Best for founders, startups, and small businesses that need a practical remote partner for a new app, an MVP, or focused work on an existing mobile product.',
                'outcome' => 'A clear, maintainable mobile product with the core flows, integrations, and release scope defined around your users and business goals.',
                'timeline' => 'Timelines depend on the feature set and existing systems, but a focused first release can move faster than a larger product roadmap.',
                'pricing' => 'I price based on scope and complexity, not a one-size-fits-all package.',
                'support' => 'Yes. I can stay involved after launch for fixes, improvements, maintenance, and new features.',
                'service_heading' => 'Mobile app development services',
                'service_intro' => 'Focused product engineering for new apps, existing applications, and the systems they need to work with.',
                'service_items' => [
                    ['title' => 'New mobile product development', 'text' => 'Turn an agreed first-release scope into a usable mobile product, from application structure through the key user flows.'],
                    ['title' => 'Android and iOS app delivery', 'text' => 'Plan the target platforms around your users, launch scope, and product requirements, whether you need Android, iOS, or both.'],
                    ['title' => 'Cross-platform app development', 'text' => 'Use a cross-platform approach where it fits the product and delivery needs. React Native and Ionic work are represented in the portfolio.'],
                    ['title' => 'Existing app features and maintenance', 'text' => 'Add features, fix issues, and improve an existing mobile codebase when the product is already in progress.'],
                    ['title' => 'Backend and API integration', 'text' => 'Connect mobile experiences with existing or new APIs and backends, including Laravel, Node.js, Supabase, and Firebase where the project calls for them.'],
                    ['title' => 'Media-rich mobile products', 'text' => 'PhotoTrail demonstrates a mobile product built around shared photos and videos in a private event album.'],
                    ['title' => 'Testing and release support', 'text' => 'Prepare the agreed release scope, check the key flows, and support launch work such as the existing Google Play release path shown by PhotoTrail.'],
                ],
                'decision_heading' => 'Choosing the right app approach',
                'decision_intro' => 'The best approach depends on your users, target platforms, product scope, existing systems, and how quickly the first release needs to move.',
                'decision_items' => [
                    ['title' => 'Android, iOS, or both?', 'text' => 'Start with the platforms your users need most, then plan the first release around the core flows rather than expanding scope too early.'],
                    ['title' => 'Cross-platform or native?', 'text' => 'The choice should follow product requirements, device-specific needs, timeline, and the team supporting the app. React Native can be a strong fit when shared Android and iOS delivery is appropriate.', 'link' => ['label' => 'See React Native development', 'route' => 'services.react-native-development']],
                    ['title' => 'New build or existing app?', 'text' => 'A new product needs clear first-release scope; an existing app needs a review of its codebase, current blockers, backend connections, and next priorities.'],
                ],
                'process' => [
                    ['title' => 'Discovery', 'text' => 'We define the app goal, user flow, must-have features, and launch timeline.'],
                    ['title' => 'Scope and planning', 'text' => 'We turn the idea into a realistic first release with clear platform, integration, and delivery priorities.'],
                    ['title' => 'Product and technical direction', 'text' => 'I review the user flows, existing designs or code, backend needs, and the app approach that fits the project.'],
                    ['title' => 'Development', 'text' => 'The mobile experience is built, connected to the required systems, and kept aligned with the agreed scope.'],
                    ['title' => 'Testing', 'text' => 'We check the key flows, device behavior, integrations, and release readiness.'],
                    ['title' => 'Launch', 'text' => 'I help prepare the release work that is in scope and support the handoff toward real users.'],
                    ['title' => 'Post-launch support', 'text' => 'I can remain involved for fixes, maintenance, improvements, and the next set of product changes.'],
                ],
                'benefits' => [
                    'A clear first-release plan that reduces wasted time and rework.',
                    'A mobile product shaped around real user flows and business goals.',
                    'A practical remote partner for new builds or existing app work.',
                    'Support that can continue after launch as the product develops.',
                ],
                'proof_heading' => 'Selected mobile app work',
                'proof_intro' => 'Portfolio examples across social, on-demand, food ordering, and mobility products, with the technologies shown for each project.',
                'technology_heading' => 'Mobile app technologies',
                'technology_intro' => 'The stack follows the product, its existing systems, and the release scope.',
                'technologies' => ['React Native', 'Ionic', 'Expo', 'JavaScript', 'Firebase', 'Supabase', 'Laravel', 'Node.js', 'MySQL', 'API integration'],
                'project_slugs' => ['phototrail', 'resq', 'grubly', 'melvony', 'fsmobility'],
                'faqs' => [
                    ['q' => 'Can I hire you to build a mobile app from scratch?', 'a' => 'Yes. We can define the first release, plan the key user flows and integrations, and build the app around the product goals that matter most.'],
                    ['q' => 'Can you work on an existing mobile application?', 'a' => 'Yes. I can review the existing scope and codebase, then take on focused feature work, fixes, improvements, or maintenance.'],
                    ['q' => 'How do I decide whether I need Android, iOS, or both?', 'a' => 'We look at your users, launch priorities, product scope, and timeline before deciding which platforms belong in the first release.'],
                    ['q' => 'Can the app connect to our existing backend or API?', 'a' => 'Yes. The portfolio includes mobile products connected with Laravel, Node.js, Supabase, and other backend systems, so the integration approach can follow your setup.'],
                    ['q' => 'Can you work with our existing design, frontend, backend, or team?', 'a' => 'Yes. We can define my responsibilities around the existing design, code, backend, and team workflow.'],
                    ['q' => 'What do you need before estimating a mobile app project?', 'a' => 'A short description of the users, core flows, target platforms, existing designs or code, backend needs, and first-release scope is a useful starting point.'],
                    ['q' => 'Do you provide maintenance after launch?', 'a' => 'Yes. I can stay involved after release for bug fixes, maintenance, updates, and new features as the product develops.'],
                ],
                'related' => [
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'About Faisal', 'route' => 'aboutme'],
                    ['label' => 'Client testimonials', 'route' => 'studio.testimonials'],
                    ['label' => 'PhotoTrail case study', 'route' => 'studio.work', 'slug' => 'phototrail'],
                    ['label' => 'ResQ case study', 'route' => 'studio.work', 'slug' => 'resq'],
                    ['label' => 'How long does app development take?', 'href' => url('blog/how-long-app-development-takes')],
                    ['label' => 'React Native launch checklist', 'href' => url('blog/react-native-launch-checklist-for-startups')],
                    ['label' => 'Start a project', 'href' => url('/#contact')],
                ],
            ],
            'react-native-development' => [
                'title' => 'Hire a React Native Developer | Faisal Imtiaz',
                'meta_description' => 'Hire Faisal Imtiaz to build or improve React Native apps for Android and iOS, with Expo, backend integrations, and remote project support.',
                'canonical' => url('services/react-native-development'),
                'eyebrow' => 'React Native Development',
                'primaryKeyword' => 'hire React Native developer',
                'h1' => 'Hire a React Native developer for your next app.',
                'intro' => 'I build and improve React Native applications for startups and businesses that need a practical path to Android and iOS. I work remotely with international teams on new products, existing codebases, and the work required to move an app toward launch.',
                'supporting_keywords' => ['React Native app development', 'Expo development', 'Android and iOS apps', 'Remote collaboration'],
                'audience' => 'For founders and product teams that need one experienced developer for a new React Native app, existing app work, or a focused delivery sprint.',
                'outcome' => 'A clear, maintainable mobile application with a delivery plan that fits the product and the team behind it.',
                'timeline' => 'Timelines depend on scope, but shared-codebase apps are usually faster to ship than separate native builds.',
                'pricing' => 'I scope and price the work based on features, integrations, and delivery complexity.',
                'support' => 'Yes. I can stay involved after launch for fixes, updates, and feature improvements.',
                'process' => [
                    ['title' => 'Discovery', 'text' => 'We clarify the product, users, priorities, and the decision the first release needs to support.'],
                    ['title' => 'Scope and planning', 'text' => 'We turn the idea into a realistic release scope with clear features and integration needs.'],
                    ['title' => 'Architecture', 'text' => 'I shape the application structure, data flow, and boundaries needed for maintainable work.'],
                    ['title' => 'Development', 'text' => 'I build the React Native experience with reusable components and a clean project structure.'],
                    ['title' => 'Testing', 'text' => 'We check the key flows, device behavior, integrations, and release readiness.'],
                    ['title' => 'Launch', 'text' => 'I help prepare the release and work through the launch checks that are in scope.'],
                    ['title' => 'Post-launch support', 'text' => 'I can remain involved for fixes, improvements, and the next set of product changes.'],
                ],
                'benefits' => [
                    'One React Native codebase for Android and iOS delivery.',
                    'A practical developer who can work with an existing team or backend.',
                    'Clear scope and progress visibility before implementation expands.',
                    'Support that can continue after the first release.',
                ],
                'service_items' => [
                    ['title' => 'New React Native app development', 'text' => 'Build a new mobile product from an agreed first-release scope, from application structure through the key user flows.'],
                    ['title' => 'Existing app features and maintenance', 'text' => 'Add features, fix issues, and improve an existing React Native codebase when you need focused engineering support.'],
                    ['title' => 'Expo development', 'text' => 'Use Expo where it fits the product and delivery needs. PhotoTrail is an existing Expo and React Native project in the portfolio.'],
                    ['title' => 'Backend and API integrations', 'text' => 'Connect the mobile experience with APIs and backends including Supabase, Node.js, and Laravel where the project calls for it.'],
                    ['title' => 'Media features and release support', 'text' => 'PhotoTrail demonstrates a React Native product built around shared photos and videos, with Google Play availability as part of its release path.'],
                ],
                'technologies' => ['React Native', 'Expo', 'JavaScript', 'Supabase', 'Node.js', 'Laravel', 'Firebase', 'REST APIs', 'Google Play'],
                'project_slugs' => ['phototrail', 'resq', 'grubly', 'melvony'],
                'related' => [
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'About Faisal', 'route' => 'aboutme'],
                    ['label' => 'Client testimonials', 'route' => 'studio.testimonials'],
                    ['label' => 'Why React Native fits startup apps', 'href' => url('blog/why-react-native-is-a-good-fit-for-startup-apps')],
                    ['label' => 'React Native vs Flutter for a startup MVP', 'href' => url('blog/react-native-vs-flutter-startup-mvp')],
                    ['label' => 'React Native launch checklist', 'href' => url('blog/react-native-launch-checklist-for-startups')],
                    ['label' => 'Start a project', 'href' => url('/#contact')],
                ],
                'faqs' => [
                    ['q' => 'Can I hire you for a new React Native app?', 'a' => 'Yes. We can define the first release, plan the integrations, and build the app around the product goals that matter most.'],
                    ['q' => 'Can you work on an existing React Native codebase?', 'a' => 'Yes. I can take on focused feature work, fixes, and improvements when you already have an application in progress.'],
                    ['q' => 'Do you work with international teams?', 'a' => 'Yes. I work remotely with startups and businesses, and the website reflects work with clients around the world.'],
                    ['q' => 'Can you work with our existing backend or team?', 'a' => 'Yes. The portfolio includes React Native projects using Supabase, Node.js, and Laravel, so the integration approach can follow the project setup.'],
                    ['q' => 'What do you need before estimating a project?', 'a' => 'A short description of the users, core flows, target platforms, existing designs or code, backend needs, and the first release scope is a useful starting point.'],
                    ['q' => 'Do you provide maintenance after launch?', 'a' => 'Yes. I can stay involved after release for bug fixes, updates, and new features as the product develops.'],
                ],
            ],
            'web-application-development' => [
                'title' => 'Custom Web Application Development | Faisal Imtiaz',
                'meta_description' => 'Custom web application development for dashboards, marketplaces, workflows, and API-connected business software built around your process.',
                'canonical' => url('services/web-application-development'),
                'eyebrow' => 'Web Application Development',
                'primaryKeyword' => 'custom web application development',
                'h1' => 'Custom web applications built around how your business works.',
                'intro' => 'I design and build browser-based applications for startups and businesses that need more than a marketing website: dashboards, marketplaces, booking workflows, data-driven tools, and backend-connected products.',
                'supporting_keywords' => ['Web app development services', 'Business web applications', 'Dashboard development', 'API-connected applications'],
                'audience' => 'Best for founders and businesses that need users or teams to sign in, manage data, complete workflows, or use software through a browser.',
                'outcome' => 'A clear, maintainable web application with the interface, business logic, data flow, and delivery scope shaped around your actual process.',
                'timeline' => 'Timelines depend on the workflows, integrations, and existing systems involved; a focused first release can move faster than a larger platform.',
                'pricing' => 'I quote based on the workflows, features, integrations, data structure, and complexity of the application.',
                'support' => 'Yes. I can stay involved after launch for fixes, improvements, maintenance, and future application work.',
                'service_heading' => 'What I build',
                'service_intro' => 'Software-like web products that help people complete work, manage information, or use a service through a browser.',
                'service_items' => [
                    ['title' => 'Custom business web applications', 'text' => 'Build browser-based products around the workflows, roles, data, and actions your business actually needs.'],
                    ['title' => 'Dashboards and admin panels', 'text' => 'Create operational interfaces for managing records, services, bookings, or application activity. FsMobility demonstrates a Laravel-powered platform with an admin dashboard.'],
                    ['title' => 'Marketplaces and booking systems', 'text' => 'Develop software for listings, service requests, bookings, pricing, and tracking where the product requires those workflows. Handiman and Meta Car show relevant marketplace experience.'],
                    ['title' => 'Data-driven applications', 'text' => 'Turn structured data into useful browser experiences such as results, reporting, listings, or operational views. Your Results demonstrates a data-focused dashboard product.'],
                    ['title' => 'API-connected frontend and backend work', 'text' => 'Connect the application interface to the backend systems and APIs required by the product, including Laravel-based systems where appropriate.'],
                    ['title' => 'Existing application improvements', 'text' => 'Review an existing application, clarify the next priority, and take on focused features, fixes, or maintenance without restarting the whole product.'],
                ],
                'decision_heading' => 'Website or web application?',
                'decision_intro' => 'The distinction is about what people need to do, not just what the project is called.',
                'decision_items' => [
                    ['title' => 'Choose a website for information', 'text' => 'A business or marketing website is usually the right fit when the main goal is explaining an offer, building trust, and generating enquiries.', 'link' => ['label' => 'Explore website development', 'route' => 'services.website-development']],
                    ['title' => 'Choose a web application for workflows', 'text' => 'A web application is a better fit when users need accounts, dashboards, stored data, transactions, custom workflows, or a tool they use repeatedly.'],
                    ['title' => 'Use the backend that fits the product', 'text' => 'Laravel can support structured business logic, data, APIs, dashboards, and application features when it matches the project requirements.', 'link' => ['label' => 'See Laravel development', 'route' => 'services.laravel-development']],
                ],
                'process' => [
                    ['title' => 'Discovery', 'text' => 'We clarify the users, business problem, workflows, and first release before implementation begins.'],
                    ['title' => 'Requirements and workflows', 'text' => 'We map the records, roles, actions, states, and screens the application needs to support.'],
                    ['title' => 'Architecture', 'text' => 'I shape the frontend, backend, data flow, and integration boundaries around the agreed scope.'],
                    ['title' => 'Application development', 'text' => 'The browser interface and application logic are built around the workflows users need to complete.'],
                    ['title' => 'Backend and integrations', 'text' => 'We connect the application to the required APIs, data sources, and existing systems in scope.'],
                    ['title' => 'Testing', 'text' => 'We check the important user paths, data behavior, permissions in scope, and release readiness.'],
                    ['title' => 'Deployment and support', 'text' => 'I help prepare the application for launch and can remain involved for maintenance and future improvements.'],
                ],
                'benefits' => [
                    'A product shaped around your real workflows instead of a generic template.',
                    'Clear separation between user-facing interface, backend logic, and data responsibilities.',
                    'A focused first release that can grow as the application proves its value.',
                    'A remote developer who can work with an existing frontend, backend, or team.',
                ],
                'proof_heading' => 'Selected web application work',
                'proof_intro' => 'Examples across dashboards, marketplaces, e-commerce systems, and data-driven products, with the technologies shown for each project.',
                'technology_heading' => 'Relevant web application technologies',
                'technology_intro' => 'The stack follows the application, its existing interface, and the backend responsibilities in scope.',
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'JavaScript', 'Ionic', 'React Native', 'Node.js', 'Supabase', 'API integration'],
                'project_slugs' => ['fsmobility', 'handiman', 'meta-car', 'your-results', 'custom-pennants-football'],
                'faqs' => [
                    ['q' => 'Can you build a web application from scratch?', 'a' => 'Yes. We can define the users, workflows, first-release scope, interface, backend responsibilities, and integrations before development begins.'],
                    ['q' => 'Can you improve an existing web application?', 'a' => 'Yes. I can review the existing application and take on focused features, fixes, improvements, or maintenance around the next priority.'],
                    ['q' => 'Can you build a dashboard or admin panel?', 'a' => 'Yes. FsMobility demonstrates a Laravel-powered platform with an admin dashboard for managing a taxi operation.'],
                    ['q' => 'Can a web application connect to our existing API or database?', 'a' => 'Yes. We can define the connection points and data responsibilities around the existing API, database, frontend, or backend in your project.'],
                    ['q' => 'Can you work with our existing frontend, backend, or team?', 'a' => 'Yes. We can define my responsibilities around the existing design, codebase, backend, and team workflow.'],
                    ['q' => 'How do you estimate a web application?', 'a' => 'I look at the users, workflows, screens, data, integrations, existing code, and first-release priorities before estimating the work.'],
                    ['q' => 'Do you provide maintenance after launch?', 'a' => 'Yes. I can stay involved after release for bug fixes, maintenance, updates, and future application features.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'Mobile app development', 'route' => 'services.mobile-app-development'],
                    ['label' => 'About Faisal', 'route' => 'aboutme'],
                    ['label' => 'Client testimonials', 'route' => 'studio.testimonials'],
                    ['label' => 'Website vs web app', 'href' => url('blog/website-vs-web-app')],
                    ['label' => 'When Laravel is the right backend choice', 'href' => url('blog/when-laravel-is-the-right-backend-choice')],
                    ['label' => 'Laravel vs WordPress for a client portal', 'href' => url('blog/laravel-vs-wordpress-client-portal')],
                    ['label' => 'Laravel dashboard features', 'href' => url('blog/laravel-dashboard-features-small-businesses-need')],
                    ['label' => 'Start a project', 'href' => url('/#contact')],
                ],
            ],
            'website-development' => [
                'title' => 'Website Development for Small Businesses | Faisal Imtiaz',
                'meta_description' => 'Faisal Imtiaz offers website development for businesses that need a fast, responsive, conversion-focused site with clean UI, solid performance, and deployment support.',
                'canonical' => url('services/website-development'),
                'eyebrow' => 'Website Development',
                'primaryKeyword' => 'website development',
                'h1' => 'Website development that turns visitors into leads.',
                'intro' => 'I create websites and web apps that look professional, load quickly, and guide people toward the next step.',
                'supporting_keywords' => ['responsive website', 'business website', 'conversion-focused design'],
                'audience' => 'Best for businesses that want a website that feels trustworthy and supports real business goals.',
                'outcome' => 'A website that clearly explains what you do and makes it easier for people to contact you.',
                'timeline' => 'Simple pages can move quickly, while larger sites with custom functionality usually take longer.',
                'pricing' => 'I price based on page count, functionality, and the amount of custom work required.',
                'support' => 'Yes. I can help after launch with fixes, content updates, and ongoing improvements.',
                'process' => [
                    ['title' => 'Understand the goal', 'text' => 'We define the audience, the offer, and the action you want visitors to take.'],
                    ['title' => 'Design the layout', 'text' => 'I shape the pages so the structure is clear and the message is easy to follow.'],
                    ['title' => 'Develop the site', 'text' => 'The site is built with responsive behavior, solid performance, and clean markup.'],
                    ['title' => 'Launch and refine', 'text' => 'I help you publish the site and improve it after launch if needed.'],
                ],
                'benefits' => [
                    'A site that looks credible on every screen size.',
                    'Clear messaging that supports conversions.',
                    'A foundation that is easier to update and expand.',
                    'A more polished online presence for your business.',
                ],
                'faqs' => [
                    ['q' => 'What services do you offer?', 'a' => 'I build websites, web apps, Laravel systems, React Native apps, and SEO-ready pages.'],
                    ['q' => 'Who is this service for?', 'a' => 'It is for small businesses and startups that need a clear, credible online presence.'],
                    ['q' => 'How long does a project take?', 'a' => 'Timelines depend on the page count and features, but smaller sites can move faster than larger builds.'],
                    ['q' => 'How do you price the work?', 'a' => 'I quote based on the number of pages, the amount of custom design, and any backend work.'],
                    ['q' => 'Do you support the site after launch?', 'a' => 'Yes. I can stay involved after launch for fixes, updates, and improvements.'],
                    ['q' => 'What is the process?', 'a' => 'We define the goal, design the pages, build the site, and then launch with support.'],
                ],
                'related' => [
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'SEO services', 'route' => 'services.seo-services'],
                    ['label' => 'Homepage FAQ', 'href' => url('/#faq')],
                    ['label' => 'Back to homepage', 'href' => url('/')],
                ],
            ],
            'laravel-development' => [
                'title' => 'Hire a Laravel Developer | Faisal Imtiaz',
                'meta_description' => 'Hire Faisal Imtiaz for Laravel web applications, APIs, admin dashboards, and backend systems with remote project support and maintenance.',
                'canonical' => url('services/laravel-development'),
                'eyebrow' => 'Laravel Development',
                'primaryKeyword' => 'hire Laravel developer',
                'h1' => 'Hire a Laravel developer for your web application.',
                'intro' => 'I build and improve Laravel web applications, APIs, dashboards, and backend systems for startups and businesses. I work remotely with international teams on new products, existing codebases, and the integrations needed to move a project toward launch.',
                'supporting_keywords' => ['Laravel web applications', 'Laravel backend development', 'Laravel API development', 'PHP Laravel developer'],
                'audience' => 'For founders and product teams that need a dependable Laravel developer for a new application, an existing codebase, or a focused backend delivery sprint.',
                'outcome' => 'A structured Laravel application that supports your business rules, data, frontend, and future product changes.',
                'timeline' => 'Timelines depend on the amount of custom logic, but larger backend systems usually take longer than simple pages.',
                'pricing' => 'I quote Laravel work based on features, integrations, data structure, and complexity.',
                'support' => 'Yes. I can stay involved after launch for bug fixes, new features, and maintenance.',
                'service_heading' => 'Laravel development services',
                'service_intro' => 'Practical backend and web-application support for new products and systems already in progress.',
                'proof_heading' => 'Selected Laravel work',
                'proof_intro' => 'Portfolio examples that show Laravel work across dashboards, marketplaces, mobile backends, and e-commerce.',
                'technology_heading' => 'Relevant Laravel technologies',
                'technology_intro' => 'The stack follows the application, its existing frontend, and the backend responsibilities in scope.',
                'process' => [
                    ['title' => 'Discovery', 'text' => 'We clarify the product, users, workflows, and the first release before implementation begins.'],
                    ['title' => 'Scope and data planning', 'text' => 'We define the features, data structure, integrations, and responsibilities of the Laravel application.'],
                    ['title' => 'Architecture', 'text' => 'I shape the application structure, backend boundaries, and connection points for the frontend or mobile app.'],
                    ['title' => 'Development', 'text' => 'I build the Laravel logic, workflows, APIs, and application features in the agreed scope.'],
                    ['title' => 'Integration and testing', 'text' => 'We connect the relevant interface or app and check the key flows before release.'],
                    ['title' => 'Deployment', 'text' => 'I help prepare the application for launch and work through the deployment checks in scope.'],
                    ['title' => 'Post-launch support', 'text' => 'I can remain involved for fixes, improvements, and future backend changes.'],
                ],
                'benefits' => [
                    'A structured Laravel codebase for custom business rules.',
                    'Backend work that can support a website, dashboard, or mobile app.',
                    'Clear scope and data planning before implementation expands.',
                    'Support that can continue after the first release.',
                ],
                'service_items' => [
                    ['title' => 'Custom Laravel web applications', 'text' => 'Build backend-driven products, portals, and business systems around the workflows your users actually need.'],
                    ['title' => 'Laravel backend and API development', 'text' => 'Create application logic and API connections that give a frontend or mobile app a dependable backend to work with.'],
                    ['title' => 'Admin panels and dashboards', 'text' => 'Build practical internal tools and dashboards for managing data, workflows, and day-to-day operations.'],
                    ['title' => 'Laravel backends for mobile apps', 'text' => 'Connect mobile products with Laravel systems where the app needs structured data, business logic, or an operational dashboard.'],
                    ['title' => 'Existing app maintenance and deployment support', 'text' => 'Take on focused fixes, feature improvements, launch checks, and ongoing maintenance for an existing Laravel application.'],
                ],
                'technologies' => ['Laravel', 'PHP', 'MySQL', 'JavaScript', 'REST APIs'],
                'project_slugs' => ['fsmobility', 'handiman', 'custom-pennants-football', 'grubly', 'melvony'],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'React Native development', 'route' => 'services.react-native-development'],
                    ['label' => 'About Faisal', 'route' => 'aboutme'],
                    ['label' => 'Client testimonials', 'route' => 'studio.testimonials'],
                    ['label' => 'When Laravel is the right backend choice', 'href' => url('blog/when-laravel-is-the-right-backend-choice')],
                    ['label' => 'Laravel dashboard features', 'href' => url('blog/laravel-dashboard-features-small-businesses-need')],
                    ['label' => 'Start a project', 'href' => url('/#contact')],
                ],
                'faqs' => [
                    ['q' => 'Can I hire you for a new Laravel project?', 'a' => 'Yes. We can define the first release, map the backend responsibilities, and build the application around the workflows that matter most.'],
                    ['q' => 'Can you work on an existing Laravel codebase?', 'a' => 'Yes. I can take on focused feature work, fixes, improvements, and maintenance when you already have an application in progress.'],
                    ['q' => 'Can Laravel be used as the backend for a mobile app?', 'a' => 'Yes. The portfolio includes Laravel-backed mobile projects, including Grubly and Melvony, alongside a Laravel-powered platform for FsMobility.'],
                    ['q' => 'Can you work with our existing frontend, design, or team?', 'a' => 'Yes. We can define the Laravel responsibilities and connect the backend to the existing interface, mobile app, or team workflow.'],
                    ['q' => 'What do you need before estimating a Laravel project?', 'a' => 'A description of the users, workflows, data, frontend or mobile client, integrations, existing code, and first-release scope is a useful starting point.'],
                    ['q' => 'Do you provide ongoing Laravel maintenance?', 'a' => 'Yes. I can stay involved after release for bug fixes, updates, feature work, and future improvements.'],
                ],
            ],
            'seo-services' => [
                'title' => 'SEO Services for Small Businesses | Faisal Imtiaz',
                'meta_description' => 'Need SEO services that improve visibility? Faisal Imtiaz helps small businesses with technical SEO, on-page optimization, speed improvements, and search-friendly site structure.',
                'canonical' => url('services/seo-services'),
                'eyebrow' => 'SEO Services',
                'primaryKeyword' => 'SEO services',
                'h1' => 'SEO services that help the right people find your business.',
                'intro' => 'I help small businesses improve search visibility with cleaner site structure, better page content, and technical fixes that support rankings.',
                'supporting_keywords' => ['technical SEO', 'on-page SEO', 'site speed'],
                'audience' => 'Best for businesses that want more qualified traffic and a site that search engines can understand.',
                'outcome' => 'A more search-friendly site that can attract the right visitors and turn them into leads.',
                'timeline' => 'SEO is usually a longer-term effort. Some fixes are immediate, while content and visibility improvements take time.',
                'pricing' => 'I price SEO based on the current state of the site, the work required, and the level of ongoing support needed.',
                'support' => 'Yes. I can help after launch with technical updates, page improvements, and ongoing SEO work.',
                'process' => [
                    ['title' => 'Audit the site', 'text' => 'I look at structure, indexing, performance, and page-level opportunities.'],
                    ['title' => 'Fix the basics', 'text' => 'We improve titles, descriptions, headings, and technical issues that block growth.'],
                    ['title' => 'Strengthen content', 'text' => 'I help shape pages around the keywords and topics people actually search for.'],
                    ['title' => 'Track improvements', 'text' => 'We watch how the pages perform and refine the work over time.'],
                ],
                'benefits' => [
                    'Better visibility for the services you want to rank for.',
                    'Cleaner pages that are easier for search engines to understand.',
                    'Technical improvements that support long-term growth.',
                    'A site structure that works for both users and search engines.',
                ],
                'faqs' => [
                    ['q' => 'What services do you offer?', 'a' => 'I help with technical SEO, on-page SEO, search-friendly structure, and site improvements.'],
                    ['q' => 'Who is this service for?', 'a' => 'It is for businesses that want better visibility and more qualified traffic from search.'],
                    ['q' => 'How long does SEO take?', 'a' => 'Some fixes help right away, but meaningful SEO results usually take time and steady improvement.'],
                    ['q' => 'How do you price SEO?', 'a' => 'I quote based on the site condition, the amount of work required, and whether ongoing support is needed.'],
                    ['q' => 'Do you support the site after launch?', 'a' => 'Yes. I can keep improving the site after launch as needed.'],
                    ['q' => 'What is the process?', 'a' => 'I audit the site, fix the basics, improve content structure, and then track changes over time.'],
                ],
                'related' => [
                    ['label' => 'Website development', 'route' => 'services.website-development'],
                    ['label' => 'Laravel development', 'route' => 'services.laravel-development'],
                    ['label' => 'Homepage FAQ', 'href' => url('/#faq')],
                    ['label' => 'Back to homepage', 'href' => url('/')],
                ],
            ],
        ];
    }
}
