<?php
/**
 * Force Update Check for ChurchTools Suite
 * 
 * Run: feg-clone.test/wp-content/plugins/churchtools-suite/scripts/force-update-check.php
 */

// Load WordPress
require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';

// Check admin
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Keine Berechtigung!' );
}

echo '<h1>🔄 ChurchTools Suite Update Check</h1>';
echo '<pre style="background: #f5f5f5; padding: 15px; border-radius: 5px; line-height: 1.6;">';

// Current version
$current_version = defined( 'CHURCHTOOLS_SUITE_VERSION' ) ? CHURCHTOOLS_SUITE_VERSION : 'unknown';
echo "📦 Aktuelle Version: <strong>$current_version</strong>\n\n";

// Delete update transients
echo "🧹 Lösche WordPress Update-Cache...\n";
delete_site_transient( 'update_plugins' );
echo "   ✅ update_plugins transient gelöscht\n\n";

// Force GitHub API check
echo "🌐 Prüfe GitHub API direkt...\n";
$api_url = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';
$response = wp_remote_get( $api_url, [
	'headers' => [
		'User-Agent' => 'ChurchTools-Suite-WordPress-Plugin',
	],
	'timeout' => 15,
] );

if ( is_wp_error( $response ) ) {
	echo "   ❌ Fehler: " . $response->get_error_message() . "\n\n";
} else {
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	
	if ( isset( $data['tag_name'] ) ) {
		$latest_tag = ltrim( $data['tag_name'], 'v' );
		$current = ltrim( $current_version, 'v' );
		$is_newer = version_compare( $latest_tag, $current, '>' );
		
		echo "   ✅ Neueste Version auf GitHub: <strong>{$data['tag_name']}</strong>\n";
		echo "   📅 Veröffentlicht: {$data['published_at']}\n";
		
		if ( $is_newer ) {
			echo "\n   🎉 <strong style='color: green;'>UPDATE VERFÜGBAR!</strong>\n";
			echo "   📦 {$data['tag_name']} > $current_version\n";
		} else if ( version_compare( $latest_tag, $current, '=' ) ) {
			echo "\n   ✅ <strong style='color: green;'>Du hast die neueste Version!</strong>\n";
		} else {
			echo "\n   ℹ️ Deine Version ist neuer als GitHub (Development-Version)\n";
		}
		
		// Check for ZIP asset
		if ( ! empty( $data['assets'] ) ) {
			foreach ( $data['assets'] as $asset ) {
				if ( str_ends_with( $asset['name'], '.zip' ) ) {
					$size_mb = round( $asset['size'] / 1048576, 2 );
					echo "\n   📎 Download: {$asset['name']} ({$size_mb} MB)\n";
					echo "   🔗 {$asset['browser_download_url']}\n";
					break;
				}
			}
		}
	} else {
		echo "   ❌ Ungültige Antwort von GitHub\n\n";
	}
}

// Trigger WordPress plugin update check
echo "\n🔄 Triggere WordPress Update-Check...\n";
wp_clean_plugins_cache();
wp_update_plugins();
echo "   ✅ Update-Check abgeschlossen\n\n";

// Check what WordPress sees
$updates = get_site_transient( 'update_plugins' );
$plugin_file = 'churchtools-suite/churchtools-suite.php';

echo "📊 WordPress Update-Status:\n";
if ( isset( $updates->response[ $plugin_file ] ) ) {
	$update_info = $updates->response[ $plugin_file ];
	echo "   🎉 <strong style='color: green;'>UPDATE ERKANNT!</strong>\n";
	echo "   📦 Version: {$update_info->new_version}\n";
	echo "   📦 Package: " . ( ! empty( $update_info->package ) ? '✅ verfügbar' : '❌ fehlt' ) . "\n";
} else if ( isset( $updates->no_update[ $plugin_file ] ) ) {
	echo "   ✅ Kein Update verfügbar (neueste Version installiert)\n";
} else {
	echo "   ⚠️ Plugin nicht in Update-Transient gefunden\n";
	echo "   💡 Versuche: ChurchTools Suite deaktivieren & reaktivieren\n";
}

echo "\n✅ Fertig!\n";
echo "\n🔗 <a href='" . admin_url( 'plugins.php' ) . "'>→ Zur Plugins-Seite</a>\n";
echo '</pre>';

echo '<style>
	body { 
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
		padding: 20px;
		background: #f0f0f1;
	}
	pre {
		max-width: 800px;
		margin: 20px auto;
	}
	h1 {
		margin: 20px auto;
		max-width: 800px;
	}
</style>';
