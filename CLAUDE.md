# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

WordPress plugin monorepo that integrates ChurchTools (a church management SaaS) with WordPress. Syncs events, calendars, and services from the ChurchTools API into WordPress for frontend display.

**Monorepo structure:**
- `churchtools-suite.php` — Main plugin entry point (v1.2.x)
- `addons/churchtools-suite-elementor/` — Elementor widget addon
- `addons/churchtools-suite-posts-sync/` — Syncs ChurchTools events to WordPress posts
- `addons/churchtools-suite-presentations/` — Presentations addon
- `docs` - Documentation for you

**Requirements:** WordPress >= 6.0, PHP >= 8.0

## Syntax Check

```bash
# Check all PHP files for syntax errors
find . -name "*.php" | xargs php -l 2>&1 | grep -v "No syntax errors"
```

No automated test suite exists. Manual testing is done by installing the plugin in a local WordPress and using the Admin → Debug tab.

## Build & Deploy

All build scripts are PowerShell (Windows):

```powershell
# Create WordPress-compatible ZIP for release
cd scripts
.\create-wp-zip.ps1 -Version "1.2.1.4" -Plugin main
.\create-wp-zip.ps1 -Version "0.6.14" -Plugin elementor
.\create-wp-zip.ps1 -Version "0.1.4" -Plugin posts-sync
```

Releases are published to GitHub (`FEGAschaffenburg/churchtools-suite`). The auto-update mechanism checks GitHub releases — all plugin ZIPs must be attached to the same release for the updater to detect new versions.

## Architecture

### Boot Sequence
`churchtools-suite.php` → defines constants → loads `repository-factory.php` → instantiates `ChurchTools_Suite` → migrations run → hooks registered via `ChurchTools_Suite_Loader`.

### Repository Pattern
All DB access goes through repositories extending `ChurchTools_Suite_Repository_Base`. The factory function `churchtools_suite_get_repository('events')` is the preferred way to instantiate repositories. Available keys: `events`, `calendars`, `event_services`, `services`, `service_groups`, `shortcode_presets`.

Tables use the prefix `cts_` (constant `CHURCHTOOLS_SUITE_DB_PREFIX`).

### Database Migrations
`ChurchTools_Suite_Migrations` runs on every plugin init, gated by version stored in `wp_options['churchtools_suite_db_version']`. Current DB version: `1.5`.

To add a migration:
1. Increment `DB_VERSION` constant
2. Add a method `migrate_to_{version_with_underscores}()`
3. Migrations must be idempotent

### ChurchTools API Client
`ChurchTools_Suite_CT_Client` uses **cookie-based** auth (not tokens):
1. Login via `/api/login` → cookies stored in `wp_options['churchtools_suite_ct_cookies']`
2. All subsequent calls include stored cookies
3. Session keep-alive runs hourly via WP-Cron hook `churchtools_suite_session_keepalive`

### Event Sync (2-Phase)
`ChurchTools_Suite_Event_Sync_Service` runs two API calls per sync:
- **Phase 1** — `/api/events?from=...&to=...` — Events with appointments (1:N relationship)
- **Phase 2** — `/api/calendars/{id}/appointments?from=...&to=...` — Standalone appointments

`appointment_id` is tracked across both phases to prevent duplicates. Sync range is configurable via `churchtools_suite_sync_days_past` (default 7) and `churchtools_suite_sync_days_future` (default 90).

### Frontend Display
Events are rendered through a template system with theme override support. Templates live in `templates/views/{view-type}/{template-name}.php`. Themes can override any template by placing files under `{theme}/churchtools-suite/`.

View types: `list`, `grid`, `calendar`, `countdown`, `carousel`, `modal`, `single`, `search`

**Shortcodes:**
- `[churchtools_events]` — Generic router, dispatches via `viewType` parameter
- `[cts_list]`, `[cts_grid]`, `[cts_calendar]`, `[cts_countdown]`, `[cts_carousel]`, `[cts_event_search]`

Shortcodes support named presets (stored via `shortcode_presets` repository).

### Admin Interface
Tab-based UI: Dashboard, Settings, Calendars, Events, Sync, Debug. Views in `admin/views/tab-*.php`. AJAX handlers are registered in `register_ajax_handlers()` — all prefixed `cts_` and require nonce `churchtools_suite_admin` + `manage_options` capability.

### Versioning Convention
Format: `Major.Minor.Patch.Build` — update in **both** the plugin file header comment AND the `CHURCHTOOLS_SUITE_VERSION` constant definition.

## Debugging

Enable in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Logs: `wp-content/debug.log` and Admin → Debug tab (shows sync stats and raw API responses).
