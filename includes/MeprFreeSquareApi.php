<?php

class MeprFreeSquareApi {
    
    public static function mepr_freeget_headers($api,$mode) {
        $headers = array();
        $headers[] = 'Square-Version: 2019-11-20';
        $token=($mode=='sandbox'?get_option('mepr_free_square_access_token_sandbox'):get_option('mepr_free_square_access_token'));
        if ($api !== '/oauth2/token') {
            $headers[] = 'Accept: application/json';
            $headers[] = 'Cache-Control: no-cache';
            $headers[] = 'Authorization: Bearer ' .$token;
            $headers[] = 'Content-Type: application/json';
        }
        return $headers;
    }

    public static function charge($request_params, $api, $method,$domain_url) {

        $url =  'https://connect.' . $domain_url . '.com'.$api;
        $mode=($domain_url=='squareupsandbox'?'sandbox':"live");
        $token = ($mode=='sandbox'?get_option('mepr_free_square_access_token_sandbox'):get_option('mepr_free_square_access_token'));
        $headers = MeprFreeSquareApi::mepr_freeget_headers($api,$mode);
        if ($api == '/oauth2/token') {
        $args = array(
            'method'      => 'POST',
            'timeout'     => 45,
            'sslverify'   => false,
            'headers'     => array(
                'Authorization' => 'Bearer '.$token,
                'Content-Type'  => 'application/json',
            ),
            'body'        => http_build_query($request_params),
        );
        } else {
            $args = array(
                'method'      => 'POST',
                'timeout'     => 45,
                'sslverify'   => false,
                'headers'     => array(
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type'  => 'application/json',
                ),
                'body'        => json_encode($request_params),
            );
        }

        
        $request = wp_remote_post( $url, $args );

        if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
            MeprUtils::error_log('memberpress square request ' . $api . 'failed');
        }
    
        $response = wp_remote_retrieve_body( $request );

        return json_decode($response);
    }
}

?>