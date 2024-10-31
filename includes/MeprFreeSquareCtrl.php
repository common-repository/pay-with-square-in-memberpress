<?php

if (!defined('ABSPATH')) {
    die('You are not allowed to call this page directly.');
}

class MeprFreeSquareCtrl extends MeprBaseCtrl {

    public function load_hooks() {
        add_action('admin_init', array($this, 'mepr_free_get_square_codes'));
		add_action( 'init', array($this, 'mepr_free_get_access_token_memsquare_renewed') ); 
        add_action('admin_notices', array($this, 'mepr_free_failed_to_get_tokens'));
		add_action('mepr_process_options', array($this, 'mepr_free_disconnect_deleted_methods'));
	}

    /**
     * Get square code to generate token
     * 
     */
    public function mepr_free_get_square_codes() {

        if (isset($_GET['refresh_token_sandbox']) && isset($_GET['access_token_sandbox'])) {
		
		
			$response_body_array = $this->mepr_free_sanitize_array($_GET['response_body']);
						
            update_option('mepr_free_square_refresh_token_sandbox', sanitize_text_field($_GET['refresh_token_sandbox']));
            update_option('mepr_free_square_access_token_sandbox', sanitize_text_field($_GET['access_token_sandbox']));
			update_option('mepr_free_square_response_body_sandbox', $response_body_array);
			update_option('mepr_free_square_app_id_sandbox', sanitize_text_field($_GET['app_id_sandbox']));
            wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration');

        } else if (isset($_GET['refresh_token']) && isset($_GET['access_token'])) {
			
			$response_body_array = $this->mepr_free_sanitize_array($_GET['response_body']);
			
            update_option('mepr_free_square_refresh_token', sanitize_text_field($_GET['refresh_token']));
            update_option('mepr_free_square_access_token', sanitize_text_field($_GET['access_token']));
            update_option('mepr_free_square_response_body', $response_body_array);
			update_option('mepr_free_square_app_id', sanitize_text_field($_GET['app_id']));
            wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration');
			
        } else {
            if (isset($_GET['error'])) {
				wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration&&mepr-square-error=true');
                exit;
            }
        }
        if (isset($_GET['success_revoke_token_sandbox'])) {
            delete_option('mepr_free_square_access_token_sandbox');
			delete_option('mepr_free_square_app_id_sandbox');
            wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration');
            exit;
        }
        if (isset($_GET['success_revoke_token'])) {
            delete_option('mepr_free_square_access_token');
			delete_option('mepr_free_square_app_id');
            wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration');
            exit;
        }
        if (isset($_GET['failed_revoke_token'])) {
			delete_option('mepr_free_square_access_token');
			delete_option('mepr_free_square_app_id');
            wp_redirect(admin_url('admin.php') . '?page=memberpress-options#mepr-integration');
            exit;
        }
    }
	
	public function mepr_free_sanitize_array( &$array ) {

		foreach ($array as $key => &$value) {	
			if( !is_array($value) ){
				$values[$key] = sanitize_text_field( $value );
			}else{
				// go inside this function again
				$this->mepr_free_sanitize_array($value);
			}
		}
		return $values;
	}


    /**
     * Add notice to admin if failed to get square tokens 
     */
    public function mepr_free_failed_to_get_tokens() {
        if (isset($_GET['mepr-square-error'])) {
            $class = 'notice notice-error is-dismissible square-first-notice';
            $message = __('failed to get / revoke square tokens', 'square-for-memberpress');
            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
        }
    }
    /**
     * renew square access token
     */
    public function mepr_free_get_access_token_memsquare_renewed() {
			if(get_option('mepr_free_square_access_token')){
				$meprSquare_auth_response = get_option('mepr_free_square_response_body');
				if( 
				!empty($meprSquare_auth_response)
					and
				(strtotime($meprSquare_auth_response['expires_at']) - 300) <= time()
				 ){ 
					$headers = array(
						'refresh_token' => $meprSquare_auth_response['refresh_token'], // Use verbose mode in cURL to determine the format you want for this header
						'Content-Type'  => 'application/json;'
					);
					$oauth_connect_url = MEPR_FREE_SQUARE_CONNECTURL;
					$redirect_url = add_query_arg(
						array(
							'app_name'    => MEPR_FREE_SQUARE_APPNAME,
							'plug'    => MEPR_FREE_SQUARE_PLUGIN_NAME,
						),
						admin_url( 'admin.php' )
					);
					$redirect_url = wp_nonce_url( $redirect_url, 'connect_wooplus', 'wc_wooplus_token_nonce' );
					$site_url = ( urlencode( $redirect_url ) );
					$args_renew = array(
						'body' => array(
							'header' => $headers,
							'action' => 'refresh_memberpress_token',
							'sandbox' => false,
							'site_url'    => $site_url,
						),
						'timeout' => 45,
					);
					
					$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );
					$decoded_oauth_response = json_decode( wp_remote_retrieve_body( $oauth_response ) );

					if(!empty($decoded_oauth_response->access_token)){
						$meprSquare_auth_response['expires_at'] = $decoded_oauth_response->expires_at;
						$meprSquare_auth_response['refresh_token'] = $decoded_oauth_response->refresh_token;
						$meprSquare_auth_response['access_token'] = $decoded_oauth_response->access_token;
						update_option('mepr_free_square_response_body',$meprSquare_auth_response);
						update_option('mepr_free_square_access_token',$meprSquare_auth_response['access_token']);
					}
				}
			}
			
			if(get_option('mepr_free_square_access_token_sandbox')){
				$mepr_square_response_body_sandbox = get_option('mepr_free_square_response_body_sandbox');
				
				if( !empty($mepr_square_response_body_sandbox)
					and
				(strtotime($mepr_square_response_body_sandbox['expires_at']) - 300) <= time()
				 ){ 
					$headers = array(
						'refresh_token' => $mepr_square_response_body_sandbox['refresh_token'], // Use verbose mode in cURL to determine the format you want for this header
						'Content-Type'  => 'application/json;'
					);
					$oauth_connect_url = MEPR_FREE_SQUARE_CONNECTURL;
					$redirect_url = add_query_arg(
						array(
							'app_name'    => MEPR_FREE_SQUARE_APPNAME,
							'plug'    => MEPR_FREE_SQUARE_PLUGIN_NAME,
						),
						admin_url( 'admin.php' )
					);
					$redirect_url = wp_nonce_url( $redirect_url, 'connect_wooplus', 'wc_wooplus_token_nonce' );
					$site_url = ( urlencode( $redirect_url ) );
					$args_renew = array(
						'body' => array(
							'header' => $headers,
							'action' => 'refresh_memberpress_token',
							'sandbox' => true,
							'site_url'    => $site_url,
						),
						'timeout' => 45,
					);
					
					$oauth_response = wp_remote_post( $oauth_connect_url, $args_renew );
					$decoded_oauth_response = json_decode( wp_remote_retrieve_body( $oauth_response ) );
					
					if(!empty($decoded_oauth_response->access_token)){
						$mepr_square_response_body_sandbox['expires_at'] = $decoded_oauth_response->expires_at;
						$mepr_square_response_body_sandbox['refresh_token'] = $decoded_oauth_response->refresh_token;
						$mepr_square_response_body_sandbox['access_token'] = $decoded_oauth_response->access_token;
						update_option('mepr_free_square_response_body_sandbox',$mepr_square_response_body_sandbox);
						update_option('mepr_free_square_access_token_sandbox',$mepr_square_response_body_sandbox['access_token']);
						
					}
				}
			}
    }

    public function mepr_free_disconnect_deleted_methods($params) {
        $mepr_options = MeprOptions::fetch(); 
        // Bail early if no payment methods have been deleted
        if (empty($params['mepr_deleted_payment_methods'])) {
            return;
        }
        foreach ($params['mepr_deleted_payment_methods'] as $method_id) {
            if (empty($mepr_options->integrations[$method_id])) {
                continue;
            }

            $integ = $mepr_options->integrations[$method_id];

            if ($integ['gateway'] === 'MeprFreeSquareGateway' && get_option('mepr_free_square_access_token')) {
                delete_option('mepr_free_square_access_token');
                delete_option('mepr_free_square_refresh_token');
            }
        }
    }

}
