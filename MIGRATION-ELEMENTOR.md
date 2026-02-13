# Elementor Integration - Migration Guide

## 📋 Übersicht

Ab Version **v1.0.9.0** wird die Elementor-Integration aus dem Hauptplugin in ein separates, optionales **Sub-Plugin** ausgelagert.

**Warum?**
- **Modularisierung:** Saubere Trennung von Core-Features und Page Builder Integrationen
- **Optionale Installation:** Nur Elementor-Nutzer benötigen das Sub-Plugin
- **Einfachere Wartung:** Separate Releases und Updates möglich
- **Zukunftssicher:** Basis für weitere Sub-Plugins (z.B. Gutenberg Pro, WooCommerce Integration)

---

## ⏱️ Timeline

| Version | Zeitraum | Status | Aktion |
|---------|----------|--------|--------|
| **v1.0.9.0** | Q1 2026 | ✅ Aktuell | Sub-Plugin verfügbar, beide Versionen funktionieren |
| **v1.0.10.0 - v1.9.x** | Q2-Q3 2026 | ⚠️ Deprecation | Admin Notice: Migration empfohlen |
| **v2.0.0** | Q4 2026 | ❌ Breaking Change | Elementor-Code entfernt, nur noch Sub-Plugin |

---

## 🚀 Installation Sub-Plugin

### Voraussetzungen

- **ChurchTools Suite** >= v1.0.9.0
- **Elementor** >= v3.0.0
- **WordPress** >= 6.0
- **PHP** >= 8.0

### Download & Installation

**GitHub Release:**
```
https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases/latest
```

**Installation:**
1. WordPress Admin → Plugins → Installieren
2. "Plugin hochladen" klicken
3. ZIP-Datei auswählen: `churchtools-suite-elementor-X.X.X.zip`
4. "Jetzt installieren" → "Aktivieren"

**Erfolg prüfen:**
- Elementor Editor öffnen
- Widget-Panel → "ChurchTools Suite" Kategorie suchen
- "ChurchTools Events" Widget sollte verfügbar sein

---

## 🔄 Upgrade-Pfade

### Szenario 1: Update von v1.0.8.0 auf v1.0.9.0

**Was passiert:**
- ✅ Elementor Widget funktioniert weiterhin über Hauptplugin
- ⚠️ Noch keine Admin Notice (erst ab v1.0.10.0)
- 💡 Sub-Plugin ist verfügbar, aber optional

**Empfehlung:**
- Sub-Plugin installieren (Vorbereitung für v2.0.0)
- Testen ob Widget weiterhin funktioniert
- Beide Plugins laufen parallel ohne Konflikte

### Szenario 2: Update von v1.0.9.0 auf v2.0.0

**Was passiert:**
- ❌ Elementor-Code komplett aus Hauptplugin entfernt
- ⚠️ Widget funktioniert NUR noch mit Sub-Plugin
- 🔧 Ohne Sub-Plugin: Kein Elementor Widget verfügbar

**WICHTIG:**
- Vor Update auf v2.0.0: Sub-Plugin installieren!
- Nach Update prüfen: Widget in Elementor verfügbar?
- Bei Problemen: Sub-Plugin neu aktivieren

### Szenario 3: Neu-Installation (ab v1.0.9.0)

**Elementor nutzen:**
1. ChurchTools Suite installieren (>= v1.0.9.0)
2. Elementor installieren
3. **Sub-Plugin** installieren
4. Fertig!

**Kein Elementor:**
- Nur ChurchTools Suite installieren
- Sub-Plugin NICHT erforderlich
- Gutenberg Blocks stehen zur Verfügung

---

## 👥 Für Nutzer

### Was ändert sich?

**Funktionalität:** ✅ Keine Änderung
- Widget funktioniert identisch
- Alle Einstellungen bleiben erhalten
- Keine neuen Anforderungen

**Installation:** ⚠️ Zusätzlicher Schritt
- Ab v2.0.0: Sub-Plugin manuell installieren
- Einmalig, dann automatische Updates

**Design:** ✅ Keine Änderung
- Templates bleiben gleich
- Shortcodes funktionieren weiterhin
- Bestehende Seiten unverändert

### FAQ

**Q: Muss ich das Sub-Plugin jetzt installieren?**  
A: Nein, erst ab v2.0.0 (Q4 2026) zwingend erforderlich. Bis dahin funktioniert die eingebaute Version.

**Q: Funktionieren beide Versionen parallel (Hauptplugin + Sub-Plugin)?**  
A: Ja, v1.0.9.0 - v1.9.x erkennen automatisch das Sub-Plugin und laden die eingebaute Version nicht mehr.

**Q: Verliere ich meine Elementor-Widgets bei Update?**  
A: Nein, solange das Sub-Plugin aktiv ist. Vor v2.0.0 Update: Sub-Plugin installieren!

**Q: Kostet das Sub-Plugin extra?**  
A: Nein, kostenlos und Open Source (GPL-3.0) wie das Hauptplugin.

**Q: Ich nutze kein Elementor, muss ich das Sub-Plugin trotzdem installieren?**  
A: Nein, nur Elementor-Nutzer benötigen es. Gutenberg Blocks sind im Hauptplugin integriert.

---

## 🔧 Für Entwickler

### Neue Action Hook

**v1.0.9.0+:**
```php
/**
 * Action: churchtools_suite_loaded
 * 
 * Feuert NACH allen Core-Dependencies und Hook-Definitionen
 * aber VOR der Ausführung der registrierten Hooks via Loader.
 * 
 * Ideal für Sub-Plugins um in das Hauptplugin zu hooken.
 * 
 * @param ChurchTools_Suite $plugin Hauptplugin-Instanz
 * @since 1.0.9.0
 */
do_action( 'churchtools_suite_loaded', $plugin );
```

**Verwendung im Sub-Plugin:**
```php
add_action( 'churchtools_suite_loaded', function( $plugin ) {
    // Zugriff auf Main Plugin API
    $version = $plugin->get_version();
    
    // Repository Factory nutzen
    $calendars_repo = churchtools_suite_get_repository( 'calendars' );
    
    // Sub-Plugin Funktionalität initialisieren
    My_SubPlugin::init();
}, 10, 1 );
```

### Dependency Checks

**Sub-Plugin sollte prüfen:**
```php
add_action( 'plugins_loaded', function() {
    // 1. ChurchTools Suite vorhanden?
    if ( ! class_exists( 'ChurchTools_Suite' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>Sub-Plugin requires ChurchTools Suite >= v1.0.9.0</p></div>';
        });
        return;
    }
    
    // 2. Repository Factory verfügbar?
    if ( ! function_exists( 'churchtools_suite_get_repository' ) ) {
        add_action( 'admin_notices', function() {
            echo '<div class="error"><p>Sub-Plugin requires ChurchTools Suite >= v1.0.9.0</p></div>';
        });
        return;
    }
    
    // 3. Alles ok, Sub-Plugin initialisieren
    add_action( 'churchtools_suite_loaded', 'my_subplugin_init' );
}, 20 );
```

### Breaking Changes (v2.0.0)

**Entfernte Dateien:**
```
includes/class-churchtools-suite-elementor-integration.php
includes/elementor/class-churchtools-suite-elementor-events-widget.php
includes/elementor/ (gesamter Ordner)
```

**Entfernte Hooks:**
```php
// Diese werden NICHT mehr gefeuert:
'elementor/elements/categories_registered' (im Main Plugin)
'elementor/widgets/register' (im Main Plugin)
```

**Beibehaltene API:**
```php
// Diese bleiben verfügbar:
do_action( 'churchtools_suite_loaded', $plugin );
churchtools_suite_get_repository( $type );
do_shortcode( '[cts_list ...]' );
do_shortcode( '[cts_grid ...]' );
do_shortcode( '[cts_calendar ...]' );
```

### Migration Checklist für eigene Sub-Plugins

- [ ] Hook: `churchtools_suite_loaded` verwenden
- [ ] Dependency Checks: ChurchTools Suite >= v1.0.9.0
- [ ] Repository Factory: `churchtools_suite_get_repository()` nutzen
- [ ] Shortcodes: Via `do_shortcode()` aufrufen (nicht direkt)
- [ ] Constants: `CHURCHTOOLS_SUITE_PATH` verfügbar
- [ ] Admin Notices: Bei fehlenden Dependencies anzeigen

---

## 📦 Sub-Plugin Details

**Repository:**  
https://github.com/FEGAschaffenburg/churchtools-suite-elementor

**License:**  
GPL-3.0-or-later (wie Hauptplugin)

**Releases:**  
https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases

**Issues:**  
https://github.com/FEGAschaffenburg/churchtools-suite-elementor/issues

**Changelog:**  
https://github.com/FEGAschaffenburg/churchtools-suite-elementor/blob/master/CHANGELOG.md

---

## 🆘 Support

**Bei Problemen:**

1. **Dependency Check:** ChurchTools Suite >= v1.0.9.0?
2. **Elementor aktiv?** Mindestens v3.0.0?
3. **Sub-Plugin aktiviert?** Plugin-Liste prüfen
4. **Widget sichtbar?** Elementor Editor → Widget-Panel → "ChurchTools Suite"
5. **Fehler-Logs:** WordPress Debug Log prüfen (`wp-content/debug.log`)

**GitHub Issues:**
- Hauptplugin: https://github.com/FEGAschaffenburg/churchtools-suite/issues
- Sub-Plugin: https://github.com/FEGAschaffenburg/churchtools-suite-elementor/issues

**Community:**
- Dokumentation: https://github.com/FEGAschaffenburg/churchtools-suite/tree/main/docs
- Discussions: https://github.com/FEGAschaffenburg/churchtools-suite/discussions

---

**Erstellt:** 13. Februar 2026  
**Version:** 1.0  
**Gültig für:** ChurchTools Suite v1.0.9.0+
