<?php
/**
 * Repository Factory
 *
 * Central factory for creating repository instances.
 * Allows plugins to override repositories via filters (e.g., Demo Plugin, Cache Plugin, etc.)
 *
 * @package ChurchTools_Suite
 * @since   1.0.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get repository instance
 *
 * Factory function for creating repository instances.
 * Allows plugins to override repositories via filters.
 *
 * Example usage:
 * ```php
 * $events_repo = churchtools_suite_get_repository( 'events' );
 * $events = $events_repo->get_all();
 * ```
 *
 * Example filter (in plugin):
 * ```php
 * add_filter( 'churchtools_suite_get_events_repository', function( $repo, $user_id ) {
 *     if ( is_demo_user( $user_id ) ) {
 *         return new Demo_Events_Repository( $user_id );
 *     }
 *     return $repo;
 * }, 10, 2 );
 * ```
 *
 * @param string   $type    Repository type: 'events', 'calendars', 'services', 'event_services', 'service_groups', 'views', 'shortcode_presets', 'sync_history'
 * @param int|null $user_id Optional user ID for multi-user support (default: current user)
 * @return object|null Repository instance or null if type invalid
 *
 * @since 1.0.8.0
 */
function churchtools_suite_get_repository( string $type, $user_id = null ) {
	// Default to current user if not specified
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	
	// Allow plugins to override (BEFORE default creation)
	// This is the key hook for Demo Plugin, Cache Plugin, etc.
	$custom_repo = apply_filters(
		"churchtools_suite_get_{$type}_repository",
		null,
		$user_id
	);
	
	// If plugin returned custom repository, use it
	if ( $custom_repo ) {
		return $custom_repo;
	}
	
	// Load and return default repository
	switch ( $type ) {
		case 'events':
			if ( ! class_exists( 'ChurchTools_Suite_Events_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-events-repository.php';
			}
			return new ChurchTools_Suite_Events_Repository();
			
		case 'calendars':
			if ( ! class_exists( 'ChurchTools_Suite_Calendars_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-calendars-repository.php';
			}
			return new ChurchTools_Suite_Calendars_Repository();
			
		case 'services':
			if ( ! class_exists( 'ChurchTools_Suite_Services_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-services-repository.php';
			}
			return new ChurchTools_Suite_Services_Repository();
			
		case 'event_services':
			if ( ! class_exists( 'ChurchTools_Suite_Event_Services_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-event-services-repository.php';
			}
			return new ChurchTools_Suite_Event_Services_Repository();
			
		case 'service_groups':
			if ( ! class_exists( 'ChurchTools_Suite_Service_Groups_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-service-groups-repository.php';
			}
			return new ChurchTools_Suite_Service_Groups_Repository();
			
		case 'views':
			if ( ! class_exists( 'ChurchTools_Suite_Views_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-views-repository.php';
			}
			return new ChurchTools_Suite_Views_Repository();
			
		case 'shortcode_presets':
			if ( ! class_exists( 'ChurchTools_Suite_Shortcode_Presets_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-shortcode-presets-repository.php';
			}
			return new ChurchTools_Suite_Shortcode_Presets_Repository();
			
		case 'sync_history':
			if ( ! class_exists( 'ChurchTools_Suite_Sync_History_Repository' ) ) {
				require_once CHURCHTOOLS_SUITE_PATH . 'includes/repositories/class-churchtools-suite-sync-history-repository.php';
			}
			return new ChurchTools_Suite_Sync_History_Repository();
			
		default:
			// Allow plugins to register custom repository types
			$custom_type_repo = apply_filters(
				'churchtools_suite_get_custom_repository',
				null,
				$type,
				$user_id
			);
			
			if ( $custom_type_repo ) {
				return $custom_type_repo;
			}
			
			// Invalid type
			return null;
	}
}

/**
 * Backward compatibility wrapper (optional)
 *
 * For legacy code that might check for repository existence.
 * Allows gradual migration to factory pattern.
 *
 * @param string $type Repository type
 * @return bool
 *
 * @since 1.0.8.0
 */
function churchtools_suite_has_repository( string $type ): bool {
	$valid_types = [
		'events',
		'calendars',
		'services',
		'event_services',
		'service_groups',
		'views',
		'shortcode_presets',
		'sync_history',
	];
	
	return in_array( $type, $valid_types, true );
}
