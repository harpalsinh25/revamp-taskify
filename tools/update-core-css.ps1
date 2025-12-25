param(
  [string]$Url = 'https://demos.themeselection.com/sneat-bootstrap-html-laravel-admin-template/demo/build/assets/core-CsLdeUI9.css',
  [string]$CorePath = 'public/assets/vendor/css/core.css',
  [string]$BackupPath = 'public/assets/vendor/css/core.backup.20251104.css',
  [string]$DateStamp = '2025-11-04'
)

Write-Host "Updating core.css from upstream..."

# Ensure backup exists
if (-not (Test-Path $BackupPath)) {
  Copy-Item -Path $CorePath -Destination $BackupPath -Force
  Write-Host "Backup created at $BackupPath"
} else {
  Write-Host "Backup already exists at $BackupPath"
}

$upstream = (Invoke-WebRequest -UseBasicParsing $Url).Content

# Preserve any Laravel-specific @imports from the original (from backup)
$firstLines = Get-Content $BackupPath -TotalCount 100
$importsMatches = $firstLines | Select-String -Pattern '^\s*@import'
$imports = ($importsMatches | ForEach-Object { $_.ToString() }) -join "`r`n"
if (-not $imports) { $imports = '' }

$header = "/* Aligned to Sneat demo (LTR + Dark). Source: $Url | Date: $DateStamp */"
if ($imports -ne '') {
  $content = $header + "`r`n" + $imports + "`r`n" + $upstream
} else {
  $content = $header + "`r`n" + $upstream
}

Set-Content -Path $CorePath -Value $content -Encoding UTF8

Write-Host "core.css updated successfully."

