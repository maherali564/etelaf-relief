<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactStoreRequest;
use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function store(ContactStoreRequest $request): RedirectResponse
    {
        ContactSubmission::create([
            ...$request->validated(),
            'locale' => app()->getLocale(),
        ]);

        return back()->with('success', __('site.contact_success'));
    }
}
