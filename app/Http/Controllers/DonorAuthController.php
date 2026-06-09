<?php

namespace App\Http\Controllers;

use App\Http\Requests\DonorLoginRequest;
use App\Http\Requests\DonorRegisterRequest;
use App\Models\Donation;
use App\Models\Donor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DonorAuthController extends Controller
{
    public function showRegister()
    {
        return view('donor.register', ['currentLocale' => app()->getLocale()]);
    }

    public function register(DonorRegisterRequest $request)
    {
        $donor = Donor::create([
            ...$request->validated(),
            'password' => Hash::make($request->input('password')),
        ]);

        Donation::where('email', $donor->email)->whereNull('donor_id')->update(['donor_id' => $donor->id]);

        Auth::guard('donor')->login($donor);

        return redirect()->route('donor.dashboard', ['locale' => app()->getLocale()]);
    }

    public function showLogin()
    {
        return view('donor.login', ['currentLocale' => app()->getLocale()]);
    }

    public function login(DonorLoginRequest $request)
    {
        if (Auth::guard('donor')->attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return redirect()->intended(
                route('donor.dashboard', ['locale' => app()->getLocale()])
            );
        }

        return back()->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => __('donor.login_error')]);
    }

    public function logout()
    {
        Auth::guard('donor')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home', ['locale' => app()->getLocale()]);
    }

    public function dashboard()
    {
        $donor = Auth::guard('donor')->user();

        return view('donor.dashboard', [
            'donor' => $donor,
            'donations' => $donor->donations()->latest()->paginate(20),
            'totalDonated' => $donor->total_donated,
            'donationCount' => $donor->donation_count,
            'currentLocale' => app()->getLocale(),
        ]);
    }
}
