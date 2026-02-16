#!/usr/bin/env pwsh
<#
.SYNOPSIS
    Erstellt GitHub Releases für alle ChurchTools Suite Plugins
    
.DESCRIPTION
    Öffnet die GitHub Release-Seiten und zeigt vorbereitete Release Notes
#>

$releases = @(
    @{
        Name = "ChurchTools Suite"
        Repo = "FEGAschaffenburg/churchtools-suite"
        Tag = "v1.1.0.3"
        Title = "v1.1.0.3 - Critical Bugfix + Classic List UI improvements"
        Notes = @"
## 🚨 Critical Bugfix Release

### CRITICAL FIXES
- ✅ **Fixed fatal error**: Image Helper class now loaded in dependency chain
- ✅ **Fixed Modern List template syntax errors**

### UI IMPROVEMENTS (Classic List)
- 📏 **Calendar name**: max-width 180px with ellipsis for long names
- 🕐 **Time display**: 2-line layout (start/end vertically)
- 📅 **Date box**: Conditional month display (hidden when separator active)
- 🔤 **Date box**: Responsive font sizing (9px uniform when no separator)

### MODERN LIST
- ⏸️ **Temporarily disabled** with development notice
- 💬 Shows user-friendly message: "Diese Ansicht wird noch entwickelt"

### FILES CHANGED
- ``includes/class-churchtools-suite.php`` (Image Helper loaded)
- ``assets/css/churchtools-suite-public.css`` (UI improvements)
- ``templates/views/event-list/modern.php`` (dev mode)
- ``churchtools-suite.php`` (version bump to 1.1.0.3)

**⚠️ Update recommended** - Fixes critical fatal error in image rendering
"@
    },
    @{
        Name = "ChurchTools Suite Demos"
        Repo = "FEGAschaffenburg/churchtools-suite-demos"
        Tag = "v1.1.0.1"
        Title = "v1.1.0.1 - Demo Plugin Update"
        Notes = @"
## 📦 Demo Plugin Release

### FEATURES
- ✅ Demo content for ChurchTools Suite
- ✅ Example configurations and templates
- ✅ Compatible with ChurchTools Suite v1.1.0+

### INSTALLATION
1. Install ChurchTools Suite first
2. Upload this plugin
3. Activate to see demo content

**Requires**: ChurchTools Suite v1.1.0 or higher
"@
    },
    @{
        Name = "ChurchTools Suite - Elementor Integration"
        Repo = "FEGAschaffenburg/churchtools-suite-elementor"
        Tag = "v0.6.0"
        Title = "v0.6.0 - Critical Parse Error Fix & Elementor Conditions"
        Notes = @"
## 🔧 Critical Fixes & New Features

### CRITICAL FIXES
- ✅ **Fixed fatal parse error** in integration class
- ✅ **Fixed widget registration** in Elementor editor

### NEW FEATURES
- 🎯 **Elementor Display Conditions** support
- 🎨 **28+ customization options** for event widgets
- 📅 **Multiple view modes**: List, Grid, Calendar

### COMPATIBILITY
- ✅ Elementor 3.0+
- ✅ ChurchTools Suite v1.1.0+
- ✅ WordPress 6.0+
- ✅ PHP 8.0+

**⚠️ Update recommended** - Fixes critical parse error
"@
    }
)

Write-Host "`n=== GitHub Release Creator für ChurchTools Suite Plugins ===`n" -ForegroundColor Cyan

foreach ($release in $releases) {
    Write-Host "📦 $($release.Name)" -ForegroundColor Green
    Write-Host "   Repository: $($release.Repo)" -ForegroundColor Gray
    Write-Host "   Tag: $($release.Tag)" -ForegroundColor Yellow
    Write-Host "   URL: https://github.com/$($release.Repo)/releases/new?tag=$($release.Tag)" -ForegroundColor Blue
    Write-Host ""
    
    Write-Host "   Release Title:" -ForegroundColor Magenta
    Write-Host "   $($release.Title)" -ForegroundColor White
    Write-Host ""
    
    Write-Host "   Release Notes (kopiere diesen Text):" -ForegroundColor Magenta
    Write-Host "   " -NoNewline
    Write-Host "─────────────────────────────────────────────────────" -ForegroundColor DarkGray
    Write-Host $release.Notes -ForegroundColor White
    Write-Host "   " -NoNewline
    Write-Host "─────────────────────────────────────────────────────" -ForegroundColor DarkGray
    Write-Host ""
    
    # Kopiere Release Notes in Zwischenablage für einfaches Einfügen
    $release.Notes | Set-Clipboard
    Write-Host "   ✅ Release Notes in Zwischenablage kopiert!" -ForegroundColor Green
    Write-Host ""
    
    # Frage ob Browser öffnen
    $open = Read-Host "   Browser öffnen für dieses Release? (j/n)"
    if ($open -eq 'j' -or $open -eq 'J' -or $open -eq 'y' -or $open -eq 'Y') {
        Start-Process "https://github.com/$($release.Repo)/releases/new?tag=$($release.Tag)"
        Write-Host "   ✅ Browser geöffnet" -ForegroundColor Green
        Write-Host ""
        Read-Host "   Drücke Enter wenn Release erstellt ist, um zum nächsten zu gehen"
    }
    
    Write-Host "`n───────────────────────────────────────────────────────────────`n" -ForegroundColor DarkGray
}

Write-Host "`n✅ Alle Releases vorbereitet!" -ForegroundColor Green
Write-Host "`nWICHTIG: Nach dem Erstellen der Releases:" -ForegroundColor Yellow
Write-Host "  1. Gehe zu WordPress Admin → Plugins" -ForegroundColor White
Write-Host "  2. Prüfe ob Updates angezeigt werden" -ForegroundColor White
Write-Host "  3. Falls nicht: Cache leeren (Strg+Shift+R)" -ForegroundColor White
Write-Host ""
