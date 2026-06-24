<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Models\MarketingEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MainController extends Controller
{
    public function contactus(ContactRequest $request){

        $contact = new Contact;
        $contact->firstname = $request->firstname;
        $contact->lastname = $request->lastname;
        $contact->email = $request->email;
        $contact->budget = $request->budget;
        $contact->details = $request->details;

        if( $contact->save() ){
            return response()->json(['msg'=>'success']);
        }else{
            return response()->json(['msg'=>'error']);
        }


    }

    public function aboutme(){
        return view('aboutme');
    }
    
    
    public function getcontacts(){
        $contacts = Contact::all();
        return json_encode(['msg'=>'success', 'data'=>$contacts]);
    }

    public function marketing(Request $request)
    {
        $activeTab = $request->query('tab', 'dashboard');

        if (! in_array($activeTab, ['dashboard', 'send', 'contacts'], true)) {
            $activeTab = 'dashboard';
        }

        $databaseReady = true;

        try {
            $marketingEmails = MarketingEmail::latest('sent_at')->get();
            $contacts = $marketingEmails
                ->pluck('recipients')
                ->flatten()
                ->filter()
                ->unique()
                ->sort()
                ->values();
        } catch (\Throwable $exception) {
            $databaseReady = false;
            $marketingEmails = collect();
            $contacts = collect();
        }

        return view('marketing', [
            'activeTab' => $activeTab,
            'databaseReady' => $databaseReady,
            'emailsSent' => $marketingEmails->sum('recipient_count'),
            'contactsCount' => $contacts->count(),
            'contacts' => $contacts,
            'recentEmails' => $marketingEmails->take(5),
        ]);
    }

    public function sendMarketingEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => ['required', 'string'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
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
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('marketing_error', 'Marketing storage is not ready yet. Please run the migration after your database credentials are configured.');
        }

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('marketing-attachments');
            $attachmentName = $request->file('attachment')->getClientOriginalName();
        }

        try {
            foreach ($recipients as $recipient) {
                Mail::raw($request->input('content'), function ($message) use ($recipient, $request, $attachmentPath, $attachmentName) {
                    $message->to($recipient)
                        ->subject($request->input('subject'));

                    if ($attachmentPath) {
                        $message->attach(Storage::path($attachmentPath), ['as' => $attachmentName]);
                    }
                });
            }
        } catch (\Throwable $exception) {
            if ($attachmentPath) {
                Storage::delete($attachmentPath);
            }

            return back()
                ->withInput()
                ->with('marketing_error', 'Email could not be sent. Please check your mail settings and try again.');
        }

        MarketingEmail::create([
            'recipients' => $recipients->all(),
            'recipient_count' => $recipients->count(),
            'subject' => $request->input('subject'),
            'body' => $request->input('content'),
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'sent_at' => now(),
        ]);

        return redirect()
            ->route('marketing', ['tab' => 'dashboard'])
            ->with('marketing_success', 'Email sent to '.$recipients->count().' contact'.($recipients->count() === 1 ? '.' : 's.'));
    }
}
