<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmationStoreRequest;
use App\Models\Donation;
use App\Services\ConfirmationService;
use Illuminate\Http\Request;

class ConfirmationController extends Controller
{
    public function __construct(
        private readonly ConfirmationService $confirmationService
    ) {}

    public function create(Request $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        $driver = $this->confirmationService->validateGateway($donation);
        if (! $driver) {
            abort(404);
        }

        $data = $this->confirmationService->loadConfirmationPage($donation);

        return view('payment.confirm', array_merge(
            compact('donation', 'driver'),
            $data
        ));
    }

    public function store(ConfirmationStoreRequest $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        $driver = $this->confirmationService->validateStoreGateway($donation);
        if (! $driver) {
            abort(404);
        }

        $this->confirmationService->submitConfirmation($donation, $request->validated(), $request);

        return redirect()->route('payment.success', [
            'locale' => $donation->locale,
            'donation' => $donation->id,
            'token' => $donation->idempotency_key,
        ])->with('success', __('common.confirmation_received'));
    }
}
