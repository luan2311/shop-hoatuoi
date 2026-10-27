param([int]$Port = 8000, [string]$Database, [switch]$CheckOnly)
$ErrorActionPreference = 'Stop'
$salesRoot = Split-Path -Parent $PSScriptRoot
Set-Location -LiteralPath $salesRoot
$salesIni = Join-Path $salesRoot '.runtime\php'
if (Test-Path -LiteralPath (Join-Path $salesIni 'mongodb.ini')) { $env:PHP_INI_SCAN_DIR = $salesIni }
if ($Database) {
    if ($Database -ne 'shop_hoatuoi' -and $Database -notmatch '^shop_hoatuoi_test_[a-zA-Z0-9_]+$') { throw 'Use shop_hoatuoi or an explicitly named shop_hoatuoi_test_* database.' }
    $env:MONGODB_DATABASE = $Database
}
& php scripts/sales-preflight.php
if ($LASTEXITCODE -ne 0) { throw 'Sales preflight failed; no migration or seeder was executed.' }
if (-not $CheckOnly) { & php artisan serve --host=127.0.0.1 --port=$Port --no-reload }
