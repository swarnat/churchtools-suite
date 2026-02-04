# Changelog v1.0.7.0

**Release Date:** 4. Februar 2026  
**Type:** Documentation Update

## 🗑️ Removed

### Documentation Cleanup
- **Removed:** Template-Überschreibung im Shortcode (event_template/modal_template Parameter)
  - **Reason:** Mit dedizierter Single-Event URL (`/events/`) ist die Template-Überschreibung pro Shortcode obsolet
  - **Impact:** Vereinfachte Dokumentation - fokussiert auf globale Template-Auswahl im Backend
  - **Breaking:** Nein - Feature wurde nie implementiert, nur dokumentiert

## 📝 Changed

### Documentation Updates
- **Updated:** Template-System Sektion in Dokumentation
  - **Before:** Komplexe Erklärung mit Template-Hierarchie, eigene Templates erstellen, Template-Überschreibung
  - **After:** Fokus auf Dropdown-Auswahl (Single Event: minimal/professional, Modal: minimal/professional)
  - **Benefit:** Klarer Fokus auf die tatsächlich verfügbare Funktionalität

## 🎯 Summary

Diese Version bereinigt die Dokumentation und entfernt theoretische Features, die mit der Single-Event URL-Struktur nicht mehr sinnvoll sind. Nutzer wählen Templates zentral im Backend, nicht pro Shortcode.

**No breaking changes** - Nur Dokumentations-Update, keine Code-Änderungen.

---

**Previous Version:** v1.0.6.2  
**Next Version:** TBD
