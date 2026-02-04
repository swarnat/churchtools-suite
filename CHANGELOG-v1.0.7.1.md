# Changelog v1.0.7.1

**Release-Datum:** 4. Februar 2026  
**Typ:** Hotfix  
**Priorität:** KRITISCH - Bitte sofort updaten!

## 🐛 Behobene Fehler

### Critical: Fatal Error beim Plugin-Load behoben
- **Problem:** `create_grid_calendar_pages()` wurde vor Initialisierung von `wp_rewrite` aufgerufen
- **Fehler:** `Call to a member function get_page_permastruct() on null`
- **Lösung:** Funktion komplett entfernt (wurde nicht mehr benötigt)
- **Betroffene Versionen:** v1.0.7.0
- **Impact:** Plugin konnte nicht aktiviert werden

### Admin-Settings: Single-Event URL Sektion entfernt
- Entfernte Konfigurationsoption für Single-Event Seiten-URL
- Mit dedizierter `/events/` URL-Struktur nicht mehr erforderlich
- Vereinfacht Admin-Interface

## 📝 Technische Details

**Geänderte Dateien:**
- `churchtools-suite.php`: Zeilen 68-90 entfernt (create_grid_calendar_pages)
- `admin/views/settings/subtab-templates.php`: Single-Event URL Formular entfernt

**Migration:**
- Keine Datenbank-Änderungen
- Keine Nutzeraktion erforderlich

## ⚠️ Wichtig

Falls Sie v1.0.7.0 installiert haben und einen Fatal Error bekommen:
1. Deaktivieren Sie das Plugin über FTP/PhpMyAdmin
2. Updaten Sie auf v1.0.7.1
3. Aktivieren Sie das Plugin erneut

## 🔗 Installation

**Automatisches Update:**
- WordPress Admin → Plugins → ChurchTools Suite → Update verfügbar

**Manuelles Update:**
1. Download: [churchtools-suite-1.0.7.1.zip](https://github.com/FEGAschaffenburg/churchtools-suite/releases/download/v1.0.7.1/churchtools-suite-1.0.7.1.zip)
2. Altes Plugin deaktivieren und löschen
3. Neues Plugin hochladen und aktivieren

## 📊 Version-Info

- **Vorherige Version:** v1.0.7.0 (FEHLERHAFT - nicht verwenden!)
- **Aktuelle Version:** v1.0.7.1
- **Nächste geplante Version:** v1.1.0 (Performance & Batch Processing)

---

**Hinweis:** v1.0.7.0 sollte nicht verwendet werden. Bitte updaten Sie direkt auf v1.0.7.1.
