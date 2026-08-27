<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\MarketingCredential;
use App\Models\MarketingEmail;
use App\Models\MarketingEmailOpen;
use App\Models\MarketingFollowupEmail;
use App\Models\MarketingFollowupEmailOpen;
use App\Models\MarketingTemplate;
use App\Models\MarketingUnsubscribe;
use App\Models\WebsiteClick;
use App\Models\WebsiteVisit;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;

class MainController extends Controller
{
    public function contactus(ContactRequest $request){
        try {
            $nameParts = preg_split('/\s+/', trim((string) $request->firstname), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            Contact::create([
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

    public function aboutme(){
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
    
    public function getcontacts(){
        $contacts = Contact::all();
        return json_encode(['msg' => 'success', 'data' => $contacts]);
    }

    public function showMarketingLogin(Request $request)
    {
        if ($request->session()->get('marketing_authenticated') === true) {
            return redirect()->route('marketing');
        }

        return view('marketing-login');
    }

    public function loginMarketing(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $validator->validate();

        $credential = $this->resolveMarketingCredential();

        if (! $credential) {
            return back()
                ->withInput($request->only('username'))
                ->with('marketing_login_error', 'Marketing login credentials are not configured.');
        }

        $validCredentials = hash_equals((string) $credential->username, (string) $request->input('username'))
            && Hash::check((string) $request->input('password'), (string) $credential->password_hash);

        if (! $validCredentials) {
            return back()
                ->withInput($request->only('username'))
                ->with('marketing_login_error', 'Invalid username or password.');
        }

        $request->session()->regenerate();
        $request->session()->put('marketing_authenticated', true);

        return redirect()->intended(route('marketing'));
    }

    public function logoutMarketing(Request $request)
    {
        $request->session()->forget('marketing_authenticated');
        $request->session()->regenerateToken();

        return redirect()->route('marketing.login');
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
        $activeTab = $request->query('tab', $request->routeIs('marketing.contact-form.show') ? 'contact-form-detail' : ($request->routeIs('marketing.contact-form') ? 'contact-form' : 'dashboard'));

        if ($activeTab === 'templates') {
            $activeTab = 'templates-list';
        }

        if (! in_array($activeTab, ['dashboard', 'send', 'sent-emails', 'sent-email-detail', 'followups', 'followups-create', 'followups-edit', 'contacts', 'contact-form', 'contact-form-detail', 'templates-create', 'templates-list', 'templates-edit', 'analytics'], true)) {
            $activeTab = 'dashboard';
        }

        $databaseReady = true;
        $editingTemplate = null;
        $editingFollowupEmail = null;
        $selectedEmail = null;
        $selectedFollowupEmail = null;
        $selectedRecipientStatuses = collect();
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
            Schema::hasColumn('website_visits', 'country') || throw new \RuntimeException('Website visit location columns are missing.');
            Schema::hasColumn('website_visits', 'last_seen_at') || throw new \RuntimeException('Website visit online status column is missing.');
            $marketingEmails = MarketingEmail::with('opens')->latest('sent_at')->get();
            $templates = MarketingTemplate::latest()->get();
            $followupEmails = MarketingFollowupEmail::with(['opens', 'originalEmail', 'template'])->latest('scheduled_at')->get();
            $websiteVisitsTotal = WebsiteVisit::count();
            $websiteClicksTotal = WebsiteClick::count();
            $activeVisits = WebsiteVisit::query()
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->latest('last_seen_at')
                ->get()
                ->unique('session_id')
                ->values();
            $activeVisitorsCount = $activeVisits->count();
            $activeVisitGroups = $this->websiteAnalyticsActiveGroups($activeVisits);
            $websiteVisits = WebsiteVisit::withCount('clicks')->latest('visited_at')->take(50)->get();
            $websiteClicks = WebsiteClick::latest('clicked_at')->take(50)->get();
            $topPages = WebsiteVisit::query()
                ->selectRaw('path, count(*) as visits_count')
                ->groupBy('path')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();
            $topClicks = WebsiteClick::query()
                ->selectRaw('path, element_text, element, count(*) as clicks_count')
                ->groupBy('path', 'element_text', 'element')
                ->orderByDesc('clicks_count')
                ->take(8)
                ->get();
            $topLocations = WebsiteVisit::query()
                ->selectRaw('country, region, city, count(*) as visits_count')
                ->whereNotNull('country')
                ->groupBy('country', 'region', 'city')
                ->orderByDesc('visits_count')
                ->take(8)
                ->get();
            $contactFormEntries = Contact::latest()->get();
            $contactFormCount = $contactFormEntries->count();
            $selectedContactFormEntry = $activeTab === 'contact-form-detail'
                ? Contact::find($request->route('contact'))
                : null;

            if ($activeTab === 'contact-form-detail' && ! $selectedContactFormEntry) {
                $activeTab = 'contact-form';
            }

            $selectedEmail = $activeTab === 'sent-email-detail'
                ? MarketingEmail::with('opens')->find($request->query('email'))
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
                    });
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
        }

        return view('marketing', [
            'activeTab' => $activeTab,
            'databaseReady' => $databaseReady,
            'emailsSent' => $marketingEmails->sum('recipient_count'),
            'emailsOpened' => $marketingEmails->sum(fn ($email) => $email->opens->whereNotNull('opened_at')->count()),
            'contactsCount' => $contacts->count(),
            'contacts' => $contacts,
            'contactStatuses' => $contactStatuses,
            'contactFormCount' => $contactFormCount,
            'contactFormEntries' => $contactFormEntries,
            'selectedContactFormEntry' => $selectedContactFormEntry,
            'recentEmails' => $marketingEmails->take(5),
            'sentEmails' => $marketingEmails,
            'selectedEmail' => $selectedEmail,
            'selectedFollowupEmail' => $selectedFollowupEmail,
            'selectedRecipientStatuses' => $selectedRecipientStatuses,
            'templates' => $templates,
            'followupEmails' => $followupEmails,
            'editingTemplate' => $editingTemplate,
            'editingFollowupEmail' => $editingFollowupEmail,
            'websiteVisits' => $websiteVisits,
            'websiteClicks' => $websiteClicks,
            'topPages' => $topPages,
            'topClicks' => $topClicks,
            'topLocations' => $topLocations,
            'activeVisitGroups' => $activeVisitGroups,
            'activeVisitorsCount' => $activeVisitorsCount,
            'websiteVisitsCount' => $websiteVisitsTotal,
            'websiteClicksCount' => $websiteClicksTotal,
            'templateOptions' => $templates->mapWithKeys(fn ($template) => [
                $template->id => [
                    'subject' => $template->subject,
                    'subjects' => $template->subject_options ?: [$template->subject],
                    'content' => $template->content,
                    'attachment_name' => $template->attachment_name,
                ],
            ]),
        ]);
    }

    public function trackWebsiteVisit(Request $request)
    {
        try {
            $ipAddress = $this->websiteAnalyticsIpAddress($request);
            $userAgent = (string) $request->userAgent();
            $location = $this->websiteAnalyticsLocation($ipAddress);
            $machine = $this->websiteAnalyticsMachine($userAgent);

            $visit = WebsiteVisit::create([
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

    public function trackWebsiteClick(Request $request)
    {
        try {
            WebsiteClick::create([
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

    public function deleteContactForm(Contact $contact)
    {
        $contact->delete();

        return redirect()
            ->route('marketing.contact-form')
            ->with('marketing_success', 'Contact form submission deleted successfully.');
    }

    public function sendMarketingEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'template_id' => ['nullable', 'integer'],
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
            Schema::hasColumn('marketing_emails', 'delivery_status') || throw new \RuntimeException('Marketing email delivery status column is missing.');
            Schema::hasColumn('marketing_templates', 'attachment_path') || throw new \RuntimeException('Marketing template attachment column is missing.');
            Schema::hasColumn('marketing_templates', 'subject_options') || throw new \RuntimeException('Marketing template subject options column is missing.');
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('marketing_error', 'Marketing storage is not ready yet. Please run the migration after your database credentials are configured.');
        }

        $selectedTemplate = $request->filled('template_id')
            ? MarketingTemplate::find($request->integer('template_id'))
            : null;

        $unsubscribed = MarketingUnsubscribe::query()
            ->whereIn('email', $recipients)
            ->pluck('email');

        $recipients = $recipients->diff($unsubscribed)->values();

        if ($recipients->isEmpty()) {
            return back()
                ->withInput()
                ->with('marketing_error', 'All selected recipients have unsubscribed from marketing emails.');
        }

        $attachmentPath = null;
        $attachmentName = null;
        $deleteAttachmentOnFailure = false;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
            $deleteAttachmentOnFailure = true;
        } elseif ($selectedTemplate && $selectedTemplate->attachment_path) {
            $attachmentPath = $selectedTemplate->attachment_path;
            $attachmentName = $selectedTemplate->attachment_name;
        }

        $marketingEmail = MarketingEmail::create([
            'recipients' => $recipients->all(),
            'recipient_count' => $recipients->count(),
            'subject' => $request->input('subject'),
            'body' => $request->input('content'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'delivery_status' => 'pending',
        ]);

        $openTrackers = $recipients->mapWithKeys(function ($recipient) use ($marketingEmail) {
            $tracker = MarketingEmailOpen::create([
                'marketing_email_id' => $marketingEmail->id,
                'email' => $recipient,
                'tracking_id' => (string) Str::uuid(),
            ]);

            return [$recipient => $tracker];
        });

        try {
            foreach ($recipients as $recipient) {
                $unsubscribeUrl = route('marketing.unsubscribe', ['email' => $recipient]);
                $plainBody = trim($request->input('content'));
                $trackingUrl = $this->marketingTrackingUrl($openTrackers[$recipient]->tracking_id);
                $htmlBody = '<div style="white-space:pre-wrap;font-family:Arial,sans-serif;font-size:14px;line-height:1.5;color:#111827;">'
                    .e($plainBody)
                    .'</div>'
                    .'<div style="margin-top:18px;">'
                    .'<img src="'.e($this->marketingDebugImageUrl()).'" width="320" alt="Faisal Imtiaz" style="display:block;width:320px;max-width:100%;height:auto;border:0;">'
                    .'</div>'
                    .'<img src="'.e($trackingUrl).'" width="1" height="1" alt="" style="width:1px;height:1px;border:0;opacity:0;">';

                Mail::send([], [], function ($message) use ($recipient, $request, $attachmentPath, $attachmentName, $unsubscribeUrl, $plainBody, $htmlBody) {
                    $message->to($recipient)
                        ->subject($request->input('subject'))
                        ->text($plainBody)
                        ->html($htmlBody);

                    $headers = $message->getSymfonyMessage()->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');

                    if ($attachmentPath) {
                        $message->attach(Storage::path($attachmentPath), ['as' => $attachmentName]);
                    }
                });
            }
        } catch (\Throwable $exception) {
            if ($attachmentPath && $deleteAttachmentOnFailure) {
                Storage::delete($attachmentPath);
            }

            $marketingEmail->update([
                'attachment_path' => null,
                'attachment_name' => null,
                'delivery_status' => 'failed',
                'delivery_error' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('marketing_error', 'Email could not be sent. Please check your mail settings and try again.');
        }

        $marketingEmail->update([
            'delivery_status' => 'delivered',
            'delivery_error' => null,
            'sent_at' => now(),
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'dashboard'])
            ->with('marketing_success', 'Email sent to '.$recipients->count().' contact'.($recipients->count() === 1 ? '.' : 's.'));
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
            'marketing_email_id' => $originalEmail->id,
            'marketing_template_id' => $template->id,
            'recipients' => $originalEmail->recipients ?: [],
            'recipient_count' => $originalEmail->recipient_count,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
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
            'marketing_template_id' => $template->id,
            'subject' => $subjects->first() ?: $template->subject,
            'body' => $template->content,
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

        try {
            MarketingTemplate::create([
                'name' => $request->input('name'),
                'subject' => $subjects->first(),
                'subject_options' => $subjects->all(),
                'content' => $request->input('content'),
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
            'content' => $request->input('content'),
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
            $tracker = MarketingEmailOpen::where('tracking_id', $trackingId)->first();

            if ($tracker) {
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
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function trackMarketingFollowupOpen(Request $request, string $trackingId)
    {
        try {
            $tracker = MarketingFollowupEmailOpen::where('tracking_id', $trackingId)->first();

            if ($tracker) {
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
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
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
        return [
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
