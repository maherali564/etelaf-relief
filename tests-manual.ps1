$base = "http://localhost:9877"
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

Write-Host "=== Admin Panel Manual Tests ===" -ForegroundColor Cyan

# 1. Login page
Write-Host "`n1. Login page..." -NoNewline
try {
    $login = Invoke-WebRequest -Uri "$base/admin/login" -UseBasicParsing -WebSession $session
    if ($login.StatusCode -eq 200) { Write-Host " OK" -ForegroundColor Green }
    else { Write-Host " FAIL $($login.StatusCode)" -ForegroundColor Red }
} catch { Write-Host " FAIL" -ForegroundColor Red }

# 2. CSRF token
$pattern = 'name="_token" value="([^"]+)"'
$match = [regex]::Match($login.Content, $pattern)
$token = $match.Groups[1].Value
Write-Host "2. CSRF: $($token.Substring(0, 15))..." -ForegroundColor Yellow

# 3. Login
Write-Host "3. Admin login..." -NoNewline
try {
    $body = @{_token=$token; email="admin@etelafrelief.org"; password="password"; remember="on"}
    $loginPost = Invoke-WebRequest -Uri "$base/admin/login" -Method POST -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    if ($loginPost.StatusCode -eq 302) { Write-Host " OK" -ForegroundColor Green }
    else { Write-Host " $($loginPost.StatusCode)" -ForegroundColor Red }
} catch { Write-Host " FAIL" -ForegroundColor Red }

# 4. Dashboard
Write-Host "4. Dashboard..." -NoNewline
try {
    $dash = Invoke-WebRequest -Uri "$base/admin" -WebSession $session -UseBasicParsing
    if ($dash.StatusCode -eq 200) {
        $title = [regex]::Match($dash.Content, '<title>([^<]+)</title>').Groups[1].Value
        Write-Host " OK - $title" -ForegroundColor Green
    } else { Write-Host " $($dash.StatusCode)" -ForegroundColor Red }
} catch { Write-Host " FAIL" -ForegroundColor Red }

# 5. Public pages
Write-Host "`n=== Public Pages ===" -ForegroundColor Cyan
$urls = @("/ar","/en","/ar/donate","/ar/projects","/ar/about","/ar/contact","/ar/donor/login","/ar/volunteer/register","/ar/donor-wall","/ar/faq","/ar/transparency")
foreach ($u in $urls) {
    Write-Host "  $u..." -NoNewline
    try {
        $r = Invoke-WebRequest -Uri "$base$u" -UseBasicParsing -TimeoutSec 10
        if ($r.StatusCode -eq 200) { Write-Host " OK" -ForegroundColor Green }
        else { Write-Host " $($r.StatusCode)" -ForegroundColor Red }
    } catch { Write-Host " Error" -ForegroundColor Red }
}

# 6. Admin pages
Write-Host "`n=== Admin Pages ===" -ForegroundColor Cyan
$adminUrls = @("/admin/donations","/admin/users","/admin/campaigns","/admin/projects","/admin/posts","/admin/volunteers","/admin/payment-methods","/admin/faqs","/admin/chat-sessions","/admin/crypto-transactions","/admin/roles")
foreach ($u in $adminUrls) {
    Write-Host "  $u..." -NoNewline
    try {
        $r = Invoke-WebRequest -Uri "$base$u" -WebSession $session -UseBasicParsing -TimeoutSec 10
        if ($r.StatusCode -eq 200) { Write-Host " OK" -ForegroundColor Green }
        elseif ($r.StatusCode -eq 302) { Write-Host " Redirect" -ForegroundColor Yellow }
        elseif ($r.StatusCode -eq 403) { Write-Host " Forbidden" -ForegroundColor Yellow }
        elseif ($r.StatusCode -eq 404) { Write-Host " Not Found" -ForegroundColor Red }
        elseif ($r.StatusCode -eq 500) { Write-Host " ERROR 500" -ForegroundColor Red }
        else { Write-Host " $($r.StatusCode)" -ForegroundColor Yellow }
    } catch { Write-Host " Error" -ForegroundColor Red }
}

# 7. Throttle test (5 fast failed attempts)
Write-Host "`n=== Throttle Test ===" -ForegroundColor Cyan
$throttled = $false
for ($i = 1; $i -le 6; $i++) {
    try {
        $badBody = @{_token=$token; email="wrong@test.com"; password="badpass"}
        $bad = Invoke-WebRequest -Uri "$base/admin/login" -Method POST -Body $badBody -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
    } catch { if ($_.Exception.Response.StatusCode -eq 429) { $throttled = $true; break } }
}
if ($throttled) { Write-Host "  Throttle OK (429 received)" -ForegroundColor Green }
else { Write-Host "  No throttle detected" -ForegroundColor Yellow }

Write-Host "`n=== All Tests Complete ===" -ForegroundColor Cyan
