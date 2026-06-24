<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProgramController extends Controller
{
    public function index(string $locale): View
    {
        $programs = Cache::remember('programs_all', 3600, function () {
            return Program::where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });

        return view('programs.index', compact('programs'));
    }
}
