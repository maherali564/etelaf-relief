<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('projects.index', [
            'projects' => Project::active()->paginate(12),
        ]);
    }

    public function show(string $locale, string $slug): View
    {
        $project = Project::query()->where('slug', $slug)->active()->firstOrFail();

        return view('projects.show', compact('project'));
    }
}
