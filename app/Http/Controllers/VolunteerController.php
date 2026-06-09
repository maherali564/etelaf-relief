<?php

namespace App\Http\Controllers;

use App\Http\Requests\VolunteerStoreRequest;
use App\Models\Volunteer;
use App\Models\VolunteerOpportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    public function store(VolunteerStoreRequest $request): RedirectResponse
    {
        $volunteer = Volunteer::create([
            ...$request->validated(),
            'locale' => app()->getLocale(),
            'status' => 'pending',
        ]);

        session()->flash('volunteer_id', $volunteer->id);

        return back()->with('success', __('common.volunteer_success'));
    }

    public function dashboard(Request $request): View
    {
        $volunteerId = session('volunteer_id');
        $volunteer = null;

        if ($volunteerId) {
            $volunteer = Volunteer::with('tasks')->find($volunteerId);
        }

        if (! $volunteer && $request->has('ref')) {
            $volunteer = Volunteer::with('tasks')->find($request->query('ref'));
        }

        if (! $volunteer && $request->filled('email')) {
            $volunteer = Volunteer::where('email', $request->input('email'))
                ->with('tasks')
                ->latest()
                ->first();
        }

        $opportunities = VolunteerOpportunity::active()->get();

        return view('volunteer.dashboard', compact('volunteer', 'opportunities'));
    }

    public function register(): View
    {
        $opportunities = VolunteerOpportunity::active()->get();

        return view('volunteer.register', compact('opportunities'));
    }
}
