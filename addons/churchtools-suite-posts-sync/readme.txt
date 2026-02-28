=== ChurchTools Suite – Posts Sync ===
Contributors: FEG Aschaffenburg
Tags: churchtools, sync, posts, events, integration
Requires at least: 5.0
Requires PHP: 8.0
Tested up to: 6.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Synchronisiert ChurchTools-Posts in WordPress-Posts und -Seiten.

== Description ==

**ChurchTools Suite – Posts Sync** ist ein Addon für das ChurchTools Suite Plugin. Es ermöglicht die automatische Synchronisation von ChurchTools-Posts in WordPress-Posts oder -Seiten.

**Anforderungen:**
- ChurchTools Suite v1.1.5.0 oder höher
- WordPress 5.0 oder höher
- PHP 8.0 oder höher

**Features:**
- 🔄 Automatische Synchronisation von ChurchTools-Posts
- 📝 Konfigurierbar: Posts oder Seiten
- 📌 Flexible Veröffentlichungsstatus: Entwurf, Veröffentlicht, Privat
- 🔐 Intelligente Änderungserkennung (verhindert unnötige Updates)
- 📊 Sync-Statistiken im Admin-Dashboard

== Installation ==

1. Installieren und aktivieren Sie da Hauptplugin **ChurchTools Suite** (v1.1.5.0+)
2. Laden Sie dieses Addon hoch: `/wp-content/plugins/churchtools-suite-posts-sync/`
3. Aktivieren Sie das Addon im Admin-Bereich
4. Gehen Sie zu **Einstellungen → ChurchTools** und konfigurieren Sie **ChurchTools Posts → WordPress**

== Configuration ==

Nach der Aktivierung können Sie unter **Einstellungen → ChurchTools → Sync** folgende Optionen konfigurieren:

- **Posts-Sync aktivieren**: Schaltet die ChurchTools-Posts-Synchronisation ein/aus
- **Ziel in WordPress**: Bestimmen Sie, ob Posts oder Seiten erstellt werden sollen
- **Status der Zielinhalte**: Wählen Sie den Veröffentlichungsstatus (Entwurf, Veröffentlicht, Privat)

Die Synchronisation läuft automatisch mit der Häufigkeit, die für die Event-Synchronisation konfiguriert ist.

== FAQ ==

**Funktioniert dieses Addon ohne das Hauptplugin?**
Nein, dieses Addon erfordert das ChurchTools Suite Hauptplugin v1.1.5.0 oder höher.

**Kann ich die Felder (Title, Content, etc.) anpassen?**
Derzeit werden folgende Felder synchronisiert:
- Titel
- Inhaltstext
- Auszug
- Slug
- Veröffentlichungsdatum

Weitere Anpassungen sind in zukünftigen Versionen geplant.

**Werden Bilder/Medien synchronisiert?**
Nein, derzeit werden nur Text-Inhalte synchronisiert. Bilder-Support ist für eine zukünftige Version geplant.

== Changelog ==

= 0.1.0 =
- Initiale Release
- Grundlegende Posts-Sync-Funktionalität
- Konfigurierbare Zieltypen und Status

== Support ==

Für Support und Fehlerberichte besuchen Sie: https://github.com/FEGAschaffenburg/churchtools-suite/issues
