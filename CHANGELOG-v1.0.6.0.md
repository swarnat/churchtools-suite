# ChurchTools Suite - Changelog v1.0.6.0

**Release Date:** 25. Januar 2026  
**Status:** ✅ Production Ready

---

## 🎯 Highlights

Diese Version behebt kritische Encoding-Probleme, die JSON-Parsing-Fehler in WordPress verursacht haben ("Unexpected token '﻿'").

---

## 🐛 Fixes

### Critical Fixes

- **UTF-8 BOM (Byte Order Mark) entfernt** (23 PHP-Dateien)
  - Symptom: JSON-Parse-Error "Unexpected token '﻿'" im WordPress Admin
  - Betroffene Dateien: `churchtools-suite.php`, `index.php` und 21 weitere
  - Lösung: Alle PHP-Dateien auf UTF-8 ohne BOM konvertiert
  - **Impact:** Settings Connection Test funktioniert nun korrekt

- **wp-config.php Korruption behoben**
  - Symptom: Fatal Error "Call to undefined function wp()"
  - Problem: Doppelte WP_DEBUG-Definitionen, Encoding-Fehler
  - Lösung: Kompletter Rewrite mit sauberer UTF-8-Kodierung
  - **Impact:** WordPress läuft nun stabil ohne Fatal Errors

- **Version-Mismatch behoben**
  - Header zeigte: 1.0.5.4
  - Konstante zeigte: 1.0.5.3
  - Gelöst: Beide auf 1.0.6.0 vereinheitlicht

### Security Fixes

- **23 Plugin-Verzeichnisse abgesichert**
  - Fehlende `index.php` Security-Dateien hinzugefügt
  - Verhindert Directory-Listing
  - Betrifft alle Plugins in `wp-content/plugins/`

---

## 🚀 Improvements

### Plugin Migration

- **churchtools-suite-demo vollständig migriert**
  - 130 Dateien von `Plugin_neu` nach `plugin-homepage` kopiert
  - Alle fehlenden Dateien ergänzt (19 includes, 8 repositories, 4 services)
  - Plugin ist nun vollständig lauffähig

### Cleanup & Optimization

- **Cache bereinigt**
  - `wp-content/cache` geleert
  - `wp-content/upgrade` bereinigt
  - Alte temporäre Dateien entfernt

- **WordPress-ZIP optimiert**
  - Forward-Slashes für WordPress-Kompatibilität
  - Keine .git, node_modules, tests
  - 0.34 MB, 118 Einträge

---

## 🔧 Technical Details

### Encoding Changes

**Before:**
```
0xEF 0xBB 0xBF <?php... (UTF-8 BOM)
```

**After:**
```
<?php... (UTF-8 ohne BOM)
```

### Files Modified

| File Type | Count | Action |
|-----------|-------|--------|
| PHP Files (BOM-Fix) | 23 | Encoding korrigiert |
| index.php (Security) | 25 | Neu erstellt |
| wp-config.php | 1 | Komplett neu geschrieben |
| Version-Files | 1 | Header + Konstante aktualisiert |

### Database Configuration (wp-config.php)

```php
DB_NAME: 'feg-clone'
DB_USER: 'root'
DB_PASSWORD: ''
DB_HOST: 'localhost'
DB_CHARSET: 'utf8'

WP_DEBUG: true
WP_DEBUG_LOG: true
WP_DEBUG_DISPLAY: false
```

---

## 📦 Deployment

### Installation

1. Download: `C:\privat\churchtools-suite-1.0.6.0.zip`
2. Upload zu WordPress: `Plugins > Installieren > Plugin hochladen`
3. Aktivieren und ChurchTools-Einstellungen prüfen

### Git

```bash
git clone <repository>
git checkout v1.0.6.0
```

### Verification

Nach Installation prüfen:
- ✅ WordPress lädt ohne Fatal Errors
- ✅ Settings Connection Test funktioniert (kein JSON-Error)
- ✅ ChurchTools Suite Admin-Panel öffnet
- ✅ Plugin aktiviert ohne Fehler

---

## ⚠️ Breaking Changes

**Keine Breaking Changes** in dieser Version.

---

## 🔄 Upgrade Path

### Von v1.0.5.x

1. Plugin deaktivieren
2. Alte Version löschen
3. v1.0.6.0 hochladen und aktivieren
4. Settings > ChurchTools > "Verbindung testen" ausführen

**Datenbank-Migrationen:** Nicht erforderlich (DB Version 1.2 bleibt)

---

## 📋 Checklist

- [x] BOM-Encoding in allen PHP-Dateien entfernt
- [x] wp-config.php neu geschrieben
- [x] Version-Mismatch behoben
- [x] Plugin Security-Dateien erstellt
- [x] churchtools-suite-demo vollständig
- [x] Git Repository initialisiert
- [x] Git Tag v1.0.6.0 erstellt
- [x] WordPress-ZIP erstellt und validiert
- [x] CHANGELOG dokumentiert

---

## 🐞 Known Issues

**Keine bekannten Probleme** in v1.0.6.0.

---

## 📞 Support

**GitHub:** [FEGAschaffenburg/churchtools-suite](https://github.com/FEGAschaffenburg/churchtools-suite)  
**Issues:** [GitHub Issues](https://github.com/FEGAschaffenburg/churchtools-suite/issues)  
**Docs:** [Plugin Documentation](https://plugin.feg-aschaffenburg.de/)

---

**Nächste Version:** v1.1.0 (geplant: Performance & Batch Processing)  
**Roadmap:** Siehe [ROADMAP.md](ROADMAP.md)

