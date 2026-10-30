param([string]$MongoBin = 'C:\Program Files\MongoDB\Server\8.2\bin\mongod.exe')
$ErrorActionPreference = 'Stop'
$salesRoot = Split-Path -Parent $PSScriptRoot
if (-not (Get-Command mongosh -ErrorAction SilentlyContinue)) { throw 'Install mongosh before starting the local replica set.' }

$salesListener = Get-NetTCPConnection -LocalPort 27018 -State Listen -ErrorAction SilentlyContinue
if (-not $salesListener) {
    if (-not (Test-Path -LiteralPath $MongoBin)) { throw 'MongoDB binary missing. Supply -MongoBin with the installed mongod.exe path.' }
    $salesRuntime = Join-Path $salesRoot '.runtime'
    $salesData = Join-Path $salesRuntime 'mongo-data'
    New-Item -ItemType Directory -Path $salesData -Force | Out-Null
    $salesLog = Join-Path $salesRuntime 'mongod.log'
    $salesArguments = @('--dbpath', ('"' + $salesData + '"'), '--port', '27018', '--bind_ip', '127.0.0.1', '--replSet', 'tienRS', '--logpath', ('"' + $salesLog + '"'))
    $salesProcess = Start-Process -FilePath $MongoBin -ArgumentList $salesArguments -WindowStyle Hidden -PassThru
    Set-Content -LiteralPath (Join-Path $salesRuntime 'mongod.pid') -Value $salesProcess.Id
}

for ($salesAttempt = 0; $salesAttempt -lt 20; $salesAttempt++) {
    # Only initialize an uninitialized local node; never reconfigure an existing server.
    $salesProbe = & mongosh 'mongodb://127.0.0.1:27018/?directConnection=true&serverSelectionTimeoutMS=1000' --quiet --eval 'const h=db.hello(); if(h.setName==="tienRS" && h.isWritablePrimary) { print("READY"); } else if(h.setName && h.setName!=="tienRS") { print("WRONG_SET"); } else if(h.isreplicaset===true) { rs.initiate({_id:"tienRS",members:[{_id:0,host:"127.0.0.1:27018"}]}); } else if(!h.setName) { print("STANDALONE"); }' 2>&1
    if ($salesProbe -match 'READY') { Write-Output 'PASS: local replica set tienRS is primary on 127.0.0.1:27018. No migrations, seeds, drops or changes to port 27017.'; exit 0 }
    if ($salesProbe -match 'WRONG_SET|STANDALONE') { throw 'Port 27018 belongs to a different MongoDB configuration. No reconfiguration was attempted.' }
    Start-Sleep -Milliseconds 250
}
throw 'Local replica set is not ready. Inspect .runtime/mongod.log; existing data has not been reset.'
