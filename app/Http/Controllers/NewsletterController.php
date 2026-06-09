<?php

namespace App\Http\Controllers;

use App\Http\Requests\NewsletterStoreRequest;
use App\Mail\NewsletterVerification;
use App\Models\Newsletter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function store(NewsletterStoreRequest $request): RedirectResponse
    {
        $subscriber = Newsletter::create([
            ...$request->validated(),
            'verify_token' => Str::random(64),
            'is_subscribed' => false,
            'subscribed_at' => null,
        ]);

        try {
            Mail::to($subscriber->email)->send(new NewsletterVerification($subscriber));
        } catch (\Exception $e) {
            $subscriber->delete();

            return back()->withErrors(['email' => __('common.newsletter_send_error')]);
        }

        return back()->with('success', __('common.newsletter_verify_sent'));
    }

    public function verify(string $locale, string $token): View|RedirectResponse
    {
        $subscriber = Newsletter::where('verify_token', $token)->whereNull('verified_at')->first();

        if (! $subscriber) {
            return redirect()->route('home', ['locale' => $locale])
                ->with('error', __('common.newsletter_verify_invalid'));
        }

        $subscriber->update([
            'verify_token' => null,
            'verified_at' => now(),
            'is_subscribed' => true,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('home', ['locale' => $locale])
            ->with('success', __('common.newsletter_verify_success'));
    }
}
