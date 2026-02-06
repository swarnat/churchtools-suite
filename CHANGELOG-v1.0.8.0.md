# Changelog v1.0.8.0

**Release Date:** 6. Februar 2026  
**Type:** Feature Release (Repository Factory Architecture)

---

## 🏭 Major Feature: Repository Factory Pattern

### What is it?
Introduced central **Repository Factory** function `churchtools_suite_get_repository()` that allows plugins to override data access layers without modifying core code.

### Why is this important?
- ✅ **Extensibility:** Enables add-on plugins (Demo, Cache, Multi-Tenancy, etc.)
- ✅ **Multi-User Support:** Demo Plugin can now provide isolated data per user
- ✅ **Future-Proof:** PageBuilder isolation, caching plugins, A/B testing possible
- ✅ **Clean Architecture:** Separation of concerns, testable code

### Implementation Details

**New Factory Function:**
```php
churchtools_suite_get_repository( 'events' );
churchtools_suite_get_repository( 'calendars' );
churchtools_suite_get_repository( 'services' );
// ... etc
```

**Filter Hooks for Plugins:**
```php
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    if ( is_demo_user( $user_id ) ) {
        return new Custom_Events_Repository( $user_id );
    }
    return $repo;
}, 10, 2 );
```

**Supported Repository Types:**
- `events` - ChurchTools_Suite_Events_Repository
- `calendars` - ChurchTools_Suite_Calendars_Repository
- `services` - ChurchTools_Suite_Services_Repository
- `event_services` - ChurchTools_Suite_Event_Services_Repository
- `service_groups` - ChurchTools_Suite_Service_Groups_Repository
- `views` - ChurchTools_Suite_Views_Repository
- `shortcode_presets` - ChurchTools_Suite_Shortcode_Presets_Repository
- `sync_history` - ChurchTools_Suite_Sync_History_Repository

---

## 🔄 Changes

### Added
- **NEW FILE:** `includes/functions/repository-factory.php` - Central factory with filter support
- **NEW CONSTANT:** Factory loaded in main plugin file after constants

### Modified
- `includes/services/class-churchtools-suite-template-data.php` - Use factory in constructor
- `includes/shortcodes/class-churchtools-suite-single-event-shortcode.php` - Use factory
- `templates/views/event-single/minimal.php` - Use factory
- `includes/elementor/class-churchtools-suite-elementor-events-widget.php` - Use factory
- `includes/class-churchtools-suite-shortcodes.php` - Use factory (2 locations)

### Removed
- **Manual require_once statements** - Factory handles autoloading

---

## 🎯 Use Cases

### 1. **Demo Plugin (Immediate)**
Demo users get isolated data:
```php
// Demo Plugin registers filter
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    if ( current_user_can( 'cts_demo_user' ) ) {
        return new Demo_Events_Repository( $user_id );
    }
    return $repo;
}, 10, 2 );

// Result: Demo users see only their own events from demo_cts_events table
```

### 2. **Caching Plugin (Future)**
```php
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    return new Cached_Repository_Wrapper( $repo, 3600 ); // 1h cache
}, 10, 2 );
```

### 3. **PageBuilder Isolation (Future)**
```php
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    if ( is_elementor_editor() && ! current_user_can( 'manage_options' ) ) {
        return new Editor_Isolated_Repository( $user_id );
    }
    return $repo;
}, 10, 2 );
```

### 4. **Multi-Tenancy (Future)**
```php
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    $site_id = get_current_blog_id();
    return new Multisite_Repository( $site_id, $user_id );
}, 10, 2 );
```

---

## ⚙️ Backward Compatibility

✅ **100% Backward Compatible**
- All existing functionality unchanged
- No breaking changes
- Old code continues to work
- Templates/Views unchanged

---

## 📊 Statistics

- **Files Modified:** 7
- **New Files:** 1 (repository-factory.php)
- **Lines Changed:** ~50
- **Filter Hooks Added:** 8+ (one per repository type)
- **Breaking Changes:** None

---

## 🚀 Upgrade Instructions

### Automatic Update
Plugin updates automatically via built-in updater.

### Manual Update
1. Backup current plugin
2. Replace plugin files
3. No database migrations required
4. No settings changes needed

### For Add-On Developers
To create compatible add-ons:

```php
// Your plugin: churchtools-suite-myaddon.php
add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
    // Your custom logic
    return new Your_Custom_Repository( $user_id );
}, 10, 2 );
```

---

## 🔧 Technical Details

### Factory Implementation
```php
function churchtools_suite_get_repository( string $type, $user_id = null ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }
    
    // Allow plugins to override
    $custom_repo = apply_filters(
        "churchtools_suite_get_{$type}_repository",
        null,
        $user_id
    );
    
    if ( $custom_repo ) {
        return $custom_repo;
    }
    
    // Return default repository
    switch ( $type ) {
        case 'events':
            return new ChurchTools_Suite_Events_Repository();
        // ... etc
    }
}
```

### Filter Priority
Multiple plugins can stack filters:
```php
// Demo Plugin (priority 10)
add_filter( 'churchtools_suite_get_events_repository', 'demo_override', 10, 2 );

// Cache Plugin (priority 20)
add_filter( 'churchtools_suite_get_events_repository', 'cache_wrapper', 20, 2 );

// Result: Cache wraps Demo repository
```

---

## 📖 Documentation

**For Developers:**
- See `includes/functions/repository-factory.php` for complete API
- See `ROADMAP.md` for future add-on possibilities
- Filter hooks documented in code with examples

**For Users:**
- No changes to user experience
- Demo Plugin will utilize this in upcoming v1.0.7.0

---

## 🎯 Next Steps

### Demo Plugin (v1.0.7.0)
Will implement:
- Multi-user data isolation
- Separate `wp_demo_cts_events` tables
- Per-user demo data

### Cache Plugin (Future)
Potential add-on for:
- Redis/Memcached integration
- Query result caching
- Performance optimization

### PageBuilder Add-Ons (Future)
- Elementor isolation
- Divi integration
- Beaver Builder support

---

## 👥 Contributors

**Developed by:** FEG Aschaffenburg Development Team  
**Architecture:** Factory Pattern, Filter-based extensibility  
**Testing:** Backward compatibility verified

---

**Full Changelog:** https://github.com/FEGAschaffenburg/churchtools-suite/releases  
**Upgrade from:** v1.0.7.1 → v1.0.8.0
