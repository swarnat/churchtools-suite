# ChurchTools Suite - Addons

## Übersicht

ChurchTools Suite kann mit Addons erweitert werden. Addons sind eigenständige Plugins, die zusätzliche Funktionalität bereitstellen.

## 📋 Verfügbare Addons

### ⚡ Elementor Integration (v0.5.3)

**Status:** ✅ Verfügbar  
**GitHub:** https://github.com/FEGAschaffenburg/churchtools-suite-elementor  
**Download:** https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases/latest

#### Features

- **ChurchTools Events Widget** für Elementor Page Builder
- **28+ Kontrollparameter** in 6 Kategorien
  - Content (Kalender, Tags, Zeitrahmen, Limit)
  - Filters (Services, Past Events)
  - Display (Beschreibungen, Location, Tags, Services)
  - Grid (Columns, Gap, Alignment)
  - Style (Colors, Typography, Spacing)
  - Advanced (CSS, Wrapper)
- **3 View-Typen:**
  - List (4 Templates: classic, classic-with-images, minimal, modern)
  - Grid (2 Templates: simple, modern)
  - Calendar (monthly)
- **Live-Preview** im Elementor Editor
- **Shortcode-Wrapper** Architektur (re-use Main Plugin)
- **Dependency Checks** (ChurchTools Suite, Elementor)

#### Installation

##### One-Click Installation (empfohlen)
1. WordPress Admin → **ChurchTools → Addons**
2. Klicke auf **"⚡ Jetzt installieren"**
3. Plugin wird automatisch heruntergeladen, installiert und aktiviert

##### Manuelle Installation
1. [Download v0.5.3](https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases/download/v0.5.3/churchtools-suite-elementor-0.5.3.zip)
2. WordPress Admin → Plugins → Installieren → ZIP hochladen
3. Plugin aktivieren

#### Verwendung

1. Seite in Elementor bearbeiten
2. Widget-Panel öffnen (linke Sidebar)
3. **"ChurchTools Suite"** Kategorie finden
4. **"ChurchTools Events"** Widget per Drag & Drop auf die Seite ziehen
5. Widget-Einstellungen im linken Panel anpassen

#### Voraussetzungen

- **ChurchTools Suite:** >= v1.0.9.0
- **Elementor:** >= v3.0.0 (Free reicht)
- **WordPress:** >= v6.0
- **PHP:** >= v8.0

---

## 🔮 Geplante Addons

### 🎨 Visual Composer Integration
**Status:** 🚧 In Planung

### ⚙️ Beaver Builder Integration
**Status:** 🚧 In Planung

### 🔔 Notifications Addon
**Status:** 🚧 In Planung

### 📱 Mobile App Connector
**Status:** 🚧 In Planung

### 📊 Analytics Addon
**Status:** 🚧 In Planung

### 🎯 Advanced Filters
**Status:** 🚧 In Planung

---

## 📖 Addon-Entwicklung

### Architektur

Addons folgen dem **Sub-Plugin Pattern**:

1. **Eigenständiges Plugin** mit eigener `plugin-name.php` Datei
2. **Dependency auf ChurchTools Suite** via `Requires Plugins:` Header
3. **Hook-basierte Integration** via `churchtools_suite_loaded` Hook
4. **Namespace-Trennung** (z.B. `CTS_Elementor_*`)

### Beispiel: Plugin-Header

```php
<?php
/**
 * Plugin Name:       ChurchTools Suite - My Addon
 * Plugin URI:        https://github.com/YourOrg/churchtools-suite-my-addon
 * Description:       My awesome addon for ChurchTools Suite
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Requires Plugins:  churchtools-suite
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-3.0-or-later
 * Text Domain:       churchtools-suite-my-addon
 */
```

### Beispiel: Hook-Integration

```php
<?php
// Warte auf churchtools_suite_loaded Hook
add_action( 'churchtools_suite_loaded', function() {
    if ( class_exists( 'ChurchTools_Suite' ) ) {
        My_Addon::init();
    }
}, 10 );

// Alternative: Late initialization support
if ( did_action( 'churchtools_suite_loaded' ) || 
     isset( $GLOBALS['churchtools_suite_plugin_instance'] ) ) {
    My_Addon::init();
}
```

---

## 🤝 Contribution

Möchtest du ein Addon entwickeln? Erstelle ein Issue auf GitHub!

---

## 📄 Lizenz

Alle Addons sind unter der **GPL-3.0-or-later** Lizenz verfügbar.
