# تثبيت مشروع ساهم - Laravel
Write-Host "=== Sahem Laravel Installer ===" -ForegroundColor Green

$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
    Write-Host "PHP غير مثبت. ثبّت Laragon من https://laragon.org ثم أعد تشغيل الطرفية." -ForegroundColor Red
    exit 1
}

$composer = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composer) {
    Write-Host "Composer غير مثبت. ثبّته من https://getcomposer.org" -ForegroundColor Red
    exit 1
}

Set-Location $PSScriptRoot

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}

if (-not (Test-Path database\database.sqlite)) {
    New-Item -ItemType File -Path database\database.sqlite | Out-Null
}

composer install --no-interaction
php artisan key:generate --force
php artisan migrate --force --seed
php artisan storage:link --force

Write-Host ""
Write-Host "تم التثبيت بنجاح!" -ForegroundColor Green
Write-Host "الموقع:      php artisan serve  -> http://127.0.0.1:8000/ar"
Write-Host "لوحة التحكم: http://127.0.0.1:8000/admin"
Write-Host "البريد:      admin@sahem.org"
Write-Host "كلمة المرور: password"
Write-Host ""
