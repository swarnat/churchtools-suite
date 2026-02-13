<?php
/**
 * Debug Script: Elementor Widget Registration Check
 * 
 * Usage: https://feg-clone.test/wp-content/plugins/churchtools-suite/debug-elementor.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
	die( 'Keine Berechtigung' );
}

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
	<title>Elementor Widget Debug</title>
	<style>
		body { font-family: system-ui; padding: 20px; background: #f0f0f0; }
		.box { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		h2 { margin-top: 0; color: #2271b1; }
		pre { background: #f6f7f7; padding: 15px; overflow-x: auto; border-left: 4px solid #2271b1; }
		.status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: bold; }
		.ok { background: #d4edda; color: #155724; }
		.error { background: #f8d7da; color: #721c24; }
		.warning { background: #fff3cd; color: #856404; }
	</style>
</head>
<body>
	<h1>🔍 Elementor Widget Debug</h1>
	
	<div class="box">
		<h2>1. Plugin Status</h2>
		<?php
		$cts_active = is_plugin_active( 'churchtools-suite/churchtools-suite.php' );
		$elementor_active = is_plugin_active( 'elementor/elementor.php' );
		$cts_elementor_active = is_plugin_active( 'churchtools-suite-elementor/churchtools-suite-elementor.php' );
		
		echo '<p>ChurchTools Suite: <span class="status ' . ( $cts_active ? 'ok' : 'error' ) . '">' . ( $cts_active ? 'AKTIV' : 'INAKTIV' ) . '</span></p>';
		echo '<p>Elementor: <span class="status ' . ( $elementor_active ? 'ok' : 'error' ) . '">' . ( $elementor_active ? 'AKTIV' : 'INAKTIV' ) . '</span></p>';
		echo '<p>ChurchTools Suite - Elementor Integration: <span class="status ' . ( $cts_elementor_active ? 'ok' : 'error' ) . '">' . ( $cts_elementor_active ? 'AKTIV' : 'INAKTIV' ) . '</span></p>';
		?>
	</div>
	
	<div class="box">
		<h2>2. Class Checks</h2>
		<?php
		$classes = [
			'ChurchTools_Suite' => 'Main Plugin Class',
			'CTS_Elementor_Integration' => 'Sub-Plugin Integration Class',
			'CTS_Elementor_Events_Widget' => 'Widget Class',
			'\\Elementor\\Widget_Base' => 'Elementor Base Class',
		];
		
		foreach ( $classes as $class => $label ) {
			$exists = class_exists( $class );
			echo '<p>' . $label . ': <span class="status ' . ( $exists ? 'ok' : 'error' ) . '">' . ( $exists ? 'EXISTS' : 'MISSING' ) . '</span></p>';
		}
		?>
	</div>
	
	<div class="box">
		<h2>3. Function Checks</h2>
		<?php
		$functions = [
			'churchtools_suite_get_repository' => 'Repository Factory (needed for sub-plugin)',
			'cts_elementor_init' => 'Sub-Plugin Init Function',
		];
		
		foreach ( $functions as $func => $label ) {
			$exists = function_exists( $func );
			echo '<p>' . $label . ': <span class="status ' . ( $exists ? 'ok' : 'error' ) . '">' . ( $exists ? 'EXISTS' : 'MISSING' ) . '</span></p>';
		}
		?>
	</div>
	
	<div class="box">
		<h2>4. Elementor Widgets</h2>
		<?php
		if ( did_action( 'elementor/loaded' ) && class_exists( '\\Elementor\\Plugin' ) ) {
			$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
			$widgets = $widgets_manager->get_widget_types();
			
			echo '<p>Registrierte Widgets: <strong>' . count( $widgets ) . '</strong></p>';
			
			// Suche nach ChurchTools Widgets
			$cts_widgets = array_filter( $widgets, function( $widget ) {
				return strpos( $widget->get_name(), 'cts-' ) === 0 || 
				       strpos( $widget->get_name(), 'churchtools' ) !== false;
			} );
			
			if ( ! empty( $cts_widgets ) ) {
				echo '<p><span class="status ok">ChurchTools Widgets gefunden: ' . count( $cts_widgets ) . '</span></p>';
				echo '<ul>';
				foreach ( $cts_widgets as $widget ) {
					echo '<li><strong>' . $widget->get_name() . '</strong> - ' . $widget->get_title() . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p><span class="status error">KEINE ChurchTools Widgets gefunden!</span></p>';
				
				// Zeige alle Widget-Namen zum Debugging
				echo '<details><summary>Alle registrierten Widget-Namen (zum Debugging)</summary><pre>';
				foreach ( $widgets as $widget ) {
					echo $widget->get_name() . ' => ' . $widget->get_title() . "\n";
				}
				echo '</pre></details>';
			}
		} else {
			echo '<p><span class="status error">Elementor ist nicht geladen</span></p>';
		}
		?>
	</div>
	
	<div class="box">
		<h2>5. Sub-Plugin Log</h2>
		<?php
		$log = get_option( 'cts_elementor_log', [] );
		if ( ! empty( $log ) && is_array( $log ) ) {
			echo '<p>Log-Einträge: <strong>' . count( $log ) . '</strong></p>';
			echo '<pre>';
			foreach ( array_slice( $log, -20 ) as $entry ) {
				echo esc_html( $entry ) . "\n";
			}
			echo '</pre>';
		} else {
			echo '<p><span class="status warning">Kein Log verfügbar</span></p>';
		}
		?>
	</div>
	
	<div class="box">
		<h2>6. Hook Status</h2>
		<?php
		global $wp_filter;
		
		$hooks_to_check = [
			'churchtools_suite_loaded',
			'elementor/loaded',
			'elementor/widgets/register',
			'elementor/elements/categories_registered',
		];
		
		foreach ( $hooks_to_check as $hook ) {
			$has_callbacks = isset( $wp_filter[ $hook ] ) && $wp_filter[ $hook ]->has_filters();
			echo '<p>' . $hook . ': <span class="status ' . ( $has_callbacks ? 'ok' : 'warning' ) . '">';
			echo $has_callbacks ? count( $wp_filter[ $hook ]->callbacks ) . ' Callbacks' : 'Keine Callbacks';
			echo '</span></p>';
		}
		?>
	</div>
	
	<div class="box">
		<h2>7. Aktionen</h2>
		<p>
			<a href="<?php echo admin_url( 'plugins.php' ); ?>" class="button">→ Zu den Plugins</a>
			<a href="<?php echo admin_url( 'admin.php?page=churchtools-suite-addons' ); ?>" class="button">→ Zu den Addons</a>
			<a href="<?php echo admin_url( 'admin.php?page=elementor' ); ?>" class="button">→ Zu Elementor</a>
		</p>
	</div>
	
</body>
</html>
