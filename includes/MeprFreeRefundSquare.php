<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('MeprFreeRefundSquare')) {

    class MeprFreeRefundSquare{

        /**
         * Class Constructor
         */
        public function __construct() {
         add_action('mepr-txn-transition-status', array($this,'mepr_free_txn_transition_status_fn'), 10, 3);
        }

        public function mepr_free_txn_transition_status_fn($old_status, $new_status, $txv) {
        
        if($new_status == 'refunded' && $txv->status == 'refunded'){
    
         if(!empty(get_option('mepr_free_square_mode'))){
         
          global $wpdb;
          $mepr_db = new MeprDb(); 
          $square_mode = get_option('mepr_free_square_mode');
          $mepr_options = MeprOptions::fetch();

          $reason = 'Memberpress Refund Payment';

					$fields = array(
						"idempotency_key" => uniqid(),
						"payment_id" => $txv->trans_num,
						"reason" => $reason,
						"amount_money" => array(
							  "amount" => (int) round(  $txv->total * 100, 2 ),
							  "currency" => $mepr_options->currency_code,
							),
					);
					
          $url = "https://connect.".$square_mode.".com/v2/refunds";
          $token=($square_mode=='squareupsandbox'?get_option('mepr_free_square_access_token_sandbox'):get_option('mepr_free_square_access_token'));
					$headers = array(
						'Accept' => 'application/json',
						'Authorization' => 'Bearer '.$token,
						'Content-Type' => 'application/json',
						'Cache-Control' => 'no-cache'
					);
					$response = json_decode(wp_remote_retrieve_body(wp_remote_post($url, array(
							'method' => 'POST',
							'headers' => $headers,
							'httpversion' => '1.0',
							'sslverify' => false,
							'body' => json_encode($fields)
							)
						)
					)
          );
         
          if (!empty($response->refund->status) && ($response->refund->status == 'COMPLETED' || $response->refund->status == 'PENDING')) {
           
            MeprUtils::send_refunded_txn_notices($txv);
	      		update_option('square_refund_id_'.$txv->id,$response->refund->id);
            $response = serialize($response->refund);
            $update = "UPDATE {$mepr_db->transactions}  SET response = '$response' WHERE id = %d";
            $wpdb->query($wpdb->prepare($update, $txv->id));   
            
          } elseif(!empty( $response->errors )) {
  
             $response = serialize($response->errors);
             $update = "UPDATE {$mepr_db->transactions}  SET response = '$response', `status` = '$old_status' WHERE id = %d";
             $wpdb->query($wpdb->prepare($update, $txv->id));    
         
            } 
           }
          }
        }
      }
}