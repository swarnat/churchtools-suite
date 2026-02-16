# Auto-Create GitHub Releases via GitHub CLI
# Requires: gh CLI authenticated

Write-Host "=== Automatische GitHub Release Erstellung ===" -ForegroundColor Cyan
Write-Host ""

# Release 1: ChurchTools Suite v1.1.0.3
Write-Host "[1/3] Erstelle Release für ChurchTools Suite v1.1.0.3..." -ForegroundColor Yellow
cd "c:\Users\nauma\OneDrive\laragon\www\feg-clone\wp-content\plugins\churchtools-suite"

$notes1 = @"
## 🚨 Critical Bugfix Release

### CRITICAL FIXES
- ✅ **Fixed fatal error**: Image Helper class now loaded in dependency chain
- ✅ **Fixed Modern List template syntax errors**

### UI IMPROVEMENTS (Classic List)
- 🔍 **Calendar name**: max-width 180px with ellipsis for long names
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

gh release create "v1.1.0.3" `
    --repo "FEGAschaffenburg/churchtools-suite" `
    --title "v1.1.0.3 - Critical Bugfix + Classic List UI improvements" `
    --notes $notes1

if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Release v1.1.0.3 erfolgreich erstellt!" -ForegroundColor Green
} else {
    Write-Host "   ❌ Fehler beim Erstellen von v1.1.0.3" -ForegroundColor Red
}
Write-Host ""

# Release 2: ChurchTools Suite Demos v1.1.0.1
Write-Host "[2/3] Erstelle Release für ChurchTools Suite Demos v1.1.0.1..." -ForegroundColor Yellow
cd "c:\Users\nauma\OneDrive\laragon\www\plugin-homepage\wp-content\plugins\churchtools-suite-demo"

$notes2 = @"
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

gh release create "v1.1.0.1" `
    --repo "FEGAschaffenburg/churchtools-suite-demos" `
    --title "v1.1.0.1 - Demo Plugin" `
    --notes $notes2

if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Release v1.1.0.1 erfolgreich erstellt!" -ForegroundColor Green
} else {
    Write-Host "   ❌ Fehler beim Erstellen von v1.1.0.1" -ForegroundColor Red
}
Write-Host ""

# Release 3: ChurchTools Suite Elementor v0.6.0
Write-Host "[3/3] Erstelle Release für ChurchTools Suite Elementor v0.6.0..." -ForegroundColor Yellow
cd "c:\Users\nauma\OneDrive\laragon\www\feg-clone\wp-content\plugins\churchtools-suite-elementor"

$notes3 = @"
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

gh release create "v0.6.0" `
    --repo "FEGAschaffenburg/churchtools-suite-elementor" `
    --title "v0.6.0 - Critical Parse Error Fix" `
    --notes $notes3

if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Release v0.6.0 erfolgreich erstellt!" -ForegroundColor Green
} else {
    Write-Host "   ❌ Fehler beim Erstellen von v0.6.0" -ForegroundColor Red
}
Write-Host ""

Write-Host "=== Fertig! ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Nächste Schritte:" -ForegroundColor Yellow
Write-Host "  1. Gehe zu WordPress Admin → Plugins (wp-admin/plugins.php)" -ForegroundColor White
Write-Host "  2. Prüfe ob Updates angezeigt werden" -ForegroundColor White
Write-Host "  3. Falls nicht: Browser-Cache leeren (Strg+Shift+R)" -ForegroundColor White
Write-Host ""
