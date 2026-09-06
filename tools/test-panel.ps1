[CmdletBinding()]
param(
    [ValidateSet('setup', 'start', 'stop', 'logs', 'refresh', 'reset')]
    [string]$Command = 'setup'
)

$ErrorActionPreference = 'Stop'
$Root = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$Runtime = Join-Path $Root '.test-panel'
$ComposeFile = Join-Path $Root 'tools/test-panel/docker-compose.yml'
$ComposeProject = 'minecraft-tools-test-panel'
$env:TEST_PANEL_DIR = './.test-panel'
$env:PANEL_PORT = if ($env:PANEL_PORT) { $env:PANEL_PORT } else { '8080' }

function Invoke-Compose([string[]]$Arguments) {
    & docker compose -p $ComposeProject --project-directory $Root -f $ComposeFile @Arguments
    if ($LASTEXITCODE -ne 0) { throw "Docker Compose failed with exit code $LASTEXITCODE." }
}

function Assert-Tools {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker Desktop is required. Install it from https://www.docker.com/products/docker-desktop/.'
    }
    docker compose version | Out-Host
    docker info *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker is installed but its engine is not running. Start Docker Desktop and run this command again.'
    }
}

function Sync-Extension {
    $extension = Join-Path $Runtime 'extensions/minecraft-tools'
    New-Item -ItemType Directory -Force -Path $extension, (Join-Path $Runtime 'logs') | Out-Null
    Get-ChildItem -Path $Root -Force | Where-Object { $_.Name -notin @('.git', '.test-panel', 'node_modules', 'vendor', 'dist') } | ForEach-Object {
        Copy-Item $_.FullName $extension -Recurse -Force
    }
}

switch ($Command) {
    'setup' {
        Assert-Tools
        Sync-Extension
        Invoke-Compose @('up', '-d')
        Invoke-Compose @('exec', '-T', '-w', '/blueprint_extensions/minecraft-tools', 'panel', 'blueprint', '-build')
        Write-Host "Test panel: http://localhost:$($env:PANEL_PORT)"
        Write-Host 'Create an admin user with: docker compose exec panel php artisan p:user:make'
    }
    'start' { Assert-Tools; Invoke-Compose @('up', '-d'); Write-Host "Test panel: http://localhost:$($env:PANEL_PORT)" }
    'stop' { Assert-Tools; Invoke-Compose @('down') }
    'logs' { Assert-Tools; Invoke-Compose @('logs', '-f', 'panel') }
    'refresh' {
        Assert-Tools
        Sync-Extension
        Invoke-Compose @('exec', '-T', '-w', '/blueprint_extensions/minecraft-tools', 'panel', 'blueprint', '-build')
    }
    'reset' {
        Assert-Tools
        Invoke-Compose @('down', '-v')
        if (Test-Path $Runtime) { Remove-Item $Runtime -Recurse -Force }
        Write-Host 'Test panel data and containers removed.'
    }
}
