<?php
/**
 * Views Registry
 * 
 * Zentrale Verwaltung aller verfügbaren Views für:
 * - Shortcodes
 * - Gutenberg Blocks
 * - Widgets
 * - Admin-Einstellungen
 *
 * @package ChurchTools_Suite
 * @since   1.0.6.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ChurchTools_Suite_Views_Registry {
	
	/**
	 * List views (für [cts_list] shortcode)
	 * 
	 * @var array
	 */
	private static $list_views = [
		'classic' => [
			'label' => 'Klassisch',
			'description' => 'Klassische Listenansicht mit Event-Details',
		],
		'minimal' => [
			'label' => 'Minimal',
			'description' => 'Minimale Listenansicht nur mit Titel und Datum',
		],
		'modern' => [
			'label' => 'Modern',
			'description' => 'Moderne Listenansicht mit visuellen Elementen',
		],
		'classic-with-images' => [
			'label' => 'Klassisch mit Bildern',
			'description' => 'Klassische Ansicht mit Event-Bildern',
		],
		'table' => [
			'label' => 'Tabelle',
			'description' => 'Tabellarische Übersicht aller Event-Details',
		],
	];
	
	/**
	 * Grid views (für [cts_grid] shortcode)
	 * 
	 * @var array
	 */
	private static $grid_views = [
		'simple' => [
			'label' => 'Einfach',
			'description' => 'Einfache Grid-Ansicht',
		],
		'modern' => [
			'label' => 'Modern',
			'description' => 'Moderne Grid-Ansicht mit Styling',
		],
	];
	
	/**
	 * Calendar views (für [cts_calendar] shortcode)
	 * 
	 * @var array
	 */
	private static $calendar_views = [
		'monthly-simple' => [
			'label' => 'Monatlich (Einfach)',
			'description' => 'Vereinfachte monatliche Kalenderansicht',
		],
	];
	
	/**
	 * Get all list views
	 * 
	 * @return array List views with labels and descriptions
	 */
	public static function get_list_views(): array {
		return apply_filters( 'churchtools_suite_list_views', self::$list_views );
	}
	
	/**
	 * Get all grid views
	 * 
	 * @return array Grid views with labels and descriptions
	 */
	public static function get_grid_views(): array {
		return apply_filters( 'churchtools_suite_grid_views', self::$grid_views );
	}
	
	/**
	 * Get all calendar views
	 * 
	 * @return array Calendar views with labels and descriptions
	 */
	public static function get_calendar_views(): array {
		return apply_filters( 'churchtools_suite_calendar_views', self::$calendar_views );
	}
	
	/**
	 * Get list view IDs only
	 * 
	 * @return array View IDs (e.g., ['classic', 'minimal', 'modern', ...])
	 */
	public static function get_list_view_ids(): array {
		return array_keys( self::get_list_views() );
	}
	
	/**
	 * Get grid view IDs only
	 * 
	 * @return array View IDs (e.g., ['simple', 'modern'])
	 */
	public static function get_grid_view_ids(): array {
		return array_keys( self::get_grid_views() );
	}
	
	/**
	 * Get calendar view IDs only
	 * 
	 * @return array View IDs (e.g., ['monthly-simple'])
	 */
	public static function get_calendar_view_ids(): array {
		return array_keys( self::get_calendar_views() );
	}
	
	/**
	 * Get view label
	 * 
	 * @param string $view_id View identifier
	 * @param string $type View type (list|grid|calendar)
	 * @return string View label or empty string if not found
	 */
	public static function get_view_label( string $view_id, string $type = 'list' ): string {
		$method = 'get_' . $type . '_views';
		if ( ! method_exists( __CLASS__, $method ) ) {
			return '';
		}
		
		$views = self::$method();
		return $views[ $view_id ]['label'] ?? '';
	}
	
	/**
	 * Get view description
	 * 
	 * @param string $view_id View identifier
	 * @param string $type View type (list|grid|calendar)
	 * @return string View description or empty string if not found
	 */
	public static function get_view_description( string $view_id, string $type = 'list' ): string {
		$method = 'get_' . $type . '_views';
		if ( ! method_exists( __CLASS__, $method ) ) {
			return '';
		}
		
		$views = self::$method();
		return $views[ $view_id ]['description'] ?? '';
	}
	
	/**
	 * Check if view exists
	 * 
	 * @param string $view_id View identifier
	 * @param string $type View type (list|grid|calendar)
	 * @return bool
	 */
	public static function view_exists( string $view_id, string $type = 'list' ): bool {
		$method = 'get_' . $type . '_views';
		if ( ! method_exists( __CLASS__, $method ) ) {
			return false;
		}
		
		$views = self::$method();
		return isset( $views[ $view_id ] );
	}
	
	/**
	 * Get views as array for JavaScript
	 * 
	 * Format für Block Editor / Admin JavaScript
	 * [
	 *   { value: 'classic', label: 'Klassisch' },
	 *   { value: 'minimal', label: 'Minimal' },
	 *   ...
	 * ]
	 * 
	 * @param string $type View type (list|grid|calendar)
	 * @return array JavaScript-compatible array
	 */
	public static function get_views_for_js( string $type = 'list' ): array {
		$method = 'get_' . $type . '_views';
		if ( ! method_exists( __CLASS__, $method ) ) {
			return [];
		}
		
		$views = self::$method();
		$result = [];
		
		foreach ( $views as $id => $data ) {
			$result[] = [
				'value' => $id,
				'label' => $data['label'] ?? $id,
			];
		}
		
		return $result;
	}
	
	/**
	 * Register available views globally in wp_localize_script
	 * 
	 * Called during enqueue_scripts action to inject views into JS
	 * 
	 * @return void
	 */
	public static function register_views_for_blocks(): void {
		wp_localize_script( 'churchtools-suite-blocks', 'ctsBockEditorViews', [
			'list' => self::get_views_for_js( 'list' ),
			'grid' => self::get_views_for_js( 'grid' ),
			'calendar' => self::get_views_for_js( 'calendar' ),
		] );
	}
}
