<?php
/**
 * Remote Update Check for Live Site
 * 
 * Upload to: test2-aschaffenburg.feg.de/wp-content/plugins/churchtools-suite/scripts/
 * Run: https://test2-aschaffenburg.feg.de/wp-content/plugins/churchtools-suite/scripts/check-update-live.php
 */

// Load WordPress
$wp_load_paths = [
	dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php',
	$_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
];

foreach ( $wp_load_paths as $path ) {
	if ( file_exists( $path ) ) {
		require_once $path;
		break;
	}
}

if ( ! function_exists( 'wp_remote_get' ) ) {
	die( 'WordPress konnte nicht geladen werden!' );
}

// Check admin (skip for debugging)
// if ( ! current_user_can( 'manage_options' ) ) {
// 	wp_die( 'Keine Berechtigung!' );
// }

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>ChurchTools Suite Update Check</title>
	<style>
		body { 
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
			padding: 20px;
			background: #f0f0f1;
			color: #1d2327;
		}
		.container {
			max-width: 900px;
			margin: 0 auto;
			background: white;
			padding: 30px;
			border-radius: 8px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		h1 { color: #2271b1; margin-top: 0; }
		h2 { 
			color: #2271b1; 
			border-bottom: 2px solid #2271b1;
			padding-bottom: 10px;
			margin-top: 30px;
		}
		.info-box {
			background: #f6f7f7;
			padding: 15px;
			border-radius: 5px;
			margin: 15px 0;
			border-left: 4px solid #72aee6;
		}
		.success { border-left-color: #00a32a; background: #f0f6fc; }
		.warning { border-left-color: #dba617; background: #fcf9e8; }
		.error { border-left-color: #d63638; background: #fcf0f1; }
		.code {
			background: #1e1e1e;
			color: #d4d4d4;
			padding: 15px;
			border-radius: 5px;
			font-family: 'Consolas', 'Monaco', monospace;
			font-size: 13px;
			overflow-x: auto;
			margin: 10px 0;
		}
		table {
			width: 100%;
			border-collapse: collapse;
			margin: 15px 0;
		}
		th, td {
			padding: 12px;
			text-align: left;
			border-bottom: 1px solid #ddd;
		}
		th {
			background: #f6f7f7;
			font-weight: 600;
		}
		.btn {
			display: inline-block;
			padding: 10px 20px;
			background: #2271b1;
			color: white;
			text-decoration: none;
			border-radius: 4px;
			margin: 10px 5px 0 0;
		}
		.btn:hover { background: #135e96; }
	</style>
</head>
<body>
	<div class="container">
		<h1>🔄 ChurchTools Suite Update Diagnose</h1>
		<p><strong>Live-Site:</strong> <?php echo home_url(); ?></p>

<?php
// Current version
$current_version = defined( 'CHURCHTOOLS_SUITE_VERSION' ) ? CHURCHTOOLS_SUITE_VERSION : 'unknown';
$plugin_file = 'churchtools-suite/churchtools-suite.php';

echo "<h2>📦 Installierte Version</h2>";
echo "<div class='info-box'>";
echo "<strong>Version:</strong> $current_version<br>";
echo "<strong>Plugin-Datei:</strong> $plugin_file<br>";
echo "<strong>WordPress Version:</strong> " . get_bloginfo( 'version' );
echo "</div>";

// GitHub API check
echo "<h2>🌐 GitHub API Check</h2>";
$api_url = 'https://api.github.com/repos/FEGAschaffenburg/churchtools-suite/releases/latest';
$response = wp_remote_get( $api_url, [
	'headers' => [ 'User-Agent' => 'ChurchTools-Suite-WordPress-Plugin' ],
	'timeout' => 15,
] );

if ( is_wp_error( $response ) ) {
	echo "<div class='info-box error'>";
	echo "❌ <strong>Fehler:</strong> " . esc_html( $response->get_error_message() );
	echo "</div>";
} else {
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	
	if ( isset( $data['tag_name'] ) ) {
		$latest_tag = ltrim( $data['tag_name'], 'v' );
		$current = ltrim( $current_version, 'v' );
		$is_newer = version_compare( $latest_tag, $current, '>' );
		
		$box_class = $is_newer ? 'success' : 'info-box';
		echo "<div class='info-box $box_class'>";
		echo "<strong>Neueste Version:</strong> {$data['tag_name']}<br>";
		echo "<strong>Veröffentlicht:</strong> " . date( 'd.m.Y H:i', strtotime( $data['published_at'] ) ) . " Uhr<br>";
		echo "<strong>Release:</strong> {$data['name']}<br>";
		
		if ( $is_newer ) {
			echo "<br>🎉 <strong style='color: green;'>UPDATE VERFÜGBAR!</strong><br>";
			echo "📦 {$data['tag_name']} &gt; $current_version";
		} else if ( version_compare( $latest_tag, $current, '=' ) ) {
			echo "<br>✅ <strong style='color: green;'>Neueste Version installiert!</strong>";
		} else {
			echo "<br>ℹ️ Installierte Version ist neuer (Development)";
		}
		echo "</div>";
		
		// Assets
		if ( ! empty( $data['assets'] ) ) {
			echo "<h3>📎 Download-Assets</h3>";
			echo "<table>";
			echo "<tr><th>Datei</th><th>Größe</th><th>Downloads</th></tr>";
			foreach ( $data['assets'] as $asset ) {
				$size_mb = round( $asset['size'] / 1048576, 2 );
				echo "<tr>";
				echo "<td><a href='" . esc_url( $asset['browser_download_url'] ) . "' target='_blank'>{$asset['name']}</a></td>";
				echo "<td>{$size_mb} MB</td>";
				echo "<td>{$asset['download_count']}</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}
}

// WordPress Update Transient
echo "<h2>📊 WordPress Update-Status</h2>";

// Clear cache first
delete_site_transient( 'update_plugins' );
wp_clean_plugins_cache();
wp_update_plugins();

$updates = get_site_transient( 'update_plugins' );

if ( isset( $updates->response[ $plugin_file ] ) ) {
	$update_info = $updates->response[ $plugin_file ];
	echo "<div class='info-box success'>";
	echo "🎉 <strong>UPDATE WIRD ANGEZEIGT!</strong><br><br>";
	echo "<strong>Neue Version:</strong> {$update_info->new_version}<br>";
	echo "<strong>Package URL:</strong> " . ( ! empty( $update_info->package ) ? '✅ verfügbar' : '❌ fehlt' ) . "<br>";
	if ( ! empty( $update_info->package ) ) {
		echo "<strong>Download:</strong> <a href='" . esc_url( $update_info->package ) . "' target='_blank'>ZIP herunterladen</a>";
	}
	echo "</div>";
	
	echo "<a href='" . admin_url( 'plugins.php' ) . "' class='btn'>→ Zur Plugins-Seite (Update verfügbar!)</a>";
	
} else if ( isset( $updates->no_update[ $plugin_file ] ) ) {
	echo "<div class='info-box'>";
	echo "✅ <strong>Kein Update verfügbar</strong><br>";
	echo "WordPress hat geprüft und keine neuere Version gefunden.";
	echo "</div>";
	
} else {
	echo "<div class='info-box warning'>";
	echo "⚠️ <strong>Plugin nicht in Update-Transient gefunden</strong><br><br>";
	echo "Das Plugin wird von WordPress nicht für Updates überwacht.<br>";
	echo "Mögliche Ursachen:<br>";
	echo "• Auto-Updater Hook nicht registriert<br>";
	echo "• Plugin wurde manuell installiert (nicht aus WordPress.org)<br>";
	echo "• Update-Check läuft nur bei aktivem Plugin<br><br>";
	echo "<strong>Lösung:</strong> Plugin einmal deaktivieren und wieder aktivieren.";
	echo "</div>";
}

// Auto-Updater Check
echo "<h2>🔧 Auto-Updater Status</h2>";
if ( class_exists( 'ChurchTools_Suite_Auto_Updater' ) ) {
	echo "<div class='info-box success'>";
	echo "✅ <strong>Auto-Updater Klasse gefunden</strong><br>";
	echo "Der Auto-Updater ist verfügbar und sollte Updates melden.";
	echo "</div>";
} else {
	echo "<div class='info-box error'>";
	echo "❌ <strong>Auto-Updater Klasse NICHT gefunden</strong><br>";
	echo "Die Klasse ChurchTools_Suite_Auto_Updater existiert nicht.";
	echo "</div>";
}

// Plugin Status
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$is_active = is_plugin_active( $plugin_file );
echo "<div class='info-box'>";
echo "<strong>Plugin Status:</strong> " . ( $is_active ? '🟢 Aktiv' : '⚪ Inaktiv' );
echo "</div>";

// Cron Schedule
$cron_hook = 'churchtools_suite_check_updates';
$next_run = wp_next_scheduled( $cron_hook );
echo "<h2>⏰ Automatische Update-Prüfung</h2>";
echo "<div class='info-box'>";
if ( $next_run ) {
	echo "✅ <strong>Geplant:</strong> " . date( 'd.m.Y H:i:s', $next_run ) . " Uhr<br>";
	echo "<strong>In:</strong> " . human_time_diff( $next_run, current_time( 'timestamp' ) );
} else {
	echo "⚠️ <strong>Keine automatische Prüfung geplant</strong>";
}
echo "</div>";

echo "<h2>🔗 Nächste Schritte</h2>";
echo "<div class='info-box'>";
echo "1. Gehe zur <a href='" . admin_url( 'plugins.php' ) . "'>Plugins-Seite</a><br>";
echo "2. Prüfe ob das Update dort angezeigt wird<br>";
echo "3. Falls nicht: Plugin deaktivieren → reaktivieren<br>";
echo "4. Seite mit Strg+F5 neu laden (Hard-Refresh)";
echo "</div>";

?>
	</div>
</body>
</html>
