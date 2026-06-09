<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

config(['app.debug' => true]);
app(ExceptionHandler::class);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;

try {
    $request = Request::create('/en/donor/login', 'GET');
    $response = app()->handle($request);
    echo 'Status: '.$response->getStatusCode()."\n\n";
    $content = $response->getContent();
    // Find error message in content
    if (preg_match('/<div[^>]*class="[^"]*error[^"]*"[^>]*>(.*?)<\/div>/si', $content, $m)) {
        echo 'Error div: '.strip_tags($m[1])."\n";
    }
    if (preg_match('/throwable|exception|error|Warning|Fatal/i', $content, $m, PREG_OFFSET_CAPTURE)) {
        echo 'Error near: ...'.substr($content, max(0, $m[0][1] - 100), 300)."...\n";
    } else {
        echo 'Content (500 chars): '.substr($content, 0, 500)."\n";
    }
} catch (Throwable $e) {
    echo 'Exception: '.get_class($e).': '.$e->getMessage()."\n";
    echo 'File: '.$e->getFile().':'.$e->getLine()."\n";
}
