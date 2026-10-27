param(
    [string]$MongoUri = 'mongodb://127.0.0.1:27028/?replicaSet=dungQA&serverSelectionTimeoutMS=1500',
    [string]$Filter = 'FlowerDatasetTest|SeederIntegrityTest|SeededSalesIntegrationTest|ProductImageLibraryTest',
    [string]$EvidencePath = '.runtime/dung-qa/phpunit.xml'
)
$ErrorActionPreference = 'Stop'
$qaRoot = Split-Path -Parent $PSScriptRoot
Push-Location -LiteralPath $qaRoot
$qaNames = @('PHP_INI_SCAN_DIR','MONGODB_URI','APP_KEY')
$qaOriginal = @{}
foreach ($qaName in $qaNames) { $qaOriginal[$qaName] = [Environment]::GetEnvironmentVariable($qaName, 'Process') }
try {
    if ($MongoUri -notmatch '^mongodb://127\.0\.0\.1:27028/\?replicaSet=dungQA(?:&|$)') {
        throw 'QA chỉ chạy trên replica set dungQA tại 127.0.0.1:27028.'
    }
    if (Test-Path -LiteralPath .runtime/php/mongodb.ini) { $env:PHP_INI_SCAN_DIR = (Resolve-Path .runtime/php).Path }
    $env:MONGODB_URI = $MongoUri
    if (-not $env:APP_KEY) {
        $qaBytes = New-Object byte[] 32
        $qaRng = [Security.Cryptography.RandomNumberGenerator]::Create()
        $qaRng.GetBytes($qaBytes)
        $qaRng.Dispose()
        $env:APP_KEY = 'base64:' + [Convert]::ToBase64String($qaBytes)
    }
    if (-not (Test-Path -LiteralPath vendor/autoload.php)) { throw 'Install the locked Composer dependencies before running QA.' }
    $qaEvidence = [IO.Path]::GetFullPath((Join-Path $qaRoot $EvidencePath))
    if (-not $qaEvidence.StartsWith($qaRoot + [IO.Path]::DirectorySeparatorChar, [StringComparison]::OrdinalIgnoreCase)) { throw 'Evidence path must stay inside the repository.' }
    New-Item -ItemType Directory -Path (Split-Path -Parent $qaEvidence) -Force | Out-Null
    $qaConfigPath = Join-Path $qaRoot '.runtime/dung-qa/phpunit-config.xml'
    [xml]$qaConfig = Get-Content -LiteralPath (Join-Path $qaRoot 'phpunit.xml') -Raw
    $qaConfig.DocumentElement.SetAttribute('bootstrap', (Join-Path $qaRoot 'vendor/autoload.php'))
    foreach ($qaPathNode in $qaConfig.SelectNodes('/phpunit/testsuites/testsuite/directory | /phpunit/source/include/directory')) {
        $qaPathNode.InnerText = [IO.Path]::GetFullPath((Join-Path $qaRoot $qaPathNode.InnerText))
    }
    $qaConfig.SelectSingleNode('/phpunit/php/env[@name="MONGODB_URI"]').SetAttribute('value', $MongoUri)
    $qaConfig.SelectSingleNode('/phpunit/php/env[@name="MONGODB_DATABASE"]').SetAttribute('value', 'shop_hoatuoi_test_dung_suite')
    $qaConfig.Save($qaConfigPath)
    & php vendor/bin/phpunit --configuration $qaConfigPath --filter $Filter --log-junit $qaEvidence
    $qaExit = $LASTEXITCODE
    if ($qaExit -eq 0) {
        [xml]$qaXml = Get-Content -LiteralPath $qaEvidence -Raw
        $qaSkipped = [int]$qaXml.testsuites.testsuite.skipped
        if ($qaSkipped -gt 0) {
            Write-Output "BLOCKED: $qaSkipped test cases were skipped. This is not a full PASS."
            $qaExit = 2
        }
    }
} finally {
    foreach ($qaName in $qaNames) { [Environment]::SetEnvironmentVariable($qaName, $qaOriginal[$qaName], 'Process') }
    Pop-Location
}
exit $qaExit
