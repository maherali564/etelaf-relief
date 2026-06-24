<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;

Project::whereIn('slug', ['meat-distribution','collective-joy','water-well','winter-aid'])->delete();
echo "Deleted 4 seeded projects\n";

$p = Project::where('slug', 'project-altqnbmz')->first();
if ($p) {
    $p->update(['goal_amount' => 50000, 'raised_amount' => 12500]);
    echo "Updated مرحبا with goal_amount=50000, raised_amount=12500\n";
} else {
    echo "مرحبا not found\n";
}

$projects = Project::all(['id', 'slug', 'goal_amount', 'raised_amount']);
foreach ($projects as $proj) {
    echo "id={$proj->id} slug={$proj->slug} goal={$proj->goal_amount} raised={$proj->raised_amount}\n";
}
