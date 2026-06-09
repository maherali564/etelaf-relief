<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;

$tests = [
    'Home /' => ['GET', '/'],
    'Home EN' => ['GET', '/en'],
    'Home AR' => ['GET', '/ar'],
    'Home TR' => ['GET', '/tr'],
    'About' => ['GET', '/en/about'],
    'Projects' => ['GET', '/en/projects'],
    'Stories' => ['GET', '/en/stories'],
    'Success Stories' => ['GET', '/en/success-stories'],
    'News' => ['GET', '/en/news'],
    'Transparency' => ['GET', '/en/transparency'],
    'Donor Wall' => ['GET', '/en/donor-wall'],
    'Donor Login' => ['GET', '/en/donor/login'],
    'Donor Register' => ['GET', '/en/donor/register'],
    'Volunteer Dashboard' => ['GET', '/en/volunteer'],
    'Volunteer Register' => ['GET', '/en/volunteer/register'],
    'Donate' => ['GET', '/en/donate'],
    'Admin Login' => ['GET', '/admin/login'],
    '404 Test' => ['GET', '/en/nonexistent-page'],
];

$passed = 0;
$failed = 0;

foreach ($tests as $label => [$method, $uri]) {
    try {
        $request = Request::create($uri, $method);
        $response = app()->handle($request);
        $status = $response->getStatusCode();

        $isRedirect = in_array($status, [301, 302, 307, 308]);
        $expected404 = $uri === '/en/nonexistent-page' && $status === 404;
        $expectedOk = in_array($status, [200, 201]) && $uri !== '/en/nonexistent-page';
        $expectedRedirect = $isRedirect && $uri === '/en/donate';

        if ($expectedOk || $expected404 || $expectedRedirect) {
            echo "  ✅ {$status} {$label}\n";
            $passed++;
        } else {
            echo "  ❌ {$status} {$label}\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "  ❌ ERROR {$label}: ".$e->getMessage().' in '.$e->getFile().':'.$e->getLine()."\n";
        $failed++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "  ✅ Passed: {$passed}\n";
echo "  ❌ Failed: {$failed}\n";
echo '  Total: '.($passed + $failed)."\n";

exit($failed > 0 ? 1 : 0);
