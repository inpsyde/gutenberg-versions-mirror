<?php
/**
 * PHP and WordPress configuration compatibility functions for the Gutenberg
 * editor plugin changes related to REST API.
 *
 * @package gutenberg
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Silence is golden.' );
}

/**
 * Update the preload paths registered in Core (`site-editor.php` or `edit-form-blocks.php`).
 *
 * @param array                   $paths REST API paths to preload.
 * @param WP_Block_Editor_Context $context Current block editor context.
 * @return array Filtered preload paths.
 */
function gutenberg_block_editor_preload_paths_6_7( $paths, $context ) {
	if ( 'core/edit-site' === $context->name ) {
		// Fixes post type name. It should be `type/wp_template_part`.
		$parts_key = array_search( '/wp/v2/types/wp_template-part?context=edit', $paths, true );
		if ( false !== $parts_key ) {
			$paths[ $parts_key ] = '/wp/v2/types/wp_template_part?context=edit';
		}

		$page_options_path = array( rest_get_route_for_post_type_items( 'page' ), 'OPTIONS' );
		$page_options_key  = array_search( $page_options_path, $paths, true );
		if ( false === $page_options_key ) {
			$paths[] = $page_options_path;
		}
	}

	if ( 'core/edit-post' === $context->name ) {
		$reusable_blocks_key = array_search(
			add_query_arg(
				array(
					'context'  => 'edit',
					'per_page' => -1,
				),
				rest_get_route_for_post_type_items( 'wp_block' )
			),
			$paths,
			true
		);

		if ( false !== $reusable_blocks_key ) {
			unset( $paths[ $reusable_blocks_key ] );
		}
	}

	// Preload theme and global styles paths.
	if ( 'core/edit-site' === $context->name || 'core/edit-post' === $context->name ) {
		$active_theme     = get_stylesheet();
		$global_styles_id = WP_Theme_JSON_Resolver_Gutenberg::get_user_global_styles_post_id();
		$paths[]          = '/wp/v2/global-styles/themes/' . $active_theme . '?context=view';
		$paths[]          = '/wp/v2/global-styles/themes/' . $active_theme . '/variations?context=view';
		$paths[]          = array( '/wp/v2/global-styles/' . $global_styles_id, 'OPTIONS' );

		// Remove duplicate or unnecessary global styles paths.
		$excluded_paths   = array();
		$excluded_paths[] = '/wp/v2/global-styles/themes/' . $active_theme;
		$excluded_paths[] = '/wp/v2/global-styles/' . $global_styles_id;
		foreach ( $paths as $key => $path ) {
			if ( in_array( $path, $excluded_paths, true ) ) {
				unset( $paths[ $key ] );
			}
		}
	}

	return $paths;
}
add_filter( 'block_editor_rest_api_preload_paths', 'gutenberg_block_editor_preload_paths_6_7', 10, 2 );

if ( ! function_exists( 'wp_api_template_registry' ) ) {
	/**
	 * Hook in to the template and template part post types and modify the rest
	 * endpoint to include modifications to read templates from the
	 * BlockTemplatesRegistry.
	 *
	 * @param array  $args Current registered post type args.
	 * @param string $post_type Name of post type.
	 *
	 * @return array
	 */
	function wp_api_template_registry( $args, $post_type ) {
		if ( 'wp_template' === $post_type || 'wp_template_part' === $post_type ) {
			$args['rest_controller_class'] = 'Gutenberg_REST_Templates_Controller_6_7';
		}
		return $args;
	}
}
add_filter( 'register_post_type_args', 'wp_api_template_registry', 10, 2 );

/**
 * Adds `plugin` fields to WP_REST_Templates_Controller class.
 */
function gutenberg_register_wp_rest_templates_controller_plugin_field() {

	register_rest_field(
		'wp_template',
		'plugin',
		array(
			'get_callback'    => function ( $template_object ) {
				if ( $template_object ) {
					$registered_template = WP_Block_Templates_Registry::get_instance()->get_by_slug( $template_object['slug'] );
					if ( $registered_template ) {
						return $registered_template->plugin;
					}
				}

				return;
			},
			'update_callback' => null,
			'schema'          => array(
				'type'        => 'string',
				'description' => __( 'Plugin that registered the template.', 'gutenberg' ),
				'readonly'    => true,
				'context'     => array( 'view', 'edit', 'embed' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'gutenberg_register_wp_rest_templates_controller_plugin_field' );

/**
 * Overrides the default 'WP_REST_Server' class.
 *
 * @return string The name of the custom server class.
 */
function gutenberg_override_default_rest_server() {
	return 'Gutenberg_REST_Server';
}
add_filter( 'wp_rest_server_class', 'gutenberg_override_default_rest_server', 1 );


/**
 * Filters the arguments for registering a wp_global_styles post type.
 * Note when syncing to Core: the capabilities should be updates for `wp_global_styles` in the wp-includes/post.php.
 *
 * @since 6.7.0
 *
 * @param array  $args      Array of arguments for registering a post type.
 *                          See the register_post_type() function for accepted arguments.
 * @param string $post_type Post type key.
 *
 * @return array Array of arguments for registering a post type.
 */
function gutenberg_register_post_type_args_for_wp_global_styles( $args, $post_type ) {
	if ( 'wp_global_styles' === $post_type ) {
		$args['capabilities']['read'] = 'edit_posts';
	}

	return $args;
}

add_filter( 'register_post_type_args', 'gutenberg_register_post_type_args_for_wp_global_styles', 10, 2 );
