<?php
/**
 * List View - Table
 *
 * Echte Tabellen-Liste mit allen wichtigen Feldern:
 * - Datum (ausgeschrieben, von/bis)
 * - Titel
 * - Beschreibung
 * - Services
 * - Ort
 *
 * Felder sind NICHT konfigurierbar - diese View zeigt immer alle Felder an.
 *
 * @package ChurchTools_Suite
 * @since   1.0.6.0
 * 
 * Available variables:
 * @var array $events Events data
 * @var array $args   Shortcode arguments
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Style mode and custom colors
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';

if ( $style_mode === 'plugin' ) {
	$primary = '#2563eb';
	$text = '#1e293b';
	$bg = '#ffffff';
	$border_radius = 4;
	$font_size = 14;
	$padding = 16;
	$spacing = 16;
	
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $primary ),
		esc_attr( $text ),
		esc_attr( $bg ),
		absint( $border_radius ),
		absint( $font_size ),
		absint( $padding ),
		absint( $spacing )
	);
} elseif ( $style_mode === 'custom' ) {
	$primary = $args['custom_primary_color'] ?? '#2563eb';
	$text = $args['custom_text_color'] ?? '#1e293b';
	$bg = $args['custom_background_color'] ?? '#ffffff';
	$border_radius = $args['custom_border_radius'] ?? 4;
	$font_size = $args['custom_font_size'] ?? 14;
	$padding = $args['custom_padding'] ?? 16;
	$spacing = $args['custom_spacing'] ?? 16;
	
	$custom_styles = sprintf(
		'--cts-primary-color: %s; --cts-text-color: %s; --cts-bg-color: %s; --cts-border-radius: %dpx; --cts-font-size: %dpx; --cts-padding: %dpx; --cts-spacing: %dpx;',
		esc_attr( $primary ),
		esc_attr( $text ),
		esc_attr( $bg ),
		absint( $border_radius ),
		absint( $font_size ),
		absint( $padding ),
		absint( $spacing )
	);
}

// Kalenderfarben aktivieren?
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// WP timezone für Zeitzonenkonsistenz
$wp_timezone = wp_timezone();

// Helper: Formatiere Zeitbereich
function cts_format_time_range( $start_time, $end_time, $wp_timezone ) {
	$start_text = wp_date( get_option( 'time_format' ), $start_time, $wp_timezone );
	if ( $end_time && $end_time !== $start_time ) {
		$end_text = wp_date( get_option( 'time_format' ), $end_time, $wp_timezone );
		return sprintf( '%s – %s', $start_text, $end_text );
	}
	return $start_text;
}

// Helper: Kürze Text
function cts_truncate_text( $text, $length = 120 ) {
	if ( empty( $text ) ) {
		return '';
	}
	$text = wp_strip_all_tags( $text );
	if ( strlen( $text ) <= $length ) {
		return $text;
	}
	return substr( $text, 0, $length ) . '…';
}
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list-table" data-view="list-table">

		<?php if ( empty( $events ) ) : ?>
			<div class="cts-list-empty">
				<span class="cts-empty-icon">📅</span>
				<h3><?php esc_html_e( 'Keine Termine gefunden', 'churchtools-suite' ); ?></h3>
				<p><?php esc_html_e( 'Es gibt aktuell keine Termine in diesem Zeitraum.', 'churchtools-suite' ); ?></p>
			</div>

		<?php else : ?>

			<div class="cts-table-wrapper">
				<table class="cts-events-table">
					<thead>
						<tr>
							<th class="cts-col-date"><?php esc_html_e( 'Datum', 'churchtools-suite' ); ?></th>
							<th class="cts-col-title"><?php esc_html_e( 'Titel', 'churchtools-suite' ); ?></th>
							<th class="cts-col-description"><?php esc_html_e( 'Beschreibung', 'churchtools-suite' ); ?></th>
							<th class="cts-col-services"><?php esc_html_e( 'Services', 'churchtools-suite' ); ?></th>
							<th class="cts-col-location"><?php esc_html_e( 'Ort', 'churchtools-suite' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $events as $event ) : 
							// Zeitzonenkonsistenz
							$start_ts = current_time( 'timestamp' );
							$end_ts = null;
							
							if ( ! empty( $event['start_datetime'] ) ) {
								try {
									$dt = new DateTime( $event['start_datetime'], new DateTimeZone( 'UTC' ) );
									$dt->setTimezone( $wp_timezone );
									$start_ts = $dt->getTimestamp();
								} catch ( Exception $e ) {
									$start_ts = current_time( 'timestamp' );
								}
							}
							
							if ( ! empty( $event['end_datetime'] ) ) {
								try {
									$end_dt = new DateTime( $event['end_datetime'], new DateTimeZone( 'UTC' ) );
									$end_dt->setTimezone( $wp_timezone );
									$end_ts = $end_dt->getTimestamp();
								} catch ( Exception $e ) {
									$end_ts = null;
								}
							}
							
							// Datum formatieren: "Montag, 25. Januar 2026, 19:00 – 21:00"
							$date_display = wp_date( 'l, j. F Y', $start_ts, $wp_timezone );
							$time_range = cts_format_time_range( $start_ts, $end_ts, $wp_timezone );
							
							// Beschreibung kombinieren (Event + Appointment)
							$description = '';
							if ( ! empty( $event['event_description'] ) ) {
								$description = $event['event_description'];
							}
							if ( ! empty( $event['appointment_description'] ) ) {
								if ( ! empty( $description ) ) {
									$description .= "\n\n";
								}
								$description .= $event['appointment_description'];
							}
							$description_display = cts_truncate_text( $description, 120 );
							
							// Services sammeln
							$services_display = '';
							if ( ! empty( $event['services'] ) && is_array( $event['services'] ) ) {
								$service_names = array_map( function( $service ) {
									return $service['service_name'] ?? '';
								}, $event['services'] );
								$service_names = array_filter( $service_names );
								if ( ! empty( $service_names ) ) {
									$services_display = implode( ', ', $service_names );
								}
							}
							
							// Ort zusammenstellen
							$location_parts = array_filter( [
								$event['address_name'] ?? '',
								$event['address_street'] ?? '',
								$event['address_zip'] ?? '',
								$event['address_city'] ?? ''
							] );
							$location_display = implode( ', ', $location_parts );
							
							// Event-Action (Modal oder Seite)
							$event_action = isset( $args['event_action'] ) ? $args['event_action'] : 'modal';
							$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
							$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );
							
							$row_class = 'cts-event-row';
							$row_attrs = '';
							
							if ( $event_action === 'modal' ) {
								$row_class .= ' cts-event-clickable';
								$row_attrs = sprintf(
									'data-event-id="%s" role="button" tabindex="0" aria-label="%s"',
									esc_attr( $event['id'] ),
									esc_attr( sprintf( __( 'Details für %s anzeigen', 'churchtools-suite' ), $event['title'] ) )
								);
							} elseif ( $event_action === 'page' ) {
								$row_class .= ' cts-event-page-link';
								$page_url = add_query_arg(
									[
										'event_id' => $event['id'],
										'template' => $single_event_template,
										'ctse_context' => 'list',
									],
									$single_event_base
								);
								$row_attrs = sprintf(
									'data-event-id="%s" data-event-url="%s" role="link" tabindex="0" aria-label="%s"',
									esc_attr( $event['id'] ),
									esc_url( $page_url ),
									esc_attr( sprintf( __( 'Zu %s navigieren', 'churchtools-suite' ), $event['title'] ) )
								);
							}
							
							// Kalenderfarbe
							$row_style = '';
							$calendar_color = $event['calendar_color'] ?? '#2563eb';
							if ( $use_calendar_colors ) {
								$row_style = sprintf( ' style="border-left: 4px solid %s;"', esc_attr( $calendar_color ) );
							}
						?>
							<tr class="<?php echo esc_attr( $row_class ); ?>"<?php echo $row_attrs; ?><?php echo $row_style; ?>>
								
								<!-- Datum (Spalte 1) -->
								<td class="cts-col-date">
									<div class="cts-date-value">
										<div class="cts-date-main"><?php echo esc_html( $date_display ); ?></div>
										<div class="cts-time-range"><?php echo esc_html( $time_range ); ?></div>
									</div>
								</td>
								
								<!-- Titel (Spalte 2) -->
								<td class="cts-col-title">
									<div class="cts-title-value">
										<?php echo esc_html( $event['title'] ); ?>
									</div>
								</td>
								
								<!-- Beschreibung (Spalte 3) -->
								<td class="cts-col-description">
									<div class="cts-description-value">
										<?php if ( ! empty( $description_display ) ) : ?>
											<span title="<?php echo esc_attr( wp_strip_all_tags( $description ) ); ?>">
												<?php echo esc_html( $description_display ); ?>
											</span>
										<?php else : ?>
											<span class="cts-empty-cell">—</span>
										<?php endif; ?>
									</div>
								</td>
								
								<!-- Services (Spalte 4) -->
								<td class="cts-col-services">
									<div class="cts-services-value">
										<?php if ( ! empty( $services_display ) ) : ?>
											<span><?php echo esc_html( $services_display ); ?></span>
										<?php else : ?>
											<span class="cts-empty-cell">—</span>
										<?php endif; ?>
									</div>
								</td>
								
								<!-- Ort (Spalte 5) -->
								<td class="cts-col-location">
									<div class="cts-location-value">
										<?php if ( ! empty( $location_display ) ) : ?>
											<div class="cts-location-text">
												<span class="dashicons dashicons-location" style="margin-right: 4px;"></span>
												<?php echo esc_html( $location_display ); ?>
											</div>
										<?php else : ?>
											<span class="cts-empty-cell">—</span>
										<?php endif; ?>
									</div>
								</td>
								
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div><!-- /.cts-table-wrapper -->

		<?php endif; ?>

	</div><!-- /.cts-list-table -->
</div><!-- /.churchtools-suite-wrapper -->

<style>
/* Table View Styles */
.cts-list-table {
	width: 100%;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

.cts-table-wrapper {
	overflow-x: auto;
	-webkit-overflow-scrolling: touch;
	border: 1px solid #e5e7eb;
	border-radius: var(--cts-border-radius, 4px);
}

.cts-events-table {
	width: 100%;
	border-collapse: collapse;
	font-size: var(--cts-font-size, 14px);
	color: var(--cts-text-color, #1e293b);
}

/* Table Header */
.cts-events-table thead {
	background-color: var(--cts-primary-color, #2563eb);
	color: #ffffff;
}

.cts-events-table thead th {
	padding: var(--cts-padding, 16px);
	text-align: left;
	font-weight: 600;
	font-size: 13px;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border-bottom: 2px solid rgba(255, 255, 255, 0.2);
}

/* Table Body */
.cts-events-table tbody tr {
	border-bottom: 1px solid #e5e7eb;
	transition: background-color 0.2s ease;
}

.cts-events-table tbody tr:hover {
	background-color: rgba(37, 99, 235, 0.03);
}

.cts-events-table tbody tr.cts-event-clickable,
.cts-events-table tbody tr.cts-event-page-link {
	cursor: pointer;
}

.cts-events-table tbody tr.cts-event-clickable:hover,
.cts-events-table tbody tr.cts-event-page-link:hover {
	background-color: rgba(37, 99, 235, 0.08);
}

.cts-events-table td {
	padding: var(--cts-padding, 16px);
	vertical-align: top;
}

/* Column Widths */
.cts-col-date {
	width: 18%;
	min-width: 180px;
}

.cts-col-title {
	width: 20%;
	min-width: 150px;
}

.cts-col-description {
	width: 27%;
	min-width: 200px;
}

.cts-col-services {
	width: 18%;
	min-width: 150px;
}

.cts-col-location {
	width: 17%;
	min-width: 150px;
}

/* Cell Content */
.cts-date-value {
	display: flex;
	flex-direction: column;
	gap: 4px;
}

.cts-date-main {
	font-weight: 600;
	color: var(--cts-primary-color, #2563eb);
}

.cts-time-range {
	font-size: 12px;
	color: #6b7280;
}

.cts-title-value {
	font-weight: 500;
	line-height: 1.4;
	word-break: break-word;
}

.cts-description-value,
.cts-services-value,
.cts-location-value {
	font-size: 13px;
	line-height: 1.5;
	color: #4b5563;
}

.cts-location-text {
	display: flex;
	align-items: center;
	gap: 4px;
}

.cts-location-text .dashicons {
	width: auto;
	height: auto;
	font-size: 16px;
	color: var(--cts-primary-color, #2563eb);
	flex-shrink: 0;
}

.cts-empty-cell {
	color: #d1d5db;
	font-style: italic;
}

/* Responsive */
@media (max-width: 1024px) {
	.cts-col-date {
		width: 16%;
		min-width: 150px;
	}
	
	.cts-col-description {
		width: 25%;
		min-width: 180px;
	}
	
	.cts-col-services {
		width: 16%;
		min-width: 120px;
	}
	
	.cts-col-location {
		width: 15%;
		min-width: 120px;
	}
}

@media (max-width: 768px) {
	.cts-events-table {
		font-size: 12px;
	}
	
	.cts-events-table th,
	.cts-events-table td {
		padding: 12px 8px;
	}
	
	.cts-col-date {
		width: 15%;
		min-width: 120px;
	}
	
	.cts-col-title {
		width: 20%;
		min-width: 120px;
	}
	
	.cts-col-description {
		width: 22%;
		min-width: 140px;
	}
	
	.cts-col-services {
		width: 18%;
		min-width: 100px;
	}
	
	.cts-col-location {
		width: 18%;
		min-width: 100px;
	}
	
	.cts-time-range {
		display: none;
	}
}

/* Empty State */
.cts-list-empty {
	padding: 60px 20px;
	text-align: center;
	color: #6b7280;
}

.cts-empty-icon {
	font-size: 48px;
	display: block;
	margin-bottom: 16px;
	opacity: 0.6;
}

.cts-list-empty h3 {
	margin: 0 0 8px 0;
	font-size: 18px;
	color: #1e293b;
}

.cts-list-empty p {
	margin: 0;
	font-size: 14px;
}
</style>
