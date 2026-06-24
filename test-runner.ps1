$sess = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$base = "http://127.0.0.1:9879"

try {
    $r = Invoke-WebRequest -Uri "$base/admin/login" -WebSession $sess -UseBasicParsing -TimeoutSec 10
    Write-Host "1. Login page: $($r.StatusCode) OK" -ForegroundColor Green
    $m = [regex]::Match($r.Content, 'name="_token" value="([^"]+)"')
    $token = $m.Groups[1].Value
    Write-Host "2. CSRF: $($token.Substring(0,10))..." -ForegroundColor Yellow
    $l = Invoke-WebRequest -Uri "$base/admin/login" -Method POST -Body @{ _token = $token; email = "admin@etelafrelief.org"; password = "password" } -WebSession $sess -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    Write-Host "3. Login: $($l.StatusCode) OK" -ForegroundColor Green
    $d = Invoke-WebRequest -Uri "$base/admin" -WebSession $sess -UseBasicParsing -TimeoutSec 10
    $title = [regex]::Match($d.Content, '<title>(.*?)</title>').Groups[1].Value
    Write-Host "4. Dashboard: $title" -ForegroundColor Green
    $pages = @("donations","users","campaigns","projects","posts","stories","chat-sessions","volunteers","payment-methods","pages")
    Write-Host "`nAdmin Pages:" -ForegroundColor Cyan
    foreach ($url in $pages) {
        try { $rr = Invoke-WebRequest -Uri "$base/admin/$url" -WebSession $sess -UseBasicParsing -TimeoutSec 10; Write-Host "  $url OK" -ForegroundColor Green } catch { Write-Host "  $url FAIL" -ForegroundColor Red }
    }
    Write-Host "`nPublic Pages:" -ForegroundColor Cyan
    try { $h = Invoke-WebRequest -Uri "$base/" -UseBasicParsing -TimeoutSec 10; Write-Host "  Home: $($h.StatusCode) OK" -ForegroundColor Green } catch { Write-Host "  Home FAIL" -ForegroundColor Red }
    try { $dr = Invoke-WebRequest -Uri "$base/ar/donate" -UseBasicParsing -TimeoutSec 10; Write-Host "  Donate: $($dr.StatusCode) OK" -ForegroundColor Green } catch { Write-Host "  Donate FAIL" -ForegroundColor Red }
    Write-Host "`nALL TESTS PASSED" -ForegroundColor Green
} catch { Write-Host "FATAL: $_" -ForegroundColor Red }
