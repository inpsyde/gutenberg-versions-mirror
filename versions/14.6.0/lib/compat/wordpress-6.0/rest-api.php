<?php
/**
 * Overrides Core's wp-includes/rest-api.php and registers the new endpoint for WP 6.0.
 *
 * @package gutenberg
 */

/**
 * Registers the Global Styles REST API routes.
 */
function gutenberg_register_global_styles_endpoints() {
	$editor_settings = new Gutenberg_REST_Global_Styles_Controller();
	$editor_settings->register_routes();
}
add_action( 'rest_api_init', 'gutenberg_register_global_styles_endpoints' );

/**
 * Registers the Edit Site's Export REST API routes.
 *
 * @return void
 */
function gutenberg_register_edit_site_export_endpoint() {
	$editor_settings = new Gutenberg_REST_Edit_Site_Export_Controller();
	$editor_settings->register_routes();
}
add_action( 'rest_api_init', 'gutenberg_register_edit_site_export_endpoint' );


/**
 * Register a core site settings.
 *
 * Note: Needs to be backported into the `register_initial_settings` function.
 */
function gutenberg_register_site_settings() {
	register_setting(
		'reading',
		'show_on_front',
		array(
			'show_in_rest' => true,
			'type'         => 'string',
			'description'  => __( 'What to show on the front page', 'gutenberg' ),
		)
	);

	register_setting(
		'reading',
		'page_on_front',
		array(
			'show_in_rest' => true,
			'type'         => 'number',
			'description'  => __( 'The ID of the page that should be displayed on the front page', 'gutenberg' ),
		)
	);

	register_setting(
		'reading',
		'page_for_posts',
		array(
			'show_in_rest' => true,
			'type'         => 'number',
			'description'  => __( 'The ID of the page that should display the latest posts', 'gutenberg' ),
		)
	);
}
add_action( 'rest_api_init', 'gutenberg_register_site_settings', 10 );
