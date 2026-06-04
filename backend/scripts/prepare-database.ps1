param(
    [string] $Database = "spydar_erp_dev",
    [string] $Username = "postgres",
    [string] $Password = "secret_pass",
    [string] $HostName = "127.0.0.1",
    [int] $Port = 5432
)

$ErrorActionPreference = "Stop"

if (-not (Get-Command psql -ErrorAction SilentlyContinue)) {
    throw "psql was not found. Install PostgreSQL separately, then rerun this script."
}

$env:PGPASSWORD = $Password

try {
    $databaseExists = & psql -U $Username -h $HostName -p $Port -tAc "SELECT 1 FROM pg_database WHERE datname = '$Database';"
    if ($databaseExists.Trim() -ne "1") {
        & psql -U $Username -h $HostName -p $Port -c "CREATE DATABASE $Database;"
    }

    $envPath = Join-Path $PSScriptRoot "..\.env"
    $examplePath = Join-Path $PSScriptRoot "..\.env.example"

    if (-not (Test-Path $envPath)) {
        Copy-Item $examplePath $envPath
    }

    $envContent = Get-Content $envPath -Raw
    $dbBlock = @"
DB_CONNECTION=pgsql
DB_HOST=$HostName
DB_PORT=$Port
DB_DATABASE=$Database
DB_USERNAME=$Username
DB_PASSWORD=$Password
"@

    $regex = "(?ms)DB_CONNECTION=.*?DB_PASSWORD=.*?\r?\n"
    if ($envContent -match $regex) {
        $envContent = $envContent -replace $regex, ($dbBlock + "`r`n")
    } else {
        $envContent += "`r`n" + $dbBlock + "`r`n"
    }

    Set-Content -Path $envPath -Value $envContent

    Write-Host "Database '$Database' is prepared and backend .env is configured." -ForegroundColor Green
} finally {
    $env:PGPASSWORD = $null
}
