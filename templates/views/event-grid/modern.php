<?php
/**
 * List View - Modern (Card Layout)
 * 
 * Modernisiertes Card-basiertes Design mit BEM-Naming, CSS Grid und Custom Properties
 * 
 * @package ChurchTools_Suite
 * @since   0.9.7.0
 * @version 2.0.0 (Modernized with BEM + Grid)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Display-Parameter parsen
$show_event_description = isset( $args['show_event_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_event_description'] ) : true;
$show_appointment_description = isset( $args['show_appointment_description'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_appointment_description'] ) : true;
$show_location = isset( $args['show_location'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_location'] ) : true;
$show_services = isset( $args['show_services'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_services'] ) : true;
$show_time = isset( $args['show_time'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_time'] ) : true;
$show_tags = isset( $args['show_tags'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_tags'] ) : false;
$show_month_separator = isset( $args['show_month_separator'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_month_separator'] ) : true;
$show_calendar_name = isset( $args['show_calendar_name'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['show_calendar_name'] ) : true;

// v0.9.9.2: Parse use_calendar_colors option
$use_calendar_colors = isset( $args['use_calendar_colors'] ) ? 
	ChurchTools_Suite_Shortcodes::parse_boolean( $args['use_calendar_colors'] ) : false;

// Single event target
$single_event_base = apply_filters( 'churchtools_suite_single_event_base_url', home_url( '/events/' ) );
$single_event_template = get_option( 'churchtools_suite_single_template', 'professional' );

// Event action (modal or page)
$event_action = $args['event_action'] ?? 'modal';

// Style-Mode
$style_mode = $args['style_mode'] ?? 'theme';
$custom_styles = '';

if ( $style_mode === 'plugin' ) {
	$primary = '#2563eb';
	$text = '#1e293b';
	$bg = '#ffffff';
	$border_radius = 12;
	$font_size = 15;
	$padding = 20;
	$spacing = 20;
	
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
	$border_radius = $args['custom_border_radius'] ?? 12;
	$font_size = $args['custom_font_size'] ?? 15;
	$padding = $args['custom_padding'] ?? 20;
	$spacing = $args['custom_spacing'] ?? 20;
	
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

// Helper: Truncate description
if ( ! function_exists( 'cts_truncate_text' ) ) {
	function cts_truncate_text( $text, $length = 150 ) {
		if ( empty( $text ) ) {
			return '';
		}
		$text = wp_strip_all_tags( $text );
		if ( strlen( $text ) <= $length ) {
			return $text;
		}
		return substr( $text, 0, $length ) . '...';
	}
}
?>

<div class="churchtools-suite-wrapper" data-style-mode="<?php echo esc_attr( $style_mode ); ?>"<?php echo $custom_styles ? ' style="' . $custom_styles . '"' : ''; ?>>
	<div class="cts-list cts-list--modern" 
		 data-view="list-modern"
		 data-show-description="<?php echo esc_attr( $show_event_description ? '1' : '0' ); ?>"
		 data-show-appointment-description="<?php echo esc_attr( $show_appointment_description ? '1' : '0' ); ?>"
		 data-show-location="<?php echo esc_attr( $show_location ? '1' : '0' ); ?>"
		 data-show-services="<?php echo esc_attr( $show_services ? '1' : '0' ); ?>"
		 data-show-time="<?php echo esc_attr( $show_time ? '1' : '0' ); ?>"
		 data-show-tags="<?php echo esc_attr( $show_tags ? '1' : '0' ); ?>"
		 data-show-calendar-name="<?php echo esc_attr( $show_calendar_name ? '1' : '0' ); ?>">

		<?php if ( empty( $events ) ) : ?>
			<div class="cts-list__empty-state" role="status" aria-live="polite">
				<span class="dashicons dashicons-calendar-alt" aria-hidden="true"></span>
				<p><?php esc_html_e( 'Keine Termine gefunden.', 'churchtools-suite' ); ?></p>
			</div>
		<?php else : ?>
			<?php 
			$current_month = '';
			foreach ( $events as $event ) : 
				$event_month = get_date_from_gmt( $event['start_datetime'], 'Y-m' );
				$show_separator = $show_month_separator && $event_month !== $current_month;
				$current_month = $event_month;
				
				// Kalenderfarbe
				$calendar_color = $event['calendar_color'] ?? '#2563eb';
				
				// Datum-Parts
				$day = get_date_from_gmt( $event['start_datetime'], 'd' );
				$month_short = get_date_from_gmt( $event['start_datetime'], 'M' );
				
				// Click behavior
				$click_class = '';
				$click_attrs = '';
				
				if ( $event_action === 'modal' ) {
					$click_class = ' cts-list--modern__card--clickable';
					$click_attrs = sprintf( 
						' role="button" tabindex="0" data-event-id="%s" data-event-title="%s" data-event-start="%s"',
						esc_attr( $event['id'] ),
						esc_attr( $event['title'] ),
						esc_attr( $event['start_datetime'] )
					);
				} elseif ( $event_action === 'page' ) {
					$click_class = ' cts-list--modern__card--clickable';
					$event_url = add_query_arg(
						[
							'event_id' => $event['id'],
							'template' => $single_event_template,
						],
						$single_event_base
					);
					$click_attrs = sprintf(
						' role="link" tabindex="0" data-event-id="%s" data-event-url="%s"',
						esc_attr( $event['id'] ),
						esc_url( $event_url )
					);
				}
			?>
				
				<?php if ( $show_separator ) : ?>
					<div class="cts-list__month-separator" role="separator">
						<time class="cts-list__month-name" datetime="<?php echo esc_attr( $event_month ); ?>">
							<?php echo esc_html( get_date_from_gmt( $event['start_datetime'], 'F Y' ) ); ?>
						</time>
					</div>
				<?php endif; ?>
				
				<?php 
				// Calendar color styling
				$card_style = '';
				if ( $use_calendar_colors ) {
					$card_style = sprintf( ' style="--calendar-color: %s; --cts-primary-color: %s;"',
						esc_attr( $calendar_color ),
						esc_attr( $calendar_color )
					);
				}
				?>
				
				<article class="cts-list--modern__card<?php echo esc_attr( $click_class ); ?>"<?php echo $card_style . $click_attrs; ?>>
					
					<!-- Date Badge -->
					<?php 
					$badge_style = '';
					if ( $use_calendar_colors ) {
						$badge_style = sprintf(
							' style="background: linear-gradient(135deg, %1$s 0%%, %1$scc 100%%); color: #ffffff; border-color: %1$s;"',
							esc_attr( $calendar_color )
						);
					}
					?>
					<div class="cts-list--modern__date-badge"<?php echo $badge_style; ?>>
						<span class="cts-list--modern__date-day"><?php echo esc_html( $day ); ?></span>
						<span class="cts-list--modern__date-month"><?php echo esc_html( $month_short ); ?></span>
					</div>
					
					<!-- Card Content -->
					<div class="cts-list--modern__content">
						
						<!-- Calendar Name -->
						<?php if ( $show_calendar_name && ! empty( $event['calendar_name'] ) ) : 
							$calendar_badge_style = '';
							if ( $use_calendar_colors ) {
								$calendar_badge_style = sprintf( ' style="color: %s;"', esc_attr( $calendar_color ) );
							}
						?>
							<div class="cts-list--modern__calendar"<?php echo $calendar_badge_style; ?>>
								<?php echo esc_html( $event['calendar_name'] ); ?>
							</div>
						<?php endif; ?>
						
						<!-- Title -->
						<h3 class="cts-list--modern__title">
							<?php echo esc_html( $event['title'] ); ?>
						</h3>
						
						<!-- Time -->
						<?php if ( $show_time && ! empty( $event['time_display'] ) ) : ?>
							<div class="cts-list--modern__time">
								<span class="dashicons dashicons-clock" aria-hidden="true"></span>
								<time datetime="<?php echo esc_attr( $event['start_datetime'] ); ?>">
									<?php echo esc_html( $event['time_display'] ); ?>
								</time>
							</div>
						<?php endif; ?>
						
						<!-- Event Description -->
						<?php if ( $show_event_description && ! empty( $event['event_description'] ) ) : ?>
							<p class="cts-list--modern__description">
								<?php echo esc_html( cts_truncate_text( $event['event_description'], 180 ) ); ?>
							</p>
						<?php endif; ?>
						
						<!-- Appointment Description -->
						<?php if ( $show_appointment_description && ! empty( $event['appointment_description'] ) ) : ?>
							<p class="cts-list--modern__description cts-list--modern__description--appointment">
								<?php echo esc_html( cts_truncate_text( $event['appointment_description'], 180 ) ); ?>
							</p>
						<?php endif; ?>
						
						<!-- Location -->
						<?php if ( $show_location && ! empty( $event['location_name'] ) ) : ?>
							<div class="cts-list--modern__location">
								<span class="dashicons dashicons-location" aria-hidden="true"></span>
								<span><?php echo esc_html( $event['location_name'] ); ?></span>
							</div>
						<?php endif; ?>
						
						<!-- Services -->
						<?php if ( $show_services && ! empty( $event['services'] ) ) : ?>
							<div class="cts-list--modern__services">
								<?php foreach ( $event['services'] as $service ) : ?>
									<div class="cts-list--modern__service">
										<span class="cts-list--modern__service-name">
											<?php echo esc_html( $service['service_name'] ); ?>
										</span>
										<?php if ( ! empty( $service['person_name'] ) ) : ?>
											<span class="cts-list--modern__service-person">
												<?php echo esc_html( $service['person_name'] ); ?>
											</span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						
						<!-- Tags -->
						<?php if ( $show_tags && ! empty( $event['tags_array'] ) ) : ?>
							<div class="cts-list--modern__tags">
								<?php foreach ( $event['tags_array'] as $tag ) : ?>
									<span class="cts-list--modern__tag" style="background-color: <?php echo esc_attr( $tag['color'] ?? '#6b7280' ); ?>;">
										<?php echo esc_html( $tag['name'] ); ?>
									</span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						
					</div>
				</article>
				
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
