<?php

if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

class MpFreeSquare {

    public function __construct() {
        add_filter('mepr-gateway-paths', array($this, 'mepr_free_add_square_gateway_path'), 10, 1);
        add_filter('mepr-ctrls-paths', array($this, 'mepr_free_add_square_gateway_path'), 99, 1);
        add_action('mepr-options-admin-enqueue-script', array($this, 'mepr_free_enqueue_scripts_in_admin'));
    }

    /**
     * Load square gateway path  to general gateway page
     * @param array $paths
     * @return array
     */

    public function mepr_free_add_square_gateway_path($paths) {
        array_push($paths, MEPR_FREE_SQUARE_PATH . 'includes');
        return $paths;
    }

    public function mepr_free_enqueue_scripts_in_admin() {
        wp_enqueue_style('mempr-free-square-admin-form-css', MEPR_FREE_SQUARE_URL . 'assets/css/admin_form.css');
    }
}

?>
