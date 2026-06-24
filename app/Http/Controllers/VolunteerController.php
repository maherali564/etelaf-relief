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
        $data = $request->validated();
        $volunteer = new Volunteer();
        $volunteer->fill($data);
        $volunteer->volunteer_opportunity_id = $data['volunteer_opportunity_id'] ?? null;
        $volunteer->locale = app()->getLocale();
        $volunteer->status = 'pending';
        $volunteer->save();

        session()->put('volunteer_id', $volunteer->id);

        return back()->with('success', __('common.volunteer_success'));
    }

    public function dashboard(Request $request): View
    {
        $volunteerId = session('volunteer_id');
        $volunteer = null;

        if ($volunteerId) {
            $volunteer = Volunteer::with('tasks')->find($volunteerId);
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
