# Deployment Guide: v1.0.8.0 + Demo v1.0.7.0

**Datum:** 6. Februar 2026  
**Main Plugin:** v1.0.8.0 (Repository Factory)  
**Demo Plugin:** v1.0.7.0 (Multi-User Isolation)

---

## 📦 Deployment-Pakete

### Main Plugin (v1.0.8.0)
**Datei:** `C:\privat\churchtools-suite-1.0.8.0.zip` (0.35 MB)  
**GitHub:** https://github.com/FEGAschaffenburg/churchtools-suite/releases/tag/v1.0.8.0

### Demo Plugin (v1.0.7.0)
**Datei:** `C:\privat\churchtools-suite-demo-1.0.7.0.zip`

---

## 🚀 Live-Server Deployment

### Voraussetzungen
- SSH/SFTP-Zugang zum Server
- WordPress Admin-Zugang
- Backup erstellt

### Schritt 1: Main Plugin aktualisieren

**Option A: Via WordPress Admin (empfohlen)**
1. WordPress Admin → Plugins
2. Suche nach Updates (Button "Nach Updates suchen")
3. "ChurchTools Suite" → Update auf v1.0.8.0
4. Automatischer Download von GitHub-Release

**Option B: Manueller Upload**
```bash
# Upload ZIP
scp C:\privat\churchtools-suite-1.0.8.0.zip user@server:~/uploads/

# SSH zum Server
ssh user@server

# Entpacken
cd html/wp-content/plugins
rm -rf churchtools-suite.bak
mv churchtools-suite churchtools-suite.bak
unzip ~/uploads/churchtools-suite-1.0.8.0.zip
```

### Schritt 2: Demo Plugin aktualisieren

```bash
# Upload Demo Plugin ZIP
scp C:\privat\churchtools-suite-demo-1.0.7.0.zip user@server:~/uploads/

# SSH zum Server
ssh user@server

# Backup erstellen
cd html/wp-content/plugins
cp -r churchtools-suite-demo churchtools-suite-demo.bak

# Entpacken (überschreibt bestehende Dateien)
unzip -o ~/uploads/churchtools-suite-demo-1.0.7.0.zip -d .

# Oder: Löschen + Neu
rm -rf churchtools-suite-demo
unzip ~/uploads/churchtools-suite-demo-1.0.7.0.zip
```

### Schritt 3: Migration ausführen

**Automatisch beim Plugin-Update:**
Die Demo-Plugin-Migration 1.2 läuft automatisch beim nächsten `init` Hook.

**Manuell prüfen:**
```bash
cd html
wp eval "require_once 'wp-content/plugins/churchtools-suite-demo/includes/class-demo-migrations.php'; ChurchTools_Suite_Demo_Migrations::run_migrations();"
```

**Verifizieren:**
```bash
wp eval "
global \$wpdb;
\$tables = ['demo_cts_events', 'demo_cts_calendars', 'demo_cts_services'];
foreach (\$tables as \$table) {
    \$full = \$wpdb->prefix . \$table;
    \$exists = \$wpdb->get_var(\"SHOW TABLES LIKE '\$full'\") === \$full;
    echo \"\$full: \" . (\$exists ? 'EXISTS' : 'MISSING') . \"\\n\";
}
"
```

Erwartete Ausgabe:
```
wp_demo_cts_events: EXISTS
wp_demo_cts_calendars: EXISTS
wp_demo_cts_services: EXISTS
```

### Schritt 4: Repository Factory testen

```bash
wp eval "
require_once 'wp-content/plugins/churchtools-suite/includes/functions/repository-factory.php';
if (function_exists('churchtools_suite_get_repository')) {
    echo 'Repository Factory: AVAILABLE\n';
    \$repo = churchtools_suite_get_repository('events');
    echo 'Default Repository: ' . get_class(\$repo) . '\n';
} else {
    echo 'Repository Factory: MISSING\n';
}
"
```

Erwartete Ausgabe:
```
Repository Factory: AVAILABLE
Default Repository: ChurchTools_Suite_Events_Repository
```

---

## ✅ Verifizierung

### 1. Main Plugin Version
WordPress Admin → Plugins → ChurchTools Suite  
**Erwartet:** v1.0.8.0

### 2. Demo Plugin Version
WordPress Admin → Plugins → ChurchTools Suite Demo  
**Erwartet:** v1.0.7.0

### 3. Factory-Filter registriert
```bash
wp eval "
\$filters = [
    'churchtools_suite_get_events_repository',
    'churchtools_suite_get_calendars_repository',
    'churchtools_suite_get_services_repository',
];
foreach (\$filters as \$filter) {
    \$has = has_filter(\$filter);
    echo \"\$filter: \" . (\$has ? 'REGISTERED' : 'MISSING') . \"\\n\";
}
"
```

Erwartete Ausgabe (wenn Demo-Plugin aktiv):
```
churchtools_suite_get_events_repository: REGISTERED
churchtools_suite_get_calendars_repository: REGISTERED
churchtools_suite_get_services_repository: REGISTERED
```

### 4. Neue Demo-Registrierung testen
1. Gehe zu Demo-Registrierungsseite
2. Registriere mit neuer E-Mail
3. Login als Demo-User
4. Prüfe: Events werden angezeigt (isolierte Daten)

```bash
# Via WP-CLI prüfen
wp eval "
\$demo_user_id = 123; // User-ID hier eintragen
global \$wpdb;
\$count = \$wpdb->get_var(\"SELECT COUNT(*) FROM wp_demo_cts_events WHERE user_id = \$demo_user_id\");
echo \"Demo User \$demo_user_id: \$count events\\n\";
"
```

---

## 🔧 Troubleshooting

### Problem: Migration läuft nicht
**Lösung:**
```bash
# Plugin deaktivieren/reaktivieren
wp plugin deactivate churchtools-suite-demo
wp plugin activate churchtools-suite-demo
```

### Problem: Keine Updates sichtbar
**Lösung:**
```bash
# Transient löschen
wp transient delete update_plugins

# Update-Check forcieren
wp eval "delete_site_transient('update_plugins'); wp_update_plugins(); echo 'Update check forced\n';"
```

### Problem: Factory-Filter nicht registriert
**Prüfen:**
- Main Plugin v1.0.8.0 installiert?
- Demo Plugin v1.0.7.0 installiert?
- Demo Plugin aktiviert?

**Lösung:**
```bash
# Plugins neu laden
wp plugin deactivate churchtools-suite-demo
wp plugin activate churchtools-suite-demo
```

### Problem: Demo-User sieht keine Events
**Debug:**
```bash
wp eval "
wp_set_current_user(123); // Demo User ID
\$repo = churchtools_suite_get_repository('events');
echo 'Repository Class: ' . get_class(\$repo) . '\n';
echo 'Expected: ChurchTools_Suite_Demo_Events_Repository\n';
"
```

Wenn falsche Klasse zurückgegeben wird:
- Factory-Filter nicht registriert → Demo-Plugin neu aktivieren
- User hat falsche Rolle → `wp user meta get 123 wp_capabilities` prüfen

---

## 📊 Post-Deployment Monitoring

### Logs prüfen
```bash
tail -f html/wp-content/debug.log | grep -i "demo\|factory\|repository"
```

### Performance-Check
```bash
# Anzahl Events pro User
wp eval "
global \$wpdb;
\$stats = \$wpdb->get_results('SELECT user_id, COUNT(*) as count FROM wp_demo_cts_events GROUP BY user_id');
foreach (\$stats as \$stat) {
    echo \"User {\$stat->user_id}: {\$stat->count} events\\n\";
}
"
```

### Auto-Update-Check
```bash
# Main Plugin Update-Info
wp eval "
require_once 'wp-content/plugins/churchtools-suite/includes/class-churchtools-suite-auto-updater.php';
\$info = ChurchTools_Suite_Auto_Updater::get_latest_release_info();
if (!is_wp_error(\$info)) {
    echo 'Latest: ' . \$info['latest_version'] . '\n';
    echo 'Current: ' . CHURCHTOOLS_SUITE_VERSION . '\n';
    echo 'Update: ' . (\$info['is_update'] ? 'YES' : 'NO') . '\n';
}
"
```

---

## 🎯 Rollback (bei Problemen)

### Main Plugin zurücksetzen
```bash
cd html/wp-content/plugins
rm -rf churchtools-suite
mv churchtools-suite.bak churchtools-suite
```

### Demo Plugin zurücksetzen
```bash
cd html/wp-content/plugins
rm -rf churchtools-suite-demo
mv churchtools-suite-demo.bak churchtools-suite-demo
```

### Migration zurückrollen
```bash
# DB-Version zurücksetzen
wp option update churchtools_suite_demo_db_version '1.1'

# Tabellen löschen (VORSICHT: Datenverlust!)
wp eval "
global \$wpdb;
\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_events');
\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_calendars');
\$wpdb->query('DROP TABLE IF EXISTS wp_demo_cts_services');
echo 'Demo tables dropped\n';
"
```

---

## 📝 Checkliste

- [ ] Backup erstellt (Dateien + Datenbank)
- [ ] Main Plugin v1.0.8.0 deployed
- [ ] Demo Plugin v1.0.7.0 deployed
- [ ] Migration 1.2 erfolgreich
- [ ] Demo-Tabellen existieren (mit user_id)
- [ ] Factory-Filter registriert
- [ ] Neue Demo-Registrierung getestet
- [ ] Demo-User sieht isolierte Events
- [ ] Admin sieht normale Events
- [ ] Logs geprüft (keine Errors)
- [ ] Performance OK

---

## 📞 Support

Bei Problemen:
1. Debug-Log prüfen: `wp-content/debug.log`
2. PHP-Errors prüfen: Server-Error-Log
3. Test-Script ausführen: `test-demo-factory.php`
4. Issue auf GitHub erstellen

**Wichtige Logs:**
- `[ChurchTools Demo]` - Demo-Plugin-Logs
- `[ChurchTools Suite]` - Main-Plugin-Logs
- `Factory` - Repository-Factory-Logs
