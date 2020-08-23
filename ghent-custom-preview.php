<?php
/**
 * Plugin Name:       Ghent Custom Preview
 * Description:       adds customer's logos to the products
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Nowell VanHoesen
 * Author URI:        creativefuse.org
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ghent-cfi
 */

define( 'GHENT_BASE_DIR', plugin_dir_path( __FILE__ ) );
define( 'GHENT_PREVIEW_BACKGROUND_DIR', GHENT_BASE_DIR . 'assets/preview-backgrounds/' );

$wp_upload_dir = wp_upload_dir();

function ghent_cfi_customize_init() {

	if ( is_admin() || ! is_product() ) {
		return;
	}

	// if is_product enqueue main.js and main.css
	add_action( 'wp_enqueue_scripts', 'nv_cfi_ghent_enqueue_scripts', 999 );

}

add_action( 'get_header', 'ghent_cfi_customize_init' );

function nv_cfi_ghent_enqueue_scripts() {

	wp_enqueue_script( 'ghent-cfi-customizer-script', plugin_dir_url( __FILE__ ) . 'assets/main.js', [], false, true );
	wp_enqueue_style( 'ghent-cfi-customizer-styles', plugin_dir_url( __FILE__ ) . 'assets/main.css' );
	wp_localize_script( 'ghent-cfi-customizer-script', 'ghent_cfi', [ 'ajax_url' => admin_url('admin-ajax.php') ]);

}

function nv_cfi_ghent_admin_enqueue() {
	wp_enqueue_style( 'ghent-cfi-admin-styles', plugin_dir_url( __FILE__ ) . 'assets/admin_main.css' );
}
require_once 'vendor/autoload.php';
require_once 'inc/wc_variation_integration.php';
require_once 'inc/order-preview-processing.php';
add_action( 'admin_enqueue_scripts', 'nv_cfi_ghent_admin_enqueue' );
