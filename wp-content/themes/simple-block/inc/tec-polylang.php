<?php
/**
 * Polylang support for The Events Calendar Views V2.
 *
 * @package Simple_Block
 */

if ( ! function_exists( 'simple_block_tec_language' ) ) {
	function simple_block_tec_language() {
		return function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : false;
	}
}

if ( ! function_exists( 'simple_block_tec_pretty_url' ) ) {
	/**
	 * Construct an archive URL without passing Polylang's capture groups through
	 * TEC's canonical rewrite (which can turn `(sr)` into a literal URL segment).
	 */
	function simple_block_tec_pretty_url( $url ) {
		$lang = simple_block_tec_language();
		if ( ! $lang || ! function_exists( 'pll_home_url' ) || ! function_exists( 'tribe_get_option' ) || ! is_string( $url ) || '' === $url ) {
			return $url;
		}

		$root = home_url( '/' );
		if ( 0 !== strpos( $url, $root ) ) {
			return $url;
		}

		$language_root = pll_home_url( $lang );
		$query = array();
		wp_parse_str( (string) wp_parse_url( $url, PHP_URL_QUERY ), $query );
		if ( ! isset( $query['post_type'] ) || 'tribe_events' !== $query['post_type'] ) {
			$archive_path = trim( tribe_get_option( 'eventsSlug', 'events' ), '/' ) . '/';
			if ( $language_root !== $root && 0 === strpos( $url, $root . $archive_path ) ) {
				return $language_root . substr( $url, strlen( $root ) );
			}
			return $url;
		}

		$archive = trim( tribe_get_option( 'eventsSlug', 'events' ), '/' );
		$view    = isset( $query['eventDisplay'] ) ? $query['eventDisplay'] : 'default';
		$url     = $language_root . $archive . '/';
		if ( in_array( $view, array( 'list', 'month', 'day' ), true ) ) {
			$date = isset( $query['eventDate'] ) && is_string( $query['eventDate'] ) ? $query['eventDate'] : '';
			if ( 'month' === $view && preg_match( '/^\d{4}-\d{2}$/', $date ) ) {
				$url .= $date . '/';
				unset( $query['eventDate'] );
			} elseif ( 'day' === $view && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				$url .= $date . '/';
				unset( $query['eventDate'] );
			} else {
				$url .= ( 'day' === $view ? 'today' : $view ) . '/';
			}
		}

		if ( isset( $query['paged'] ) && absint( $query['paged'] ) > 1 ) {
			$url .= 'page/' . absint( $query['paged'] ) . '/';
		}
		unset( $query['post_type'], $query['eventDisplay'], $query['paged'], $query['lang'] );

		return $query ? add_query_arg( $query, $url ) : $url;
	}
}

if ( ! function_exists( 'simple_block_tec_view_url' ) ) {
	function simple_block_tec_view_url( $url, $canonical, $view ) {
		// TEC can hand this filter an already cached rewrite pattern, not a URL.
		if ( is_string( $url ) && strpos( (string) wp_parse_url( $url, PHP_URL_PATH ), '(' ) !== false ) {
			$url = add_query_arg( array( 'post_type' => 'tribe_events', 'eventDisplay' => $view->get_template_slug() ), home_url( '/' ) );
		}
		return simple_block_tec_pretty_url( $url );
	}
}
add_filter( 'tribe_events_views_v2_view_url', 'simple_block_tec_view_url', 10, 3 );

if ( ! function_exists( 'simple_block_tec_adjacent_url' ) ) {
	function simple_block_tec_adjacent_url( $url, $canonical, $view ) {
		if ( $canonical && is_string( $url ) && strpos( (string) wp_parse_url( $url, PHP_URL_PATH ), '(' ) !== false ) {
			$url = current_filter() === 'tribe_events_views_v2_view_next_url' ? $view->next_url( false ) : $view->prev_url( false );
		}
		return simple_block_tec_pretty_url( $url );
	}
}
add_filter( 'tribe_events_views_v2_view_prev_url', 'simple_block_tec_adjacent_url', 10, 3 );
add_filter( 'tribe_events_views_v2_view_next_url', 'simple_block_tec_adjacent_url', 10, 3 );

if ( ! function_exists( 'simple_block_tec_endpoint_url' ) ) {
	function simple_block_tec_endpoint_url( $url ) {
		$lang = simple_block_tec_language();
		return $lang ? add_query_arg( 'lang', $lang, $url ) : $url;
	}
}
add_filter( 'tribe_events_views_v2_endpoint_url', 'simple_block_tec_endpoint_url' );

if ( ! function_exists( 'simple_block_tec_rest_locale' ) ) {
	function simple_block_tec_rest_locale( $params ) {
		if ( function_exists( 'pll_current_language' ) ) {
			$locale = pll_current_language( 'locale' );
			if ( $locale && get_locale() !== $locale ) {
				switch_to_locale( $locale );
			}
		}
		return $params;
	}
}
add_filter( 'tribe_events_views_v2_rest_params', 'simple_block_tec_rest_locale' );

if ( ! function_exists( 'simple_block_tec_repository_args' ) ) {
	function simple_block_tec_repository_args( $args ) {
		$lang = simple_block_tec_language();
		if ( $lang ) {
			$args['lang'] = $lang;
		}
		return $args;
	}
}
add_filter( 'tribe_events_views_v2_view_repository_args', 'simple_block_tec_repository_args' );

if ( ! function_exists( 'simple_block_tec_public_views' ) ) {
	function simple_block_tec_public_views( $views ) {
		foreach ( $views as $slug => $view ) {
			if ( isset( $view->view_url ) ) {
				$original_query = array();
				wp_parse_str( (string) wp_parse_url( $view->view_url, PHP_URL_QUERY ), $original_query );
				$query = array_intersect_key( $original_query, array_flip( array( 'tribe-bar-date', 'eventDate', 'tribe-bar-search', 'featured', 'tribe_events_cat', 'tag' ) ) );
				$view->view_url = simple_block_tec_pretty_url( add_query_arg( array_merge( $query, array( 'post_type' => 'tribe_events', 'eventDisplay' => $slug ) ), home_url( '/' ) ) );
			}
		}
		return $views;
	}
}
add_filter( 'tribe_events_views_v2_view_public_views', 'simple_block_tec_public_views' );

if ( ! function_exists( 'simple_block_tec_template_vars' ) ) {
	function simple_block_tec_template_vars( $vars, $view ) {
		if ( ! simple_block_tec_language() ) {
			return $vars;
		}

		// Today must reset the date, not inherit the date from the current view.
		if ( isset( $vars['today_url'] ) ) {
			$vars['today_url'] = simple_block_tec_pretty_url( add_query_arg( array( 'post_type' => 'tribe_events', 'eventDisplay' => $view->get_template_slug() ), home_url( '/' ) ) );
		}
		return $vars;
	}
}
add_filter( 'tribe_events_views_v2_view_template_vars', 'simple_block_tec_template_vars', 10, 2 );