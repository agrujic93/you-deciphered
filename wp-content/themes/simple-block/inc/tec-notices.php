<?php
/**
 * Single-event notice compatibility for The Events Calendar.
 *
 * @package Simple_Block
 */

if ( ! function_exists( 'simple_block_tec_remove_single_event_search_notice' ) ) {
	/**
	 * Remove the legacy no-results notice when rendering an existing single event.
	 *
	 * The plugin's legacy template checks have_posts() after the block template
	 * may have consumed the main loop, incorrectly treating the event as missing.
	 *
	 * @param string $template_file The event view template being rendered.
	 */
	function simple_block_tec_remove_single_event_search_notice( $template_file ) {
		if (
			! is_singular( 'tribe_events' )
			|| 'single-event.php' !== basename( $template_file )
			|| ! get_queried_object() instanceof WP_Post
			|| ! class_exists( 'Tribe__Notices' )
			|| ( function_exists( 'tribe_is_showing_all' ) && tribe_is_showing_all() )
		) {
			return;
		}

		Tribe__Notices::remove_notice( 'event-search-no-results' );
	}
}
add_action( 'tribe_events_before_view', 'simple_block_tec_remove_single_event_search_notice', 20 );
