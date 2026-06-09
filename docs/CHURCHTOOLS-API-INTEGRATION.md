# ChurchTools API Integration

Reference for implementing new API endpoints, sync modules, and data pipelines within the ChurchTools Suite plugin.

---

## Table of Contents

1. [Connection & Authentication](#1-connection--authentication)
2. [Making API Requests](#2-making-api-requests)
3. [Endpoints in Use](#3-endpoints-in-use)
4. [Sync Architecture](#4-sync-architecture)
5. [Implementing a New Sync](#5-implementing-a-new-sync)
6. [Rate Limiting](#6-rate-limiting)
7. [WP Options Reference](#7-wp-options-reference)
8. [WP-Cron Hooks](#8-wp-cron-hooks)

---

## 1. Connection & Authentication

**Class:** `ChurchTools_Suite_CT_Client`
**File:** `includes/class-churchtools-suite-ct-client.php`

The client is instantiated without arguments — it reads all credentials from `wp_options` in the constructor.

```php
$client = new ChurchTools_Suite_CT_Client();
```

### Auth Modes

Two modes are supported, configured via `churchtools_suite_ct_auth_method`:

| Mode | Value | Mechanism |
|---|---|---|
| Password / Cookie | `password` | POST `/api/login` → session cookies stored in `wp_options` |
| API Token | `token` | `Authorization: Bearer <token>` header on every request |

### Password Mode Flow

1. `login()` POSTs `{username, password}` to `/api/login`
2. On HTTP 200 + `data.status === 'success'`, extracts `Set-Cookie` response headers
3. Cookies stored as array in `wp_options['churchtools_suite_ct_cookies']`
4. Format per cookie entry: `{name, value, expires (unix timestamp), path, domain}`
5. `is_authenticated()` checks that no stored cookie has expired

### Token Mode Flow

1. No login request needed — `login()` returns success immediately
2. Every `api_request()` call sets `Authorization: Bearer <token>` header
3. `is_authenticated()` returns `true` if token and URL options are non-empty

### Key Methods

| Method | Purpose |
|---|---|
| `login()` | Establish session (password) or validate config (token). Returns `['success' => bool, 'message' => string]` |
| `test_connection()` | Calls `login()` then `GET /api/whoami`. Saves user info to options. |
| `api_request($endpoint, $method, $data)` | All API calls go through here — see section 2 |
| `is_authenticated()` | Returns bool; checks cookie expiry in password mode |
| `keepalive()` | Calls `GET /api/whoami` to keep session alive; re-logs in if needed |
| `logout()` | Clears cookies and user info from options |

---

## 2. Making API Requests

All requests go through `ChurchTools_Suite_CT_Client::api_request()`.

```php
$client = new ChurchTools_Suite_CT_Client();
$result = $client->api_request('endpoint/path', 'GET', $params);

if (is_wp_error($result)) {
    // Handle error — $result->get_error_message()
    return;
}

$items = $result['data'] ?? [];
```

### Signature

```php
api_request(
    string $endpoint,  // Relative to /api/ — e.g., 'events', 'calendars/5/appointments'
    string $method,    // 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE'
    array  $data       // GET: query params; POST/PUT/PATCH: request body
): array|WP_Error
```

### Query String Handling

For GET requests, `$data` is converted to a query string. Arrays are expanded to repeated `key[]=value` notation:

```php
// This:
$client->api_request('events', 'GET', [
    'from'    => '2025-01-01',
    'to'      => '2025-12-31',
    'include' => ['tags', 'eventServices'],
]);

// Produces: /api/events?from=2025-01-01&to=2025-12-31&include[]=tags&include[]=eventServices
```

### Error Handling

`api_request()` returns `WP_Error` on:
- Rate limit exceeded (`rate_limit_exceeded`)
- Missing credentials (`missing_token`, `no_auth`)
- Network error (WP_Error from `wp_remote_request`)
- Empty/HTML response — HTML detection catches broken sessions returning login pages
- HTTP 4xx / 5xx (after one retry-after-relogin attempt for 401 in password mode)
- JSON decode failure

Always check `is_wp_error($result)` before using the return value.

### Response Structure

ChurchTools API responses follow this envelope:

```json
{
    "data": [ ... ],
    "meta": { "count": 42, "total": 100 },
    "pagination": { ... }
}
```

`api_request()` returns the decoded array including the outer envelope. Access data via `$result['data']`.

---

## 3. Endpoints in Use

Base URL pattern: `{churchtools_suite_ct_url}/api/{endpoint}`

### Authentication

| Endpoint | Method | Purpose | Used By |
|---|---|---|---|
| `/api/login` | POST | Password login | `CT_Client::login()` |
| `/api/whoami` | GET | Validate session / test connection | `CT_Client::test_connection()`, `keepalive()` |

### Calendars

| Endpoint | Method | Params | Purpose |
|---|---|---|---|
| `/api/calendars` | GET | — | Fetch all calendars (id, name, color, sortKey, isPublic) |
| `/api/calendars/{id}/appointments` | GET | `from`, `to`, `include[]` | Phase 2 of event sync — standalone appointments |

**Used includes for appointments:**
```php
'include' => ['tags', 'bookings', 'meetingRequests', 'titleSuffix']
```

### Events

| Endpoint | Method | Params | Purpose |
|---|---|---|---|
| `/api/events` | GET | `from`, `to`, `include[]`, `modified_after` | Phase 1 — events with their appointments |

**Used includes for events:**
```php
'include' => ['eventServices']
```
`modified_after` (ISO 8601 datetime) enables incremental sync.

### Services

| Endpoint | Method | Params | Purpose |
|---|---|---|---|
| `/api/servicegroups` | GET | — | Fetch all service groups |
| `/api/services` | GET | `serviceGroupId` | Fetch services, optionally filtered by group |

---

## 4. Sync Architecture

### Component Overview

```
WordPress Cron / Admin AJAX
         │
         ▼
ChurchTools_Suite_Sync_Runtime      ← Lock management, status tracking
         │
         ▼
[Sync Service]                      ← Business logic per data type
  ├─ Event_Sync_Service             (Phase 1 + Phase 2 + deletion)
  ├─ Calendar_Sync_Service
  └─ Service_Sync_Service
         │
         ▼
ChurchTools_Suite_CT_Client         ← HTTP + auth
         │
         ▼
ChurchTools API
```

### Lock Mechanism

`ChurchTools_Suite_Sync_Runtime` prevents concurrent syncs using WordPress transients.

```php
$runtime = new ChurchTools_Suite_Sync_Runtime();

$lock = $runtime->acquire_lock('events', 'sync', 300); // 300s timeout
if (!$lock) {
    return; // Another sync is running
}

try {
    // ... do sync ...
    $runtime->record_result('events', ['status' => 'success', ...]);
} catch (Exception $e) {
    $runtime->record_result('events', ['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    $runtime->release_lock('events', 'sync');
}
```

Lock key pattern: `cts_lock_module_{module_id}_{action}` (WordPress transient)

### Module Registration

New sync modules register via the `cts_register_sync_modules` filter:

```php
add_filter('cts_register_sync_modules', function($modules) {
    $modules['my_module'] = [
        'id'           => 'my_module',
        'label'        => 'My Module',
        'capability'   => 'manage_options',
        'dependencies' => ['events'],     // optional
        'callbacks'    => [
            'sync'   => 'my_sync_function',
            'status' => 'my_status_function',
        ],
    ];
    return $modules;
});
```

### Event Sync (3 Phases)

**Class:** `ChurchTools_Suite_Event_Sync_Service`
**File:** `includes/services/class-churchtools-suite-event-sync-service.php`

**Phase 1 — Events API:**
- Calls `GET /api/events?from=...&to=...&include[]=eventServices`
- Processes each event and its nested `appointments` array
- Upserts by composite key `appointment_id|start_datetime`
- Imports event images as WP media attachments
- Extracts `eventServices` (people assigned to service roles)

**Phase 2 — Appointments API:**
- Calls `GET /api/calendars/{id}/appointments` for each selected calendar
- Picks up standalone appointments not attached to an event
- Skips appointment IDs already processed in Phase 1
- Enriches with `bookings` (resource/room addresses + geo coords)
- Tags are on the outer `appointment_data` level — **not** nested

**Phase 3 — Deletion:**
- Compares the set of `appointment_id|start_datetime` keys returned by the API against the local DB
- Deletes rows whose keys were not in either phase's result set
- Phase 3 is skipped entirely if Phase 2 failed (prevents false-positive deletes during API outages)

### Composite Key

Events are stored with a composite natural key: `appointment_id` + `start_datetime`. This handles recurring appointments — the same `appointment_id` can appear multiple times at different start times.

Upsert method: `ChurchTools_Suite_Events_Repository::upsert_by_appointment_id()`

---

## 5. Implementing a New Sync

### Step 1 — Create a Service Class

```php
// includes/services/class-churchtools-suite-mydata-sync-service.php

class ChurchTools_Suite_MyData_Sync_Service {

    private ChurchTools_Suite_CT_Client $client;
    private ChurchTools_Suite_MyData_Repository $repo;

    public function __construct() {
        $this->client = new ChurchTools_Suite_CT_Client();
        $this->repo   = churchtools_suite_get_repository('my_data'); // register in factory first
    }

    public function sync(): array {
        $result = $this->client->api_request('my-endpoint', 'GET', [
            'param1' => 'value1',
        ]);

        if (is_wp_error($result)) {
            return ['status' => 'error', 'message' => $result->get_error_message()];
        }

        $items = $result['data'] ?? [];
        $count = 0;

        foreach ($items as $item) {
            $this->repo->upsert($item);
            $count++;
        }

        return ['status' => 'success', 'count' => $count];
    }
}
```

### Step 2 — Create a Repository

```php
// includes/repositories/class-churchtools-suite-mydata-repository.php

class ChurchTools_Suite_MyData_Repository extends ChurchTools_Suite_Repository_Base {

    public function __construct() {
        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'my_data';
    }

    public function upsert(array $data): int|false {
        $existing = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table} WHERE external_id = %d",
                $data['id']
            )
        );

        $row = [
            'external_id' => $data['id'],
            'name'        => $data['name'] ?? '',
            'updated_at'  => current_time('mysql'),
        ];

        if ($existing) {
            $this->wpdb->update($this->table, $row, ['id' => $existing]);
            return $existing;
        }

        $row['created_at'] = current_time('mysql');
        $this->wpdb->insert($this->table, $row);
        return $this->wpdb->insert_id;
    }
}
```

### Step 3 — Register in Repository Factory

In `includes/functions/repository-factory.php`, add to `$repository_map`:

```php
'my_data' => 'ChurchTools_Suite_MyData_Repository',
```

### Step 4 — Add DB Migration

In `includes/class-churchtools-suite-migrations.php`:

1. Increment `const DB_VERSION` (e.g., `'1.5'` → `'1.6'`)
2. Add the migration method:

```php
private static function migrate_to_1_6(): void {
    global $wpdb;
    $table = $wpdb->prefix . CHURCHTOOLS_SUITE_DB_PREFIX . 'my_data';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS {$table} (
        id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
        external_id INT UNSIGNED NOT NULL,
        name        VARCHAR(255) NOT NULL DEFAULT '',
        created_at  DATETIME     NOT NULL,
        updated_at  DATETIME     NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY uq_external_id (external_id)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
```

3. Add the call inside `run_migrations()` in the version dispatch block.

> Migrations **must be idempotent** — `dbDelta()` and `CREATE TABLE IF NOT EXISTS` satisfy this for table creation.

### Step 5 — Wire to Cron / AJAX (optional)

**For AJAX**, add to `admin/class-churchtools-suite-admin.php`:

```php
// In register_ajax_handlers():
add_action('wp_ajax_cts_sync_mydata', [$this, 'ajax_sync_mydata']);

// Handler method:
public function ajax_sync_mydata(): void {
    check_ajax_referer('churchtools_suite_admin', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    $service = new ChurchTools_Suite_MyData_Sync_Service();
    $result  = $service->sync();

    if ($result['status'] === 'error') {
        wp_send_json_error($result);
    }
    wp_send_json_success($result);
}
```

**For cron**, hook into the existing `churchtools_suite_auto_sync` action in `class-churchtools-suite-cron.php` or register the module via `cts_register_sync_modules` filter.

---

## 6. Rate Limiting

**Class:** `ChurchTools_Suite_Rate_Limiter`
**File:** `includes/class-churchtools-suite-rate-limiter.php`

Applied automatically inside `CT_Client::api_request()` — you do not need to call it directly in sync services.

| Limit | Value |
|---|---|
| Per minute | 60 requests |
| Per hour | 1000 requests |
| Implementation | Sliding window via WordPress transients |
| Bypass | `localhost` or when `WP_DEBUG` is `true` |

If exceeded, `api_request()` returns `WP_Error('rate_limit_exceeded', ...)`.

---

## 7. WP Options Reference

All options use `get_option()` / `update_option()`.

| Option Key | Type | Description |
|---|---|---|
| `churchtools_suite_ct_url` | string | Base URL of the ChurchTools installation, e.g. `https://church.ctapp.io` |
| `churchtools_suite_ct_auth_method` | string | `'password'` or `'token'` |
| `churchtools_suite_ct_username` | string | Login email (password mode) |
| `churchtools_suite_ct_password` | string | Login password (password mode) |
| `churchtools_suite_ct_token` | string | API bearer token (token mode) |
| `churchtools_suite_ct_cookies` | array | Stored session cookies (password mode). Array of `{name, value, expires, path, domain}` |
| `churchtools_suite_ct_person_id` | int | Authenticated person's ChurchTools ID |
| `churchtools_suite_ct_user_info` | array | `/api/whoami` response data |
| `churchtools_suite_ct_last_login` | string | MySQL datetime of last successful login |
| `churchtools_suite_last_keepalive` | string | MySQL datetime of last keepalive ping |
| `churchtools_suite_db_version` | string | Installed DB schema version (e.g. `'1.5'`) |
| `churchtools_suite_sync_days_past` | int | Days before today to include in event sync (default: 7) |
| `churchtools_suite_sync_days_future` | int | Days after today to include in event sync (default: 90) |
| `churchtools_suite_module_{id}_status` | array | Per-module sync status: `{state, last_source_sync_at, last_result, ...}` |

---

## 8. WP-Cron Hooks

| Hook | Schedule | Description |
|---|---|---|
| `churchtools_suite_session_keepalive` | Dynamic (based on cookie expiry − 5 min, fallback hourly) | Pings `/api/whoami`; reschedules itself after each run |
| `churchtools_suite_auto_sync` | Configurable: hourly / twicedaily / daily / cts_2days / cts_3days / cts_weekly / cts_2weeks / cts_monthly | Full event sync pipeline. Fires `cts_do_sync_posts` action after completion for addons. |

### Custom Intervals Registered

| Interval ID | Period |
|---|---|
| `cts_2days` | 48 hours |
| `cts_3days` | 72 hours |
| `cts_weekly` | 7 days |
| `cts_2weeks` | 14 days |
| `cts_monthly` | 30 days |

### Addon Integration Hook

After every auto-sync the `cts_do_sync_posts` action fires, allowing addons to react:

```php
add_action('cts_do_sync_posts', function() {
    // Runs after the main event sync completes
    // Example: Posts Sync addon picks this up to update WP posts
});
```
