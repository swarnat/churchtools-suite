<?php
/**
 * Fix Plugin Cache Issues
 * 
 * Run: feg-clone.test/wp-content/plugins/churchtools-suite/scripts/fix-plugin-cache.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

// Check admin
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung!' );
}

echo '<h1>🔧 Plugin Cache Reparieren</h1>';
echo '<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px;">';

// Delete all plugin-related transients
echo "Lösche Plugin-Transients...\n";
$transients = [
	'update_plugins',
	'plugin_slugs',
	'plugins_delete_result_' . get_current_user_id(),
];

foreach ( $transients as $transient ) {
	$deleted = delete_site_transient( $transient );
	echo "- $transient: " . ( $deleted ? '✅' : '❌' ) . "\n";
}

// Clear plugin cache
echo "\nLeere Plugin-Cache...\n";
wp_cache_delete( 'plugins', 'plugins' );
echo "✅ Cache geleert\n";

// Refresh plugin list
echo "\nAktualisiere Plugin-Liste...\n";
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugins = get_plugins();
echo "✅ " . count( $plugins ) . " Plugins gefunden\n";

// Check ChurchTools plugins
echo "\nChurchTools Plugins:\n";
foreach ( $plugins as $plugin_file => $plugin_data ) {
	if ( strpos( $plugin_file, 'churchtools' ) !== false ) {
		$active = is_plugin_active( $plugin_file ) ? '🟢 AKTIV' : '⚪ INAKTIV';
		echo "- $active {$plugin_data['Name']} ({$plugin_data['Version']})\n";
		echo "  Datei: $plugin_file\n";
	}
}

// Force WordPress to check for updates
echo "\nPrüfe auf Updates...\n";
wp_update_plugins();
echo "✅ Update-Prüfung abgeschlossen\n";

echo "\n✅ Fertig!\n";
echo "\nGehe zu: <a href='" . admin_url( 'plugins.php' ) . "'>Plugins-Seite</a>\n";
echo '</pre>';

echo '<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 20px; }</style>';
