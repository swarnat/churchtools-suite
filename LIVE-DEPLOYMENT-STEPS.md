# Live-Deployment: ChurchTools Suite v1.0.8.0 + Demo v1.0.7.0

**Status:** Ready for Production  
**Datum:** 6. Februar 2026  
**ZIPs:** C:\privat\churchtools-suite-1.0.8.0.zip + C:\privat\churchtools-suite-demo-1.0.7.0.zip

---

## ✅ Lokale Tests (feg-clone)

- ✅ Repository Factory: 4 Filter registriert
- ✅ Demo Repositories: ChurchTools_Suite_Demo_Events_Repository aktiv
- ✅ Full Registration: 26 Events isoliert (user_id 6)
- ✅ Database Isolation: wp_demo_cts_events korrekt
- ✅ Frontend Test: Alle Checks bestanden

---

## 📦 Deployment-Pakete

| Paket | Pfad | Größe | Dateianzahl |
|-------|------|-------|-------------|
| Main Plugin | `C:\privat\churchtools-suite-1.0.8.0.zip` | 0.35 MB | 125 |
| Demo Plugin | `C:\privat\churchtools-suite-demo-1.0.7.0.zip` | ~0.5 MB | ~80 |

**Hinweis:** SSH-Upload fehlgeschlagen (Connection timeout) - Manuelle Installation erforderlich

---

## 🚀 Live-Deployment-Schritte

### Schritt 1: WordPress Admin Upload (Einfachste Methode)

1. **Login:** https://plugin.feg-aschaffenburg.de/wp-admin/
   - User: `naumann`
   
2. **Main Plugin Update:**
   - Plugins → Installierte Plugins → ChurchTools Suite
   - Falls Update-Button sichtbar: Direkt auf "Aktualisieren" klicken
   - Falls kein Update-Button: Deaktivieren → Plugins → Installieren → ZIP hochladen
   - Datei: `C:\privat\churchtools-suite-1.0.8.0.zip`
   - Aktivieren

3. **Demo Plugin Update:**
   - Plugins → Installierte Plugins → ChurchTools Suite Demo
   - Deaktivieren
   - Plugins → Installieren → ZIP hochladen
   - Datei: `C:\privat\churchtools-suite-demo-1.0.7.0.zip`
   - Aktivieren

4. **Migration prüfen:**
   - Admin → ChurchTools Suite → Erweitert → Übersicht
   - Suche nach "DB-Version: 1.2"
   - Falls Migration nicht lief: Deaktivieren/Reaktivieren Demo Plugin

---

### Schritt 2: SFTP Upload (Alternative)

Falls WordPress Admin Upload fehlschlägt:

```
FileZilla/WinSCP:
Host: plugin.feg-aschaffenburg.de
Port: 21 (SFTP) oder 22 (SFTP/SSH)
User: naumann

Upload-Pfade:
churchtools-suite-1.0.8.0.zip → ~/uploads/
churchtools-suite-demo-1.0.7.0.zip → ~/uploads/

Dann SSH:
cd ~/html/wp-content/plugins
unzip ~/uploads/churchtools-suite-1.0.8.0.zip
unzip ~/uploads/churchtools-suite-demo-1.0.7.0.zip
```

---

## ✅ Verifikation (SSH Commands)

**Voraussetzung:** SSH-Zugang verfügbar

### 1. Versionen prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp plugin list | grep churchtools"
```

**Erwartet:**
```
churchtools-suite      active   1.0.8.0
churchtools-suite-demo active   1.0.7.0
```

---

### 2. Factory prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"echo function_exists('churchtools_suite_get_repository') ? 'Factory: OK' : 'Factory: MISSING';\""
```

**Erwartet:** `Factory: OK`

---

### 3. Filter prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"echo has_filter('churchtools_suite_get_events_repository') ? 'Filters: OK' : 'Filters: MISSING';\""
```

**Erwartet:** `Filters: OK`

---

### 4. Demo-Tabellen prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"global \\\$wpdb; \\\$tables = ['demo_cts_events', 'demo_cts_calendars', 'demo_cts_services', 'demo_cts_event_services']; foreach (\\\$tables as \\\$t) { echo \\\$wpdb->prefix . \\\$t . ': ' . (\\\$wpdb->get_var(\\\"SHOW TABLES LIKE '\\\"\\\$wpdb->prefix.\\\$t.\\\"'\\\") ? 'EXISTS' : 'MISSING') . PHP_EOL; }\""
```

**Erwartet:**
```
wp_demo_cts_events: EXISTS
wp_demo_cts_calendars: EXISTS
wp_demo_cts_services: EXISTS
wp_demo_cts_event_services: EXISTS
```

---

### 5. Migration 1.2 Status prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp option get churchtools_suite_demo_db_version"
```

**Erwartet:** `1.2`

---

## 🧪 Funktionstest Live-Server

### Test 1: Neue Demo-Registrierung

1. **Browser öffnen:**
   - URL: https://plugin.feg-aschaffenburg.de/backend-demo/
   
2. **Registrierung:**
   - Email: `test-factory-live@example.com`
   - Name: `Factory Test Live`
   - Absenden
   
3. **Erwartetes Verhalten:**
   - ✅ Auto-Login funktioniert
   - ✅ Redirect zu ChurchTools Suite
   - ✅ 26 Demo-Events sichtbar
   
4. **SSH Verifikation:**
   ```bash
   # User-ID ermitteln
   ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp user list --role=cts_demo_user --field=ID"
   
   # Letzte User-ID nehmen (z.B. 15), dann:
   ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"global \\\$wpdb; echo \\\$wpdb->get_var('SELECT COUNT(*) FROM wp_demo_cts_events WHERE user_id = 15');\""
   ```
   
   **Erwartet:** `26` (oder ähnliche Zahl, je nach Demo-Daten)

---

### Test 2: Repository Override Verifikation

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"
wp_set_current_user(15); // Demo User ID
\\\$repo = churchtools_suite_get_repository('events');
echo get_class(\\\$repo);
\""
```

**Erwartet:** `ChurchTools_Suite_Demo_Events_Repository`

---

### Test 3: Admin User Check

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"
wp_set_current_user(1); // Admin
\\\$repo = churchtools_suite_get_repository('events');
echo get_class(\\\$repo);
\""
```

**Erwartet:** `ChurchTools_Suite_Events_Repository`

---

## 🔧 Troubleshooting

### Problem: Migration 1.2 nicht ausgeführt

**Symptom:** `wp option get churchtools_suite_demo_db_version` → `1.1`

**Lösung:**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp plugin deactivate churchtools-suite-demo && wp plugin activate churchtools-suite-demo"
```

---

### Problem: Factory-Filter nicht registriert

**Symptom:** `has_filter('churchtools_suite_get_events_repository')` → `false`

**Check 1: Main Plugin Version**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp plugin list | grep churchtools-suite"
```
- Muss v1.0.8.0 oder höher sein
- Falls älter: Main Plugin ZIP nochmal hochladen

**Check 2: Demo Plugin geladen**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"echo class_exists('ChurchTools_Suite_Demo') ? 'Demo Plugin: Loaded' : 'Demo Plugin: NOT LOADED';\""
```

**Check 3: Filter-Registration Log**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && tail -100 wp-content/debug.log | grep 'Repository Factory'"
```

---

### Problem: Demo User sieht keine Events

**Check 1: User Role**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp user meta get <user_id> wp_capabilities"
```
**Erwartet:** `a:1:{s:14:"cts_demo_user";b:1;}`

**Check 2: Demo Mode Meta**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp user meta get <user_id> cts_demo_mode"
```
**Erwartet:** `true`

**Check 3: Events in DB**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"global \\\$wpdb; echo \\\$wpdb->get_var('SELECT COUNT(*) FROM wp_demo_cts_events WHERE user_id = <user_id>');\""
```
**Erwartet:** `> 0`

**Fix: Data Import nachträglich ausführen**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"
require_once 'wp-content/plugins/churchtools-suite-demo/includes/services/class-demo-registration-service.php';
require_once 'wp-content/plugins/churchtools-suite-demo/includes/repositories/class-demo-users-repository.php';
\\\$repo = new ChurchTools_Suite_Demo_Users_Repository();
\\\$service = new ChurchTools_Suite_Demo_Registration_Service(\\\$repo);
\\\$service->import_demo_data(<user_id>);
echo 'Import complete';
\""
```

---

### Problem: WordPress Update erkennt v1.0.8.0 nicht

**Transient löschen:**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp transient delete --all"
```

**Update Check forcieren:**
```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"delete_site_transient('update_plugins'); wp_update_plugins();\""
```

---

## 🔄 Rollback (Falls nötig)

### Option A: ZIP Restore

```bash
# Backup erstellen (wenn noch nicht vorhanden)
ssh naumann@plugin.feg-aschaffenburg.de "cd html/wp-content/plugins && tar czf ~/backup-plugins-$(date +%Y%m%d).tar.gz churchtools-suite churchtools-suite-demo"

# Alte Version wiederherstellen
ssh naumann@plugin.feg-aschaffenburg.de "cd html/wp-content/plugins && rm -rf churchtools-suite churchtools-suite-demo && unzip ~/backup-churchtools-suite-1.0.7.1.zip && unzip ~/backup-churchtools-suite-demo-1.0.6.0.zip"
```

### Option B: Migration Rollback

```bash
# Migration-Version zurücksetzen
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp option update churchtools_suite_demo_db_version 1.1"

# Demo-Tabellen löschen
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"
global \\\$wpdb;
\\\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_events');
\\\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_calendars');
\\\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_services');
\\\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_event_services');
echo 'Demo tables dropped';
\""
```

---

## 📊 Post-Deployment Monitoring

### 1. Error Logs prüfen

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && tail -50 wp-content/debug.log"
```

**Achten auf:**
- ❌ `PHP Fatal error`
- ❌ `Repository Factory`
- ❌ `Demo Migrations`

---

### 2. Performance Check

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp eval \"
\\\$start = microtime(true);
for (\\\$i = 0; \\\$i < 1000; \\\$i++) {
    churchtools_suite_get_repository('events');
}
\\\$end = microtime(true);
echo 'Factory: ' . round((\\\$end - \\\$start) * 1000, 2) . 'ms for 1000 calls';
\""
```

**Erwartet:** `< 10ms` (Factory hat minimalen Overhead)

---

### 3. Auto-Update Check (nach 24h)

```bash
ssh naumann@plugin.feg-aschaffenburg.de "cd html && wp plugin update --dry-run churchtools-suite"
```

**Erwartet:** Falls v1.0.8.1 verfügbar → Update wird erkannt

---

## ✅ Deployment Checklist

- [ ] **Backup erstellt** (Plugins + Datenbank)
- [ ] **Main Plugin v1.0.8.0** hochgeladen & aktiviert
- [ ] **Demo Plugin v1.0.7.0** hochgeladen & aktiviert
- [ ] **Migration 1.2** ausgeführt (Demo-Tabellen existieren)
- [ ] **Factory verfügbar** (`function_exists` Check)
- [ ] **Filter registriert** (`has_filter` Check)
- [ ] **Neue Demo-Registrierung** getestet (26 Events isoliert)
- [ ] **Repository Override** verifiziert (get_class Check)
- [ ] **Error Logs** geprüft (keine Fatals)
- [ ] **WordPress Update System** funktioniert (Transient gelöscht)

---

## 📞 Support

Bei Problemen:
1. Error Logs prüfen: `tail -100 wp-content/debug.log`
2. Factory Status: `wp eval "var_dump(function_exists('churchtools_suite_get_repository'));"`
3. GitHub Issue: https://github.com/FEGAschaffenburg/churchtools-suite/issues

---

**Deployment-Guide erstellt:** 6. Februar 2026  
**Autor:** GitHub Copilot  
**Status:** Production Ready ✅
