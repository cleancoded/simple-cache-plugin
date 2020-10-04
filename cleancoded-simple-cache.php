<?php
/**
 * Plugin Name: Cleancoded Simple Cache
 * Plugin URI: https://github.com/cleancoded/simple-cache-plugin
 * Description: A simple caching plugin that just works.
 * Author: Cleancoded
 * Version: 1.0
 * Text Domain: cleancoded-cleancoded-simple-cache
 * Domain Path: /languages
 * Author URI: http://cleancoded.com
 *
 * @package  cleancoded-simple-cache
 */

defined( 'ABSPATH' ) || exit;

define( 'Cleancoded_VERSION', '1.0' );
define( 'Cleancoded_PATH', dirname( __FILE__ ) );

$active_plugins = get_site_option( 'active_sitewide_plugins' );

if ( is_multisite() && isset( $active_plugins[ plugin_basename( __FILE__ ) ] ) ) {
	define( 'Cleancoded_IS_NETWORK', true );
} else {
	define( 'Cleancoded_IS_NETWORK', false );
}

require_once Cleancoded_PATH . '/inc/pre-wp-functions.php';
require_once Cleancoded_PATH . '/inc/functions.php';
require_once Cleancoded_PATH . '/inc/class-sc-notices.php';
require_once Cleancoded_PATH . '/inc/class-sc-settings.php';
require_once Cleancoded_PATH . '/inc/class-sc-config.php';
require_once Cleancoded_PATH . '/inc/class-sc-advanced-cache.php';
require_once Cleancoded_PATH . '/inc/class-sc-object-cache.php';
require_once Cleancoded_PATH . '/inc/class-sc-cron.php';

Cleancoded_Settings::factory();
Cleancoded_Advanced_Cache::factory();
Cleancoded_Object_Cache::factory();
Cleancoded_Cron::factory();
Cleancoded_Notices::factory();

/**
 * Load text domain
 *
 * @since 1.0
 */
function Cleancoded_load_textdomain() {

	load_plugin_textdomain( 'cleancoded-simple-cache', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'Cleancoded_load_textdomain' );


/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions Each action is HTML.
 * @param  string $plugin_file Path to plugin file.
 * @since  1.0
 * @return array
 */
function Cleancoded_filter_plugin_action_links( $plugin_actions, $plugin_file ) {

	$new_actions = array();

	if ( basename( dirname( __FILE__ ) ) . '/cleancoded-simple-cache.php' === $plugin_file ) {
		/* translators: Param 1 is link to settings page. */
		$new_actions['Cleancoded_settings'] = '<a href="' . esc_url( admin_url( 'options-general.php?page=cleancoded-simple-cache' ) ) . '">' . esc_html__( 'Settings', 'cleancoded-simple-cache' ) . '</a>';
	}

	return array_merge( $new_actions, $plugin_actions );
}
add_filter( 'plugin_action_links', 'Cleancoded_filter_plugin_action_links', 10, 2 );

/**
 * Clean up necessary files
 *
 * @param  bool $network Whether the plugin is network wide
 * @since 1.0
 */
function Cleancoded_deactivate( $network ) {
	if ( ! apply_filters( 'Cleancoded_disable_auto_edits', false ) ) {
		Cleancoded_Advanced_Cache::factory()->clean_up();
		Cleancoded_Advanced_Cache::factory()->toggle_caching( false );
		Cleancoded_Object_Cache::factory()->clean_up();
	}

	Cleancoded_Config::factory()->clean_up();

	Cleancoded_cache_flush( $network );
}
add_action( 'deactivate_' . plugin_basename( __FILE__ ), 'Cleancoded_deactivate' );

/**
 * Create config file
 *
 * @param  bool $network Whether the plugin is network wide
 * @since 1.0
 */
function Cleancoded_activate( $network ) {
	if ( $network ) {
		Cleancoded_Config::factory()->write( array(), true );
	} else {
		Cleancoded_Config::factory()->write( array() );
	}
}
add_action( 'activate_' . plugin_basename( __FILE__ ), 'Cleancoded_activate' );


