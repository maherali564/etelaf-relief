<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\DonationService;
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

        $data = app(DonationService::class)->loadDonationPageData(projectId: $project->id);

        $similar = Project::active()->where('id', '!=', $project->id)->limit(3)->get();

        return view('projects.show', ['project' => $project, 'similar' => $similar, ...$data]);
    }
}
