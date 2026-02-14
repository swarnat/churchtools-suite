<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Test Addon Installation</title>
</head>
<body>
	<h1>Test: Elementor Plugin Installation</h1>
	<p>Simuliert die AJAX-Installation, zeigt detaillierte Debug-Ausgabe.</p>
	
	<?php
	// Load WordPress
	require_once dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php';
	
	if ( ! current_user_can( 'install_plugins' ) ) {
		die( 'Keine Berechtigung!' );
	}
	
	echo '<pre style="background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 5px; overflow-x: auto;">';
	
	$addon_slug = 'churchtools-suite-elementor';
	$repo = 'FEGAschaffenburg/churchtools-suite-elementor';
	
	echo "Step 1: GitHub API Check\n";
	echo "========================\n";
	$api_url = "https://api.github.com/repos/{$repo}/releases/latest";
	$response = wp_remote_get( $api_url, [
		'timeout' => 15,
		'headers' => [ 'User-Agent' => 'ChurchTools-Suite-WordPress-Plugin' ],
	] );
	
	if ( is_wp_error( $response ) ) {
		echo "❌ Fehler: " . $response->get_error_message() . "\n";
		die();
	}
	
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );
	
	echo "✅ Release gefunden: {$data['tag_name']}\n";
	echo "   Name: {$data['name']}\n";
	
	// Find ZIP asset
	$zip_url = null;
	foreach ( $data['assets'] as $asset ) {
		if ( str_ends_with( $asset['name'], '.zip' ) ) {
			$zip_url = $asset['browser_download_url'];
			$zip_size = $asset['size'];
			echo "   ZIP: {$asset['name']} (" . round( $zip_size / 1024 ) . " KB)\n";
			break;
		}
	}
	
	if ( ! $zip_url ) {
		echo "❌ Keine ZIP-Datei gefunden\n";
		die();
	}
	
	echo "\nStep 2: Download ZIP\n";
	echo "====================\n";
	echo "URL: $zip_url\n";
	
	$temp_file = download_url( $zip_url );
	
	if ( is_wp_error( $temp_file ) ) {
		echo "❌ Download fehlgeschlagen: " . $temp_file->get_error_message() . "\n";
		die();
	}
	
	$file_size = filesize( $temp_file );
	echo "✅ Download erfolgreich\n";
	echo "   Temp-Datei: $temp_file\n";
	echo "   Größe: " . round( $file_size / 1024 ) . " KB\n";
	
	// Check if file is suspiciously small (< 10 KB)
	if ( $file_size < 10240 ) {
		echo "⚠️  WARNUNG: Datei ist verdächtig klein!\n";
		echo "   Prüfe Inhalt...\n";
		$content = file_get_contents( $temp_file );
		if ( strpos( $content, '<html' ) !== false || strpos( $content, '<!DOCTYPE' ) !== false ) {
			echo "❌ FEHLER: Download ist eine HTML-Seite, keine ZIP-Datei!\n";
			echo "   Erste 500 Zeichen:\n";
			echo "   " . substr( $content, 0, 500 ) . "\n";
			@unlink( $temp_file );
			die();
		}
	}
	
	// Check ZIP content
	echo "\nStep 3: ZIP-Struktur prüfen\n";
	echo "============================\n";
	
	$zip = new ZipArchive();
	if ( $zip->open( $temp_file ) === TRUE ) {
		echo "✅ ZIP ist gültig\n";
		echo "   Dateien im ZIP: " . $zip->numFiles . "\n\n";
		echo "Erste 10 Einträge:\n";
		for ( $i = 0; $i < min( 10, $zip->numFiles ); $i++ ) {
			$stat = $zip->statIndex( $i );
			echo "   " . $stat['name'] . "\n";
		}
		$zip->close();
	} else {
		echo "❌ ZIP kann nicht geöffnet werden\n";
		@unlink( $temp_file );
		die();
	}
	
	echo "\nStep 4: Plugin Installation\n";
	echo "============================\n";
	
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	
	// Custom skin to capture messages
	class Debug_Upgrader_Skin extends WP_Ajax_Upgrader_Skin {
		public function feedback( $string, ...$args ) {
			if ( isset( $this->upgrader->strings[$string] ) ) {
				$string = $this->upgrader->strings[$string];
			}
			
			if ( strpos( $string, '%' ) !== false ) {
				$args = array_map( 'strip_tags', $args );
				$args = array_map( 'esc_html', $args );
				$string = vsprintf( $string, $args );
			}
			
			echo "   " . strip_tags( $string ) . "\n";
		}
	}
	
	$upgrader = new Plugin_Upgrader( new Debug_Upgrader_Skin() );
	$result = $upgrader->install( $temp_file );
	
	// Clean up
	@unlink( $temp_file );
	
	echo "\nStep 5: Installation Result\n";
	echo "============================\n";
	
	if ( is_wp_error( $result ) ) {
		echo "❌ Fehler: " . $result->get_error_message() . "\n";
	} else if ( $result ) {
		echo "✅ Installation erfolgreich!\n";
		
		$plugin_file = $upgrader->plugin_info();
		if ( $plugin_file ) {
			echo "   Plugin-Datei: $plugin_file\n";
			
			// Try to activate
			echo "\nStep 6: Plugin aktivieren\n";
			echo "=========================\n";
			$activation = activate_plugin( $plugin_file );
			if ( is_wp_error( $activation ) ) {
				echo "❌ Aktivierung fehlgeschlagen: " . $activation->get_error_message() . "\n";
			} else {
				echo "✅ Plugin aktiviert!\n";
			}
		} else {
			echo "⚠️ Plugin-Datei konnte nicht ermittelt werden\n";
		}
	} else {
		echo "❌ Installation fehlgeschlagen (result = false)\n";
		if ( ! empty( $upgrader->skin->result ) ) {
			if ( is_wp_error( $upgrader->skin->result ) ) {
				echo "   Fehler: " . $upgrader->skin->result->get_error_message() . "\n";
			}
		}
	}
	
	echo '</pre>';
	?>
	
	<p><a href="<?php echo admin_url( 'plugins.php' ); ?>">→ Zur Plugins-Seite</a></p>
</body>
</html>
