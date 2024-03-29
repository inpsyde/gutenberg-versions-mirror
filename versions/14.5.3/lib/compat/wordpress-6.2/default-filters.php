<?php
/**
 * Sets up the default filters and actions for most
 * of the WordPress hooks.
 *
 * If you need to remove a default hook, this file will
 * give you the priority to use for removing the hook.
 *
 * Not all of the default hooks are found in this file.
 * For instance, administration-related hooks are located in
 * wp-admin/includes/admin-filters.php.
 *
 * If a hook should only be called from a specific context
 * (admin area, multisite environment…), please move it
 * to a more appropriate file instead.
 *
 * @package gutenberg
 */

add_action( 'switch_theme', 'wp_theme_has_theme_json_clean_cache' );
add_action( 'start_previewing_theme', 'wp_theme_has_theme_json_clean_cache' );
add_action( 'upgrader_process_complete', '_wp_theme_has_theme_json_clean_cache_upon_upgrading_active_theme' );
