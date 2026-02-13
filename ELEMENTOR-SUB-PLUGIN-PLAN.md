# Elementor Sub-Plugin Extraktion - Implementierungsplan

## 📋 Übersicht

**Ziel:** Extraktion der Elementor-Integration aus dem Hauptplugin in ein separates, optionales Sub-Plugin

**Motivation:**
- Saubere Modularisierung (Hauptplugin bleibt schlank)
- Optional Installation (nur für Elementor-Nutzer relevant)
- Einfachere Wartung (separate Releases möglich)
- Zukunftssicher (weitere Sub-Plugins nach gleichem Muster)

**Aktueller Stand:**
- Elementor-Code im Hauptplugin: 2 Dateien, 741 Zeilen
- Integration seit: v1.0.4.0
- Conditional Loading: Ja (nur wenn Elementor aktiv)

---

## 🎯 Zeitplan & Releases

### Phase 1: Sub-Plugin erstellen (v1.0.9.0 - Q1 2026)
- ✅ Neue Repository: `churchtools-suite-elementor`
- ✅ Sub-Plugin implementieren
- ✅ Hook-System im Hauptplugin (`churchtools_suite_loaded`)
- ✅ Beide Versionen parallel lauffähig (Hauptplugin + Sub-Plugin)
- ✅ Migration Guide veröffentlichen

### Phase 2: Deprecation (v1.0.10.0 - v1.9.x)
- ⚠️ Admin Notice im Hauptplugin: "Elementor-Integration wird in v2.0.0 entfernt"
- ⚠️ Empfehlung: Sub-Plugin installieren
- ⏳ Grace Period: Mindestens 6 Monate vor v2.0.0

### Phase 3: Removal (v2.0.0 - Q4 2026)
- ❌ Elementor-Code komplett aus Hauptplugin entfernt
- ✅ Sub-Plugin ist einzige Möglichkeit für Elementor-Nutzung
- ✅ Breaking Change dokumentiert

---

## 🏗️ Technische Struktur

### Aktuelle Struktur (Hauptplugin v1.0.8.0)

```
churchtools-suite/
├── includes/
│   ├── class-churchtools-suite.php
│   │   └── Constructor: Lädt Elementor via plugins_loaded Hook (Priority 20)
│   ├── class-churchtools-suite-elementor-integration.php (114 Zeilen)
│   │   ├── init() - Registriert Hooks
│   │   ├── register_category() - Erstellt "ChurchTools Suite" Kategorie
│   │   ├── register_widget() - Lädt & registriert Widget
│   │   └── log() - Debug-Logging
│   └── elementor/
│       └── class-churchtools-suite-elementor-events-widget.php (627 Zeilen)
│           ├── get_name() → 'churchtools_suite_events'
│           ├── get_title() → 'ChurchTools Events'
│           ├── get_icon() → 'eicon-calendar'
│           ├── get_categories() → ['basic', 'churchtools-suite']
│           ├── register_controls() → 6 Sections (Content, Filters, Display, Grid, Style, Advanced)
│           ├── render() → Baut [cts_list], [cts_grid], [cts_calendar] Shortcodes
│           ├── build_shortcode_atts() → Konvertiert Settings zu Shortcode-Attributen
│           ├── get_calendars_options() → Lädt Kalender via Repository Factory
│           └── get_tags_options() → Lädt Tags via Repository Factory
```

**Initialisierung (class-churchtools-suite.php, Zeile 75-91):**
```php
add_action( 'plugins_loaded', function() {
    if ( is_plugin_active( 'elementor/elementor.php' ) || did_action( 'elementor/loaded' ) ) {
        require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-elementor-integration.php';
    }
}, 20 );
```

**Abhängigkeiten:**
- ✅ CHURCHTOOLS_SUITE_PATH Konstante (Main Plugin)
- ✅ `do_shortcode()` (WordPress Core)
- ✅ `churchtools_suite_get_repository()` (Main Plugin Factory Funktion)
- ✅ Shortcode-Handler: `cts_list`, `cts_grid`, `cts_calendar` (Main Plugin)
- ✅ Template Loader (indirekt via Shortcodes)
- ✅ Views Registry (indirekt via Shortcodes)

---

### Ziel-Struktur (Sub-Plugin v1.0.0)

```
churchtools-suite-elementor/
├── churchtools-suite-elementor.php (Main Plugin File)
├── readme.txt (WordPress.org Format)
├── includes/
│   ├── class-cts-elementor-integration.php (umbenannt von Original)
│   └── class-cts-elementor-events-widget.php (umbenannt von Original)
└── assets/
    ├── icon-128x128.png (Plugin Icon)
    └── banner-772x250.png (WordPress.org Banner)
```

**Main Plugin File:**
```php
<?php
/**
 * Plugin Name: ChurchTools Suite - Elementor Integration
 * Plugin URI: https://github.com/FEGAschaffenburg/churchtools-suite-elementor
 * Description: Elementor Widget für ChurchTools Suite Events
 * Version: 1.0.0
 * Requires Plugins: churchtools-suite, elementor
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: FEG Aschaffenburg
 * Author URI: https://www.feg-aschaffenburg.de
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: churchtools-suite-elementor
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin Constants
define( 'CTS_ELEMENTOR_VERSION', '1.0.0' );
define( 'CTS_ELEMENTOR_PATH', plugin_dir_path( __FILE__ ) );
define( 'CTS_ELEMENTOR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check dependencies and initialize
 */
add_action( 'plugins_loaded', function() {
    // 1. Check if ChurchTools Suite is active and >= v1.0.9.0
    if ( ! class_exists( 'ChurchTools_Suite' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>';
            echo '<strong>ChurchTools Suite - Elementor Integration</strong> erfordert das ';
            echo '<strong>ChurchTools Suite Plugin (>= v1.0.9.0)</strong>.';
            echo '</p></div>';
        });
        return;
    }

    // 2. Check if Elementor is active
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>';
            echo '<strong>ChurchTools Suite - Elementor Integration</strong> erfordert das ';
            echo '<strong>Elementor Plugin</strong>.';
            echo '</p></div>';
        });
        return;
    }

    // 3. Check main plugin version
    if ( ! function_exists( 'churchtools_suite_get_repository' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>';
            echo '<strong>ChurchTools Suite - Elementor Integration</strong> erfordert ';
            echo '<strong>ChurchTools Suite v1.0.9.0 oder höher</strong>.';
            echo '</p></div>';
        });
        return;
    }

    // 4. Hook into main plugin's loaded action
    add_action( 'churchtools_suite_loaded', function() {
        require_once CTS_ELEMENTOR_PATH . 'includes/class-cts-elementor-integration.php';
        CTS_Elementor_Integration::init();
    });
}, 20 );
```

---

## 🔄 Migration Strategy

### Haupt-Plugin Änderungen (v1.0.9.0)

#### 1. Neuer Hook vor Loader-Initialisierung

**Datei:** `includes/class-churchtools-suite.php`  
**Zeile:** Nach allen Core-Classes geladen, vor `$this->loader->run()`

```php
/**
 * Constructor
 */
public function __construct() {
    $this->load_dependencies();
    $this->set_locale();
    $this->init_logger();
    $this->run_migrations();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    
    // v1.0.9.0: Allow sub-plugins to hook in
    // This action fires AFTER all core dependencies are loaded
    // but BEFORE hooks are registered via Loader
    do_action( 'churchtools_suite_loaded', $this );
    
    $this->loader->run();
}
```

#### 2. Deprecation Notice (v1.0.10.0)

**Datei:** `includes/class-churchtools-suite.php`  
**Zeile:** Im `plugins_loaded` Hook für Elementor

```php
add_action( 'plugins_loaded', function() {
    if ( is_plugin_active( 'elementor/elementor.php' ) || did_action( 'elementor/loaded' ) ) {
        
        // v1.0.10.0: Check if sub-plugin is active
        if ( ! class_exists( 'CTS_Elementor_Integration' ) ) {
            // Load built-in Elementor (deprecated)
            require_once CHURCHTOOLS_SUITE_PATH . 'includes/class-churchtools-suite-elementor-integration.php';
            
            // Show deprecation notice
            add_action( 'admin_notices', function() {
                $screen = get_current_screen();
                if ( $screen && strpos( $screen->id, 'churchtools' ) !== false ) {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p><strong>⚠️ Hinweis:</strong> Die eingebaute Elementor-Integration wird in ';
                    echo '<strong>ChurchTools Suite v2.0.0</strong> entfernt.</p>';
                    echo '<p>Bitte installieren Sie das <strong>ChurchTools Suite - Elementor Integration</strong> ';
                    echo 'Sub-Plugin: <a href="https://github.com/FEGAschaffenburg/churchtools-suite-elementor">Download</a></p>';
                    echo '</div>';
                }
            });
        }
    }
}, 20 );
```

#### 3. Removal (v2.0.0)

**Zu löschen:**
- `includes/class-churchtools-suite-elementor-integration.php`
- `includes/elementor/class-churchtools-suite-elementor-events-widget.php`
- `includes/elementor/` (Ordner)
- `plugins_loaded` Hook für Elementor (siehe oben)

**Zu behalten:**
- `churchtools_suite_loaded` Action Hook (für alle Sub-Plugins)
- `churchtools_suite_get_repository()` Factory Funktion

---

## 📝 Sub-Plugin Implementation Details

### Namenskonventionen

**Klassen:**
- `ChurchTools_Suite_Elementor_Integration` → `CTS_Elementor_Integration`
- `ChurchTools_Suite_Elementor_Events_Widget` → `CTS_Elementor_Events_Widget`

**Konstanten:**
- `CHURCHTOOLS_SUITE_PATH` → `CTS_ELEMENTOR_PATH` (nur intern)
- Nutze `CHURCHTOOLS_SUITE_PATH` vom Main Plugin für Shortcodes/Templates

**Text Domain:**
- `churchtools-suite` → `churchtools-suite-elementor`

**Log Option:**
- `churchtools_suite_elementor_log` → `cts_elementor_log`

### Code-Anpassungen

#### Integration Klasse (`class-cts-elementor-integration.php`)

**Vor (Main Plugin):**
```php
require_once CHURCHTOOLS_SUITE_PATH . 'includes/elementor/class-churchtools-suite-elementor-events-widget.php';
```

**Nach (Sub-Plugin):**
```php
require_once CTS_ELEMENTOR_PATH . 'includes/class-cts-elementor-events-widget.php';
```

#### Widget Klasse (`class-cts-elementor-events-widget.php`)

**KEINE ÄNDERUNGEN NÖTIG!** Das Widget verwendet:
- `do_shortcode()` - WordPress Core
- `churchtools_suite_get_repository()` - Main Plugin API
- `\Elementor\Widget_Base` - Elementor Core

Alle Dependencies werden vom Main Plugin bereitgestellt.

---

## ✅ Testing Checkliste

### Pre-Release Tests (v1.0.9.0)

**Haupt-Plugin:**
- [ ] `churchtools_suite_loaded` Hook wird gefeuert
- [ ] Hook feuert NACH Dependencies aber VOR Loader
- [ ] Elementor lädt weiterhin wenn Sub-Plugin NICHT installiert
- [ ] Deprecation Notice NICHT sichtbar wenn Sub-Plugin aktiv

**Sub-Plugin:**
- [ ] Dependency Checks funktionieren (Main Plugin, Elementor)
- [ ] Admin Notices angezeigt bei fehlenden Dependencies
- [ ] Widget erscheint in Elementor Widget-Liste
- [ ] Widget-Kategorie "ChurchTools Suite" wird erstellt
- [ ] Alle 6 Control Sections funktionieren
- [ ] Shortcode-Rendering funktioniert (List, Grid, Calendar)
- [ ] Repository Factory funktioniert (Kalender/Tags Options)

### Kompatibilitätstests

**Szenario 1: Main Plugin v1.0.9.0 + Sub-Plugin v1.0.0**
- [ ] Widget funktioniert über Sub-Plugin
- [ ] KEINE doppelte Widget-Registrierung
- [ ] KEINE PHP Errors

**Szenario 2: Main Plugin v1.0.9.0 OHNE Sub-Plugin**
- [ ] Widget funktioniert über Main Plugin (backward compatibility)
- [ ] Deprecation Notice wird angezeigt

**Szenario 3: Main Plugin v2.0.0 + Sub-Plugin v1.0.0**
- [ ] Widget funktioniert AUSSCHLIESSLICH über Sub-Plugin
- [ ] KEINE PHP Errors wegen fehlender Main Plugin Dateien

**Szenario 4: Main Plugin v2.0.0 OHNE Sub-Plugin**
- [ ] KEIN Widget verfügbar
- [ ] KEINE PHP Errors
- [ ] Optional: Admin Notice "Für Elementor bitte Sub-Plugin installieren"

### Upgrade-Pfad

**User hat Main Plugin v1.0.8.0 (mit Elementor):**
1. Update zu v1.0.9.0 → Widget funktioniert weiterhin (backward compat)
2. Deprecation Notice erscheint → User installiert Sub-Plugin
3. Update zu v2.0.0 → Widget funktioniert über Sub-Plugin

**User hat Main Plugin v1.0.8.0 (OHNE Elementor):**
1. Update zu v2.0.0 → Keine Änderung (Elementor-Code wurde nie geladen)

---

## 📚 Dokumentation

### Migration Guide (MIGRATION-ELEMENTOR.md)

Erstelle im Hauptplugin:

```markdown
# Elementor Integration - Migration Guide

## Übersicht

Die Elementor-Integration wird aus dem Hauptplugin in ein separates Sub-Plugin extrahiert.

## Timeline

- **v1.0.9.0 (Q1 2026):** Sub-Plugin verfügbar, beide Versionen funktionieren
- **v1.0.10.0 - v1.9.x:** Deprecation Period (Admin Notice)
- **v2.0.0 (Q4 2026):** Entfernung aus Hauptplugin (Breaking Change)

## Installation Sub-Plugin

1. Download: https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases
2. WordPress Admin → Plugins → Installieren → ZIP hochladen
3. Aktivieren

**Voraussetzungen:**
- ChurchTools Suite >= v1.0.9.0
- Elementor >= v3.0.0

## Für Entwickler

**Neue Action Hook:**
```php
// Main Plugin v1.0.9.0+
do_action( 'churchtools_suite_loaded', $churchtools_suite_instance );
```

**Sub-Plugin Repositories:**
- GitHub: https://github.com/FEGAschaffenburg/churchtools-suite-elementor
- Releases: https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases

## FAQ

**Q: Muss ich das Sub-Plugin installieren?**  
A: Ja, ab v2.0.0 (Q4 2026). Bis dahin funktioniert die eingebaute Version.

**Q: Funktionieren beide Versionen parallel?**  
A: Ja, v1.0.9.0 - v1.9.x unterstützen beide Varianten.

**Q: Was ändert sich für End-User?**  
A: Nichts, das Widget funktioniert identisch. Nur Installation ist erforderlich.
```

### Changelog Updates

**Hauptplugin CHANGELOG.md:**

```markdown
## v1.0.9.0 - Elementor Sub-Plugin Support (Q1 2026)

### 🏗️ Architektur
- **Sub-Plugin Hook System** - Neuer Action Hook `churchtools_suite_loaded`
  - Erlaubt Sub-Plugins nach Core-Initialisierung zu laden
  - Feuert nach Dependencies, vor Loader::run()
  - Basis für modulare Plugin-Architektur

### ♻️ Deprecations
- **Elementor Integration** - Eingebaute Elementor-Integration als deprecated markiert
  - Wird in v2.0.0 entfernt
  - Sub-Plugin verfügbar: churchtools-suite-elementor
  - Siehe MIGRATION-ELEMENTOR.md

---

## v2.0.0 - Major Release (Q4 2026)

### ❌ Breaking Changes
- **Elementor Integration entfernt** - Nutzen Sie das Sub-Plugin
  - Removed: includes/class-churchtools-suite-elementor-integration.php
  - Removed: includes/elementor/*
  - Migration: https://github.com/FEGAschaffenburg/churchtools-suite-elementor
```

**Sub-Plugin CHANGELOG.md:**

```markdown
# ChurchTools Suite - Elementor Integration - Changelog

## v1.0.0 - Initial Release (Q1 2026)

### ✨ Features
- **ChurchTools Events Widget** - Elementor Page Builder Integration
  - 28+ Kontrollparameter (Content, Filters, Display, Grid, Style, Advanced)
  - Unterstützt List, Grid, Calendar Views
  - Shortcode-Wrapper Architektur (re-use Main Plugin functionality)

### 🔧 Technische Details
- Requires: ChurchTools Suite >= v1.0.9.0
- Requires: Elementor >= v3.0.0
- Kompatibel mit Main Plugin v1.0.9.0 - v1.9.x (parallel)
- Einzige Option ab Main Plugin v2.0.0

### 📦 Installation
1. Main Plugin updaten auf >= v1.0.9.0
2. Sub-Plugin ZIP herunterladen
3. Über WordPress installieren & aktivieren
```

---

## 🚀 Deployment Workflow

### Repository Setup

**Main Plugin:**
- Repository: https://github.com/FEGAschaffenburg/churchtools-suite
- Branch: `feature/elementor-subplugin-hook` (für v1.0.9.0)
- Merge to: `master` oder `release/v1.0.9.0`

**Sub-Plugin:**
- Repository: https://github.com/FEGAschaffenburg/churchtools-suite-elementor (NEU)
- Branch: `master`
- Initial Commit: Code aus Main Plugin v1.0.8.0 extrahiert

### Release Process

**v1.0.9.0 (Main Plugin):**
```bash
cd churchtools-suite
git checkout -b feature/elementor-subplugin-hook

# 1. Add churchtools_suite_loaded hook
# 2. Add deprecation notice logic (commented out, activate in v1.0.10.0)
# 3. Create MIGRATION-ELEMENTOR.md
# 4. Update CHANGELOG.md
# 5. Update version to 1.0.9.0

git add .
git commit -m "feat: Add sub-plugin support via churchtools_suite_loaded hook"
git push origin feature/elementor-subplugin-hook

# Create PR, review, merge to master
# Tag release
git tag v1.0.9.0
git push origin v1.0.9.0

# Create ZIP
.\scripts\create-wp-zip.ps1 -Version "1.0.9.0"
```

**v1.0.0 (Sub-Plugin):**
```bash
# Create new repository on GitHub
cd c:\Users\nauma\OneDrive\laragon\www\plugin-homepage\wp-content\plugins
mkdir churchtools-suite-elementor
cd churchtools-suite-elementor

# Initialize Git
git init
git remote add origin https://github.com/FEGAschaffenburg/churchtools-suite-elementor.git

# Copy files from main plugin
mkdir includes
copy ..\..\feg-clone\wp-content\plugins\churchtools-suite\includes\class-churchtools-suite-elementor-integration.php includes\class-cts-elementor-integration.php
copy ..\..\feg-clone\wp-content\plugins\churchtools-suite\includes\elementor\class-churchtools-suite-elementor-events-widget.php includes\class-cts-elementor-events-widget.php

# Create main plugin file
# Create readme.txt
# Create CHANGELOG.md
# Update class names and paths

git add .
git commit -m "Initial release: Extracted from ChurchTools Suite v1.0.8.0"
git push -u origin master

# Tag release
git tag v1.0.0
git push origin v1.0.0

# Create ZIP manually or via GitHub Actions
```

### GitHub Release Assets

**Main Plugin v1.0.9.0:**
- `churchtools-suite-1.0.9.0.zip` (WordPress installable)
- Release Notes: Neue Hook, Elementor Sub-Plugin Support

**Sub-Plugin v1.0.0:**
- `churchtools-suite-elementor-1.0.0.zip` (WordPress installable)
- Release Notes: Initial release, extracted from Main Plugin

---

## 💡 Zukunft: Weitere Sub-Plugins

Dieses Pattern kann für weitere Integrationen verwendet werden:

**Potenzielle Sub-Plugins:**
- `churchtools-suite-gravity-forms` (Anmeldungen via Gravity Forms)
- `churchtools-suite-woocommerce` (Event Tickets via WooCommerce)
- `churchtools-suite-analytics` (Erweiterte Statistiken)
- `churchtools-suite-multilanguage` (WPML/Polylang Integration)

**Hook System:**
```php
// Main Plugin
do_action( 'churchtools_suite_loaded', $main_plugin_instance );
apply_filters( 'churchtools_suite_shortcode_atts', $atts, $shortcode_tag );
apply_filters( 'churchtools_suite_render_output', $html, $view_type );
apply_filters( 'churchtools_suite_event_data', $event, $source );
```

---

## 📊 Entscheidungsmatrix

| Aspekt | Haupt-Plugin (Monolith) | Sub-Plugin (Modular) | ✅ Empfehlung |
|--------|-------------------------|----------------------|---------------|
| **Code-Größe** | 741 Zeilen | 0 Zeilen Main Plugin | Sub-Plugin |
| **Wartbarkeit** | Alle Features in 1 Repo | Separate Releases | Sub-Plugin |
| **User Choice** | Immer geladen (wenn Elementor aktiv) | Optional Installation | Sub-Plugin |
| **Update-Komplexität** | 1 Plugin updaten | 2 Plugins updaten | Haupt-Plugin |
| **Backward Compat** | Einfach (alles integriert) | Grace Period nötig | Haupt-Plugin |
| **Zukunftssicherheit** | Schwer zu extrahieren | Bereits modular | Sub-Plugin |

**Fazit:** Sub-Plugin-Architektur ist langfristig besser, erfordert aber sorgfältige Migration.

---

## ❓ Offene Fragen

1. **Timeline zu aggressiv?**
   - Alternative: Phase 2 auf 12 Monate verlängern?
   
2. **Auto-Updater für Sub-Plugin?**
   - Lösung: GitHub Updater wie bei Main Plugin
   
3. **WordPress.org Distribution?**
   - Optional: Sub-Plugin auch auf WordPress.org veröffentlichen
   
4. **Dokumentation wo hosten?**
   - Option A: docs/ in Sub-Plugin Repo
   - Option B: Separate Website (docs.churchtools-suite.de)

---

## ✅ Next Steps

1. **Main Plugin v1.0.9.0 erstellen**
   - [ ] `churchtools_suite_loaded` Hook hinzufügen
   - [ ] MIGRATION-ELEMENTOR.md schreiben
   - [ ] Changelog updaten
   - [ ] Branch: `feature/elementor-subplugin-hook`

2. **Sub-Plugin Repository erstellen**
   - [ ] GitHub Repo anlegen: churchtools-suite-elementor
   - [ ] Code aus Main Plugin extrahieren
   - [ ] Umbenennen (Klassen, Konstanten, Text Domain)
   - [ ] readme.txt schreiben (WordPress.org Format)

3. **Testing**
   - [ ] Lokales WordPress mit Main v1.0.9.0 + Sub-Plugin v1.0.0
   - [ ] Alle Widget-Features testen
   - [ ] Dependency Checks testen
   - [ ] Upgrade-Pfade simulieren

4. **Release**
   - [ ] Main Plugin v1.0.9.0 auf GitHub releasen
   - [ ] Sub-Plugin v1.0.0 auf GitHub releasen
   - [ ] ZIPs erstellen und testen
   - [ ] Dokumentation verlinken

---

**Erstellt:** 2026-01-21  
**Version:** 1.0  
**Status:** Draft - Bereit für Review
