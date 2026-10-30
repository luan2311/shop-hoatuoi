param([string]$MongoBin, [int]$Port = 27028)
$ErrorActionPreference = 'Stop'
$qaRoot = Split-Path -Parent $PSScriptRoot
$qaRuntime = Join-Path $qaRoot '.runtime/dung-qa'
$qaData = Join-Path $qaRuntime 'data'
if (-not (Get-Command mongosh -ErrorAction SilentlyContinue)) { throw 'mongosh is required for the isolated replica set.' }
New-Item -ItemType Directory -Path $qaData -Force | Out-Null
$qaData = (Resolve-Path -LiteralPath $qaData).Path
$qaUri = "mongodb://127.0.0.1:$Port/?directConnection=true&serverSelectionTimeoutMS=1000"
function Read-QaServer {
    try {
        $qaRaw = & mongosh --norc --quiet $qaUri --eval 'const o=db.adminCommand({getCmdLineOpts:1}); const h=db.hello(); print(JSON.stringify({set:h.setName,primary:h.isWritablePrimary,path:o.parsed.storage.dbPath}));' 2>$null
        if ($LASTEXITCODE -ne 0) { return $null }
        return (($qaRaw | Where-Object { $_.Trim().StartsWith('{') } | Select-Object -Last 1) | ConvertFrom-Json)
    } catch { return $null }
}
$qaPortOpen = $false
$qaSocket = New-Object Net.Sockets.TcpClient
try { $qaSocket.Connect('127.0.0.1', $Port); $qaPortOpen = $true } catch { } finally { $qaSocket.Dispose() }
if ($qaPortOpen) {
    $qaExisting = Read-QaServer
    if (-not $qaExisting -or -not $qaExisting.path) { throw 'Existing listener could not be verified. No startup or initialization attempted.' }
    if (($qaExisting.set -and $qaExisting.set -ne 'dungQA') -or [IO.Path]::GetFullPath($qaExisting.path) -ne $qaData) { throw 'Port belongs to another instance. No reconfiguration attempted.' }
} else {
    if (-not $MongoBin) {
        $qaMongoCommand = Get-Command mongod -ErrorAction SilentlyContinue
        if ($qaMongoCommand) { $MongoBin = $qaMongoCommand.Source }
        else {
            $qaCandidate = Get-ChildItem -Path (Join-Path $env:ProgramFiles 'MongoDB/Server/*/bin/mongod.exe') -ErrorAction SilentlyContinue | Sort-Object FullName -Descending | Select-Object -First 1
            if ($qaCandidate) { $MongoBin = $qaCandidate.FullName }
        }
    }
    if (-not $MongoBin -or -not (Test-Path -LiteralPath $MongoBin)) { throw 'mongod not found. Supply -MongoBin with an installed binary.' }
    $qaLog = Join-Path $qaRuntime 'mongod.log'
    $qaProcess = Start-Process -FilePath $MongoBin -ArgumentList @('--dbpath', ('"' + $qaData + '"'), '--port', $Port, '--bind_ip', '127.0.0.1', '--replSet', 'dungQA', '--logpath', ('"' + $qaLog + '"')) -WindowStyle Hidden -PassThru
    Set-Content -LiteralPath (Join-Path $qaRuntime 'mongod.pid') -Value $qaProcess.Id
}
$qaInitialize = "const h=db.hello(); if(!h.setName&&h.isreplicaset===true)rs.initiate({_id:'dungQA',members:[{_id:0,host:'127.0.0.1:" + $Port + "'}]});"
for ($qaAttempt = 0; $qaAttempt -lt 40; $qaAttempt++) {
    $qaInfo = Read-QaServer
    if ($qaInfo) {
        if (-not $qaInfo.path -or [IO.Path]::GetFullPath($qaInfo.path) -ne $qaData -or ($qaInfo.set -and $qaInfo.set -ne 'dungQA')) { throw 'Listener ownership changed. No initialization attempted.' }
        if ($qaInfo.set -eq 'dungQA' -and $qaInfo.primary) { break }
        if (-not $qaInfo.set) { try { $null = & mongosh --norc --quiet $qaUri --eval $qaInitialize 2>$null } catch { } }
    }
    Start-Sleep -Milliseconds 250
}
$qaResult = & mongosh --norc --quiet $qaUri --eval "const h=db.hello(); if(h.setName!=='dungQA'||!h.isWritablePrimary)quit(2); print('PASS: dungQA writable primary');" 2>$null
if ($LASTEXITCODE -ne 0) { throw 'QA replica set not ready. Inspect .runtime/dung-qa/mongod.log.' }
Write-Output ($qaResult | Where-Object { $_ -match '^PASS:' })
Write-Output "MONGODB_URI=mongodb://127.0.0.1:$Port/?replicaSet=dungQA"
Write-Output 'Isolated instance only. No migrations or seeds executed.'
