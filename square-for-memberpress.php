<?php
/*
  Plugin Name: Square For Memberpress
  Plugin URI: https://apiexperts.io/solutions/memberpress-square/
  Description: Memberpress integration with square Payment gateway
  Version: 1.0
  Author: wpexpertsio
  Author URI: https://apiexperts.io/solutions/memberpress-square/
  Text Domain: square-for-memberpress
 */

if (!defined('ABSPATH'))
    die;

add_action('plugins_loaded', 'mepr_free_load_memberpress_square_free_only');


function mepr_free_load_memberpress_square_free_only() {

	define('MEPR_FREE_SQUARE_PATH', plugin_dir_path(__FILE__));
	define('MEPR_FREE_SQUARE_URL', plugin_dir_url(__FILE__));
	define('MEPR_FREE_SQUARE_IMAGES_URL', MEPR_FREE_SQUARE_URL . 'assets/images');

    if (!class_exists('MeprBaseRealGateway')) {
        add_action('admin_notices', 'mepr_free_require_memberpress_notice');
        return;
    } 
    if (!is_ssl()) {
        add_action('admin_notices', 'mepr_free_require_ssl_notice');
        return;
    }
    if(!get_option('mepr_free_square_access_token')	AND	!get_option('mepr_free_square_access_token_sandbox')	){
			add_action('admin_notices', 'mepr_free_connect_square_notice');
		}
	
		if (class_exists('MeprSquareGateway')) {
			add_action('admin_notices', 'mepr_free_square_premium_memberpress_notice');
			return;
		} 
			 
		if (!class_exists('MeprSquareGateway')) {
		require_once MEPR_FREE_SQUARE_PATH . 'includes/MeprFreeSquareApi.php';
		require_once MEPR_FREE_SQUARE_PATH . 'includes/MeprFreeRefundSquare.php';
		require_once MEPR_FREE_SQUARE_PATH . 'includes/MeprFreeSquareCtrl.php';
		require_once MEPR_FREE_SQUARE_PATH . 'MpFreeSquare.php';
	
		new MpFreeSquare;
		new MeprFreeSquareCtrl;
		new MeprFreeRefundSquare;

		}
	
		//connection auth credentials
		if (!function_exists('get_plugin_data')) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if (!defined('MEPR_FREE_SQUARE_SANDBOX'))	
			define('MEPR_FREE_SQUARE_SANDBOX', 'squareupsandbox');

		if (!defined('MEPR_FREE_SQUARE_LIVE'))	
			define('MEPR_FREE_SQUARE_LIVE', 'squareup');
			
           $plugin_data = get_plugin_data(__FILE__);

            $plugin_name = $plugin_data['Name'];
		if (!defined('MEPR_FREE_SQUARE_PLUGIN_NAME'))
			define('MEPR_FREE_SQUARE_PLUGIN_NAME', $plugin_name);

		if (!defined('MEPR_FREE_SQUARE_APPNAME'))
			define('MEPR_FREE_SQUARE_APPNAME', 'memberpress-connect');
		if (!defined('MEPR_FREE_SQUARE_CONNECTURL'))
			define('MEPR_FREE_SQUARE_CONNECTURL', 'https://connect.apiexperts.io/');

	
}

/**
 * Require ssl certificate notice
 */
function mepr_free_require_ssl_notice() {
    $class = 'notice notice-error is-dismissible';
    $message = __('Memperpress Square needs a SSL certificate .. Please ensure your server has a valid SSL certificate</a>', 'square-for-memberpress');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
}

/**
 * Memberpress ready connect to square
 */
function mepr_free_connect_square_notice() {
    $class = 'notice notice-error is-dismissible';
    $message = __( MEPR_FREE_SQUARE_PLUGIN_NAME.' Activated! To get started', 'square-for-memberpress' );

	printf( '<div data-dismissible="notice-one-forever-woosquare" class="%1$s"><p>%2$s %3$sConnect your Square Account.%4$s</p></div>', esc_attr( $class ),   $message,  '<a href="' . admin_url( 'admin.php?page=memberpress-options#mepr-integration' ) . '">', '</a>'   ); 
}

if ( !define('MEPR_FREE_NEW_PLUGIN_FILE', __FILE__) )
define('MEPR_FREE_NEW_PLUGIN_FILE', __FILE__);

//mepr_edit_status

function mepr_free_require_memberpress_notice(){
   $class = 'notice notice-error is-dismissible';
    $message = __('Memberpress Square requires memberpress to be installed and activated', 'square-for-memberpress');
    printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));

}

function mepr_free_square_premium_memberpress_notice(){
	$class = 'notice notice-error is-dismissible disapeared_msg';
	 $message = __('Memberpress-Square Premium is activated, so please deactivate free version', 'square-for-memberpress');
	 printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
 }

 function mepr_free_enqueue_selectively_enqueue_admin_script() {
	if(!defined('MEPR_VERSION') )
		$memberpress_version = '1.0'; 
	else
		$memberpress_version = MEPR_VERSION;  
  wp_enqueue_script('mepr-free-square-admin-script', MEPR_FREE_SQUARE_URL . 'assets/js/admin_script.js', array(), $memberpress_version);
}
add_action( 'admin_enqueue_scripts', 'mepr_free_enqueue_selectively_enqueue_admin_script');