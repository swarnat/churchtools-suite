<?php
/**
 * Addons Overview Page
 * 
 * Displays all installed ChurchTools Suite addon plugins.
 * 
 * @package ChurchTools_Suite
 * @since   1.0.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure plugin.php functions are available
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Get all ChurchTools Suite addon plugins
 * 
 * Detects addons by:
 * - Plugin slug starts with 'churchtools-suite-'
 * - OR Plugin header contains 'Requires Plugins: churchtools-suite'
 * 
 * Excludes:
 * - Main plugin (churchtools-suite)
 * - Demo plugins (churchtools-suite-demo, churchtools-suite-demos)
 * 
 * @return array Array of addon plugin data
 */
function cts_get_addon_plugins() {
	$all_plugins = get_plugins();
	$addons = [];
	
	foreach ( $all_plugins as $plugin_file => $plugin_data ) {
		$plugin_slug = dirname( $plugin_file );
		
		// Skip main plugin itself
		if ( $plugin_slug === 'churchtools-suite' || $plugin_file === 'churchtools-suite/churchtools-suite.php' ) {
			continue;
		}
		
		// Skip demo plugins (ALWAYS exclude)
		if ( $plugin_slug === 'churchtools-suite-demo' || $plugin_slug === 'churchtools-suite-demos' ) {
			continue;
		}
		
		// Check if plugin is a ChurchTools Suite addon
		$is_addon = false;
		
		// Method 1: Plugin slug starts with 'churchtools-suite-'
		if ( strpos( $plugin_slug, 'churchtools-suite-' ) === 0 ) {
			$is_addon = true;
		}
		
		// Method 2: Plugin requires churchtools-suite (WordPress 6.5+ header)
		if ( ! empty( $plugin_data['RequiresPlugins'] ) && 
		     strpos( $plugin_data['RequiresPlugins'], 'churchtools-suite' ) !== false ) {
			$is_addon = true;
		}
		
		// Method 3: Plugin description mentions ChurchTools Suite
		if ( ! empty( $plugin_data['Description'] ) && 
		     ( stripos( $plugin_data['Description'], 'ChurchTools Suite' ) !== false ||
		       stripos( $plugin_data['Description'], 'churchtools-suite' ) !== false ) ) {
			$is_addon = true;
		}
		
		if ( $is_addon ) {
			$addons[ $plugin_file ] = array_merge( $plugin_data, [
				'plugin_file' => $plugin_file,
				'plugin_slug' => $plugin_slug,
				'is_active' => is_plugin_active( $plugin_file ),
				'is_network_active' => is_plugin_active_for_network( $plugin_file ),
			] );
		}
	}
	
	return $addons;
}

$addons = cts_get_addon_plugins();
$has_addons = ! empty( $addons );
$active_addons = array_filter( $addons, fn( $addon ) => $addon['is_active'] );

// Check if Elementor is active but sub-plugin is not
$elementor_active = false;
$elementor_subplugin_active = false;

if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$elementor_active = is_plugin_active( 'elementor/elementor.php' ) || did_action( 'elementor/loaded' );
$elementor_subplugin_active = is_plugin_active( 'churchtools-suite-elementor/churchtools-suite-elementor.php' ) 
                               || class_exists( 'CTS_Elementor_Integration' );

// Debug output (remove after testing)
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( 'Addons Page Debug:' );
	error_log( '- Elementor Active: ' . ( $elementor_active ? 'YES' : 'NO' ) );
	error_log( '- Elementor Subplugin Active: ' . ( $elementor_subplugin_active ? 'YES' : 'NO' ) );
	error_log( '- Show Notice: ' . ( ( $elementor_active && ! $elementor_subplugin_active ) ? 'YES' : 'NO' ) );
}

?>
<div class="wrap cts-wrap">
	
	<div class="cts-header">
		<h1>
			<span>🧩</span>
			<?php esc_html_e( 'Addons', 'churchtools-suite' ); ?>
		</h1>
		<p class="cts-subtitle"><?php esc_html_e( 'Erweitere ChurchTools Suite mit zusätzlichen Plugins', 'churchtools-suite' ); ?></p>
	</div>
	
	<!-- Debug: Elementor=<?php echo $elementor_active ? 'YES' : 'NO'; ?>, Subplugin=<?php echo $elementor_subplugin_active ? 'YES' : 'NO'; ?>, Show=<?php echo ( $elementor_active && ! $elementor_subplugin_active ) ? 'YES' : 'NO'; ?> -->
	
	<?php if ( $elementor_active && ! $elementor_subplugin_active ) : ?>
		<div class="notice notice-info" style="margin: 20px 0;">
			<p>
				<strong><?php esc_html_e( 'ChurchTools Suite - Elementor Integration', 'churchtools-suite' ); ?></strong><br>
				<?php esc_html_e( 'Elementor wurde erkannt! Installiere das ChurchTools Suite - Elementor Integration Plugin für erweiterte Funktionen.', 'churchtools-suite' ); ?>
			</p>
			<p>
				<a href="https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases" class="button button-primary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Jetzt herunterladen', 'churchtools-suite' ); ?>
				</a>
			</p>
		</div>
	<?php endif; ?>
	
	<?php if ( ! $has_addons ) : ?>
		<div class="notice notice-info inline">
			<p>
				<strong><?php esc_html_e( 'Keine Addons installiert', 'churchtools-suite' ); ?></strong><br>
				<?php esc_html_e( 'Es sind derzeit keine Erweiterungen für ChurchTools Suite installiert.', 'churchtools-suite' ); ?>
			</p>
		</div>
		
		<div class="cts-section">
			<h2><?php esc_html_e( 'Verfügbare Addons', 'churchtools-suite' ); ?></h2>
			<p><?php esc_html_e( 'Erweitere ChurchTools Suite mit offiziellen Addons:', 'churchtools-suite' ); ?></p>
			
			<div class="cts-addon-card">
				<h3>🎨 ChurchTools Suite - Elementor Integration</h3>
				<p>
					<?php esc_html_e( 'Elementor Page Builder Widget für ChurchTools Suite Events. Erstelle moderne Event-Seiten mit dem beliebten Elementor Page Builder.', 'churchtools-suite' ); ?>
				</p>
				<p>
					<strong><?php esc_html_e( 'Features:', 'churchtools-suite' ); ?></strong>
				</p>
				<ul>
					<li><?php esc_html_e( 'Drag & Drop Event-Widget für Elementor', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Live-Vorschau im Elementor Editor', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Alle ChurchTools Suite View-Typen (List, Grid, Calendar)', 'churchtools-suite' ); ?></li>
					<li><?php esc_html_e( 'Filter, Gruppierung und erweiterte Optionen', 'churchtools-suite' ); ?></li>
				</ul>
				<p>
					<a href="https://github.com/FEGAschaffenburg/churchtools-suite-elementor/releases" class="button button-primary" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Download auf GitHub', 'churchtools-suite' ); ?>
					</a>
					<a href="https://github.com/FEGAschaffenburg/churchtools-suite-elementor" class="button" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Dokumentation', 'churchtools-suite' ); ?>
					</a>
				</p>
			</div>
		</div>
		
	<?php else : ?>
		
		<div class="cts-section">
			<div class="cts-section-header">
				<h2><?php esc_html_e( 'Installierte Addons', 'churchtools-suite' ); ?></h2>
				<p class="cts-section-description">
					<?php printf( 
						esc_html__( '%d Addon(s) installiert, davon %d aktiv', 'churchtools-suite' ),
						count( $addons ),
						count( $active_addons )
					); ?>
				</p>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 3%;"><?php esc_html_e( 'Status', 'churchtools-suite' ); ?></th>
						<th style="width: 30%;"><?php esc_html_e( 'Name', 'churchtools-suite' ); ?></th>
						<th style="width: 40%;"><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></th>
						<th style="width: 12%;"><?php esc_html_e( 'Version', 'churchtools-suite' ); ?></th>
						<th style="width: 15%;"><?php esc_html_e( 'Aktionen', 'churchtools-suite' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $addons as $plugin_file => $addon ) : ?>
						<?php
						$status_icon = $addon['is_active'] ? '✅' : '⚪';
						$status_text = $addon['is_active'] 
							? esc_html__( 'Aktiv', 'churchtools-suite' ) 
							: esc_html__( 'Inaktiv', 'churchtools-suite' );
						$row_class = $addon['is_active'] ? 'active' : 'inactive';
						
						// Generate action links
						$actions = [];
						if ( $addon['is_active'] ) {
							$deactivate_url = wp_nonce_url(
								admin_url( 'plugins.php?action=deactivate&plugin=' . urlencode( $plugin_file ) . '&plugin_status=all&paged=1' ),
								'deactivate-plugin_' . $plugin_file
							);
							$actions[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url( $deactivate_url ),
								esc_html__( 'Deaktivieren', 'churchtools-suite' )
							);
						} else {
							$activate_url = wp_nonce_url(
								admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $plugin_file ) . '&plugin_status=all&paged=1' ),
								'activate-plugin_' . $plugin_file
							);
							$actions[] = sprintf(
								'<a href="%s">%s</a>',
								esc_url( $activate_url ),
								esc_html__( 'Aktivieren', 'churchtools-suite' )
							);
						}
						
						// Plugin URI link
						if ( ! empty( $addon['PluginURI'] ) ) {
							$actions[] = sprintf(
								'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
								esc_url( $addon['PluginURI'] ),
								esc_html__( 'Details', 'churchtools-suite' )
							);
						}
						?>
						<tr class="<?php echo esc_attr( $row_class ); ?>">
							<td style="text-align: center; font-size: 1.2em;">
								<span title="<?php echo esc_attr( $status_text ); ?>"><?php echo $status_icon; ?></span>
							</td>
							<td>
								<strong><?php echo esc_html( $addon['Name'] ); ?></strong>
								<?php if ( $addon['is_network_active'] ) : ?>
									<span class="cts-badge cts-badge-info">
										<?php esc_html_e( 'Netzwerk', 'churchtools-suite' ); ?>
									</span>
								<?php endif; ?>
							</td>
							<td>
								<?php echo wp_kses_post( $addon['Description'] ); ?>
							</td>
							<td>
								<code><?php echo esc_html( $addon['Version'] ); ?></code>
							</td>
							<td>
								<?php echo implode( ' | ', $actions ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		
	<?php endif; ?>
	
</div>
