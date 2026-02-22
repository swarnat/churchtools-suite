<?php
/**
 * Query variable Class
 * 
 * Simple wrapper around the wordpress query variables
 * Only logs errors and warnings in production, all levels in WP_DEBUG mode.
 *
 * @package ChurchTools_Suite
 * @since   0.3.13.3
 * @version 1.1.4.2 Simplified to use WordPress error_log()
 */

if (!defined('ABSPATH')) {
    exit;
}

class ChurchTools_Suite_Queryvars {

    public static function filter($vars) {
        # Event Search Shortcode
        $vars[] = "event-search";

        return $vars;
    }

    public static function get($key, $default = null) {
        return get_query_var( $key, $default);
    }

    public static function getEventSearchQuery(): string {
        $query_variable = self::get("event-search", "");

        return sanitize_text_field($query_variable);
    }
        
}
