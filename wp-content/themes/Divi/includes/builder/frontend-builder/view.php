<?php

/**
 * Boots Frond End Builder App,
 *
 * @return Front End Builder wrap if main query, $content otherwise.
 */
function et_fb_app_boot( $content ) {
	global $et_fb_excerpt_query;
	static $called = false;

	// do not load the app if excerpt queried
	if ( $et_fb_excerpt_query ) {
		$et_fb_excerpt_query = false;
		return $content;
	}

	// Don't boot the app if the builder is not in use
	if ( ! et_pb_is_pagebuilder_used( get_the_ID() ) ) {
		return $content;
	}

	$class = apply_filters( 'et_fb_app_preloader_class', 'et-fb-page-preloading' );

	if ( '' !== $class ) {
		$class = sprintf( ' class="%1$s"', esc_attr( $class ) );
	}

	// Only return React app wrapper once for the main query.
	if ( is_main_query() && ! $called ) {
		$called = true;
		return sprintf( '<div id="et-fb-app"%1$s></div>', $class );
	}

	// Stop shortcode object processor so that shortcode in the content are treated normaly.
	et_fb_reset_shortcode_object_processing();

	return $content;
}

/**
 * Updates $et_fb_excerpt_query variable to indicate that the_excerpt query in progress,
 *
 * @return $content.
 */
function et_fb_prevent_app_boot( $content ) {
	global $et_fb_excerpt_query;

	$et_fb_excerpt_query = true;

	return $content;
}

add_filter( 'the_content', 'et_fb_app_boot', 1 );
add_filter( 'get_the_excerpt', 'et_fb_prevent_app_boot', 1 );

/**
 * Added frontend builder assets.
 * Note: loading assets on head is way too early, computedVars returns undefined on header.
 *
 * @return void
 */
function et_fb_wp_footer() {
	et_fb_enqueue_assets();

	// TODO: this is specific to Audio Module and we should conditionally call it once we have
	// $content set as an object, we can then to a check whether the audio module is
	// present.
	remove_all_filters( 'wp_audio_shortcode_library' );
	remove_all_filters( 'wp_audio_shortcode' );
	remove_all_filters( 'wp_audio_shortcode_class');
}
add_action( 'wp_footer', 'et_fb_wp_footer' );

/**
 * Added frontend builder specific body class
 * @todo load conditionally, only when the frontend builder is used
 *
 * @param array  initial <body> classes
 * @return array modified <body> classes
 */
function et_fb_add_body_class( $classes ) {
	$classes[] = 'et-fb';

	return $classes;
}
add_filter( 'body_class', 'et_fb_add_body_class' );
