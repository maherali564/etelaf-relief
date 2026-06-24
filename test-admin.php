<?php
// Admin panel manual test using Laravel's internal HTTP testing
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    // 1. Test login page loads
    $loginResp = $this->get('/admin/login');
    echo "1. Login page: ".$loginResp->status()." OK\n";
    
    // 2. Test authentication
    $loginPost = $this->post('/admin/login', [
        'email' => 'admin@etelafrelief.org',
        'password' => 'password',
    ]);
    echo "2. Login POST: ".$loginPost->status()."\n";
    
} catch (\Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
}
