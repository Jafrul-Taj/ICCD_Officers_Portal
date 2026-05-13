# ============================================================
# download-assets.ps1
# Downloads all CDN assets required by the ICCD Officer's Portal
# so the project runs completely offline.
#
# Run once from the project root:
#   cd "C:\xampp\htdocs\ICCD_Officer's_Portal"
#   .\download-assets.ps1
# ============================================================

$ErrorActionPreference = 'Stop'

# ── Helper ──────────────────────────────────────────────────
function Download-File {
    param([string]$Url, [string]$Dest)
    $dir = Split-Path $Dest
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Force $dir | Out-Null }
    if (Test-Path $Dest) {
        Write-Host "  [skip] $Dest already exists" -ForegroundColor DarkGray
        return
    }
    Write-Host "  Downloading $(Split-Path $Dest -Leaf) ..." -ForegroundColor Cyan
    Invoke-WebRequest -Uri $Url -OutFile $Dest -UseBasicParsing
    Write-Host "  [ok]   $Dest" -ForegroundColor Green
}

# ── CSS ─────────────────────────────────────────────────────
Write-Host "`n[1/4] Bootstrap CSS" -ForegroundColor Yellow
Download-File `
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" `
    "css\bootstrap.min.css"

Write-Host "`n[2/4] Bootstrap Icons CSS" -ForegroundColor Yellow
Download-File `
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" `
    "css\bootstrap-icons.min.css"

# ── Bootstrap Icons font files (referenced inside the CSS) ──
Write-Host "`n[3/4] Bootstrap Icons font files" -ForegroundColor Yellow

# bootstrap-icons.min.css uses url("fonts/bootstrap-icons.woff2")
# — a path relative to the CSS file itself, so fonts must live at css\fonts\
Download-File `
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff2" `
    "css\fonts\bootstrap-icons.woff2"

Download-File `
    "https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/fonts/bootstrap-icons.woff" `
    "css\fonts\bootstrap-icons.woff"

# ── JavaScript ──────────────────────────────────────────────
Write-Host "`n[4/4] JavaScript files" -ForegroundColor Yellow
Download-File `
    "https://code.jquery.com/jquery-3.7.1.min.js" `
    "js\jquery.min.js"

Download-File `
    "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" `
    "js\bootstrap.bundle.min.js"

# ── Patch font path in the local CSS ────────────────────────
# The CDN CSS uses:  url("./fonts/bootstrap-icons.woff2")
# which already resolves correctly relative to css\ — no patch needed
# as long as your fonts\ folder sits next to css\ at the project root.

Write-Host "`nAll assets downloaded successfully." -ForegroundColor Green
Write-Host "Final layout expected:" -ForegroundColor White
Write-Host "  css\bootstrap.min.css"
Write-Host "  css\bootstrap-icons.min.css"
Write-Host "  css\fonts\bootstrap-icons.woff2"
Write-Host "  css\fonts\bootstrap-icons.woff"
Write-Host "  js\jquery.min.js"
Write-Host "  js\bootstrap.bundle.min.js"
