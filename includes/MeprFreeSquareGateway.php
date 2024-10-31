<?php
if (!defined('ABSPATH'))
    die;

class MeprFreeSquareGateway extends MeprBaseRealGateway {

 

    public $name;
	public $settings;

    public function __construct() {

        $this->name = __("Square For Memberpress", 'memberpress-for-square');
        $this->icon = MEPR_FREE_SQUARE_IMAGES_URL . '/square-connect.jpg';
        $this->desc = __('Pay with your credit card via Square', 'square-for-memberpress');
        $this->set_defaults();

        $this->capabilities = array(
            'process-credit-cards',
            'create-subscriptions',
            'process-payments',
        );
    }

 
    public function load($settings) {
        $this->settings = (object) $settings;
        $this->set_defaults();
    }

    /**
     *  Set default plugin settings
     */
    protected function set_defaults() {
        if (!isset($this->settings)) {
            $this->settings = array();
        }
       
        $this->settings = (object) array_merge(
                        array(
                    'gateway' => 'MeprFreeSquareGateway',
                    'id' => $this->generate_id(),
                    'label' => '',
                    'icon' => MEPR_IMAGES_URL . '/checkout/cards.png',
                    'use_label' => true,
                    'use_icon' => true,
                    'desc' => __('Pay with your credit card via Square', 'square-for-memberpress'),
                    'use_desc' => true,
                    'email' => '',
                    'sandbox' => false,
                    'force_ssl' => true,
                    'debug' => false,
                    'test_mode' => false,
                    'api_keys' => array(
                        'test' => array(
                            'app_id' => '',
                            'domain_url' => ''
                        ),
                        'live' => array(
                            'app_id' => '',
                            'domain_url' => ''
                        )
                    ),
                        ), (array) $this->settings
        );

        $this->id = $this->settings->id;
        $this->label = $this->settings->label;
        $this->use_label = $this->settings->use_label;
        $this->use_icon = $this->settings->use_icon;
        $this->use_desc = $this->settings->use_desc;
        if ($this->is_test_mode()) {
		
            $this->settings->app_id_sandbox = get_option('mepr_free_square_app_id_sandbox');
            $this->settings->domain_url = 'squareupsandbox';
            update_option( 'mepr_free_square_mode', 'squareupsandbox' );
         
        } else {
           
            $this->settings->app_id = get_option('mepr_free_square_app_id');
            $this->settings->domain_url = 'squareup';
            update_option( 'mepr_free_square_mode', 'squareup');
        
        }
     
    }

    /**
     *  Used to send data to a given payment gateway.
     */
  
    public function process_payment($txn) {
        if (isset($txn) && $txn instanceof MeprTransaction) {
            $usr = new MeprUser($txn->user_id);
            $prd = new MeprProduct($txn->product_id);
        } else {
            throw new MeprGatewayException(__('Payment was unsuccessful, please check your payment details and try again.', 'square-for-memberpress'));
        }
        $mepr_options = MeprOptions::fetch();
        if (!isset($txn->total)) {
            throw new MeprGatewayException(__('No Amount!!', 'square-for-memberpress'));
        }
        $nonce = isset($_POST['card_nonce']) ? sanitize_text_field( $_POST['card_nonce'] ) : '';
        if (empty($nonce)) {
            throw new MeprGatewayException(__('Payment could not be processed at this time', 'square-for-memberpress'));
        }
        $idempotency_key = uniqid();
        $fields = array(
            "idempotency_key" => $idempotency_key,
            "autocomplete" => true,
            "amount_money" => array(
                "amount" => ( (int) round( $txn->total *100, 2 ) ), 
                "currency" => $mepr_options->currency_code
            ),
            "reference_id" => $txn->id,
            "source_id" => $nonce,
            'metadata' => array(
                'site_url' => esc_url(get_site_url()),
            )
        );
        $response = MeprFreeSquareApi::charge($fields, $api = '/v2/payments', $method = 'POST', $this->settings->domain_url);
        if (isset($response->payment->status) && $response->payment->status == 'COMPLETED') {
            $txn->trans_num = $response->payment->id;
            $txn->status = MeprTransaction::$complete_str;

            $this->email_status("Standard Transaction\n" . MeprUtils::object_to_string($txn->rec, true) . "\n", $this->settings->debug);
        } else if (isset($response->payment->status) && ($response->payment->status == 'FAILED' || $response->payment->status == 'CANCELED')) {
            $txn->status = MeprTransaction::$failed_str;
            MeprUtils::send_failed_txn_notices($txn);
        } else if ($response->errors) {
            foreach ($response->errors as $error) {
                throw new MeprGatewayException($error->detail);
            }
        } else { // request error
            $txn->status = MeprTransaction::$failed_str;
            MeprUtils::send_failed_txn_notices($txn);
        }
        $txn->store();
        MeprUtils::send_transaction_receipt_notices($txn);
    }

    public function record_payment() {
        return;
    }

    public function process_refund(MeprTransaction $txn) {
        return;
    }

    public function record_refund() {
        return;
    }

    public function record_subscription_payment() {
        return;
    }
    /** Used to record a declined payment. */
    public function record_payment_failure() {
        return;
    }

    public function process_trial_payment($txn) {
        return;
    }

    public function record_trial_payment($txn) {
        return;
    }
    public function process_create_subscription($txn) {
        return;
    }

    public function record_create_subscription() {
        return;
    }

    public function process_update_subscription($subscription_id) {
        return;
    }

    public function record_update_subscription() {
        return;
    }

    public function process_suspend_subscription($subscription_id) {
        return;
    }

    public function record_suspend_subscription() {
        return;
    }

    public function process_resume_subscription($subscription_id) {
        return;
    }

    public function record_resume_subscription() {
        return;
    }

    public function process_cancel_subscription($subscription_id) {
        return;
    }

    public function record_cancel_subscription() {
        return;
    }

    public function process_signup_form($txn) {
        return;
    }

    public function display_payment_page($txn) {
        return;
    }

    public function enqueue_payment_form_scripts() {
        wp_enqueue_script('mempr-free-square-payment-form-js', 'https://js.' . $this->settings->domain_url . '.com/v2/paymentform', array(), MEPR_VERSION);
        wp_enqueue_script('mepr-free-square-payment-form-scripts', MEPR_FREE_SQUARE_URL . 'assets/js/sq-paymet-form.js', array(), MEPR_VERSION);
        wp_localize_script('mepr-free-square-payment-form-scripts', 'MeprFreeSquareGateway', array(
            'applicationId' => ($this->is_test_mode() ? $this->settings->app_id_sandbox : $this->settings->app_id),
		 ));
		    
        wp_enqueue_style('mempr-free-square-payment-form-css', MEPR_FREE_SQUARE_URL . 'assets/css/sq-payment-form.css');
    }

    public function display_payment_form($amount, $user, $product_id, $transaction_id) {
		
        $mepr_options = MeprOptions::fetch();
        $prd = new MeprProduct($product_id);
        $coupon = false;
        $txn = new MeprTransaction($transaction_id);
        $prd_mode = $txn->product();
    

        //Artifically set the price of the $prd in case a coupon was used
        if ($prd->price != $amount) {
            $coupon = true;
            $prd->price = $amount;
        }

        $invoice = MeprTransactionsHelper::get_invoice($txn);
        echo $invoice;

	if((get_option('mepr_free_square_access_token')	|| get_option('mepr_free_square_access_token_sandbox')) && !empty($prd_mode->is_one_time_payment())){  ?>
        <div  id="square-errors"></div>
        <form method="post" id="mepr_square_payment_form" class=" mepr-square mepr-checkout-form mepr-form mepr-card-form" novalidate>
            <input type="hidden" name="mepr_process_payment_form" value="Y" />
            <input type="hidden" name="mepr_transaction_id" value="<?php echo esc_attr($transaction_id); ?>" />
            <label><?php _e('Payment Details', 'square-for-memberpress') ?></label>
            <div id="form-container">
                <div id="sq-card-number"></div>
                <div class="third" id="sq-expiration-date"></div>
                <div class="third" id="sq-cvv"></div>
                <div class="third" id="sq-postal-code"></div>
                <input type="hidden" id="card-nonce" name="card_nonce" />
                <button id="sq-creditcard" class="button-credit-card" onclick="requestCardNonce(event)"><?php _e('Pay with Square', 'square-for-memberpress'); ?> </button>
            </div> <!-- end #form-container -->
        </form>
        
        <?php
         wp_enqueue_script('mepr-free-square-admin-form-scripts-custom', MEPR_FREE_SQUARE_URL . 'assets/js/sq-paymet.js', array(), MEPR_VERSION);
        } else { ?>
			<div id="square-errors"><?php echo _e('Sorry, it seems that subscription payment is not available in memberpress square.', 'square-for-memberpress'); ?></div>			
	    <?php }
    }

    public function process_payment_form ($txn) {
       
        $mepr_options = MeprOptions::fetch();
        //Back button fix for IE and Edge
        //Make sure they haven't just completed the subscription signup and clicked the back button
        if($txn->status != MeprTransaction::$pending_str) {
        throw new Exception(sprintf(_x('You already completed your payment to this subscription. %1$sClick here to view your subscriptions%2$s.', 'ui', 'square-for-memberpress'), '<a href="'.$mepr_options->account_page_url("action=subscriptions").'">', '</a>'));
        }

        $error_str = __('Sorry but we can\'t process your payment at this time. Try back later.', 'square-for-memberpress');

        if(isset($txn) && $txn instanceof MeprTransaction) {
        $usr = $txn->user();
        $prd = $txn->product();
        }
        else {
        throw new Exception($error_str.' [PPF01]');
        }

        if($txn->amount <= 0.00) {
        MeprTransaction::create_free_transaction($txn);
        return;
        }
      

        if($txn->gateway == $this->id) {
        if($prd->is_one_time_payment()) {
            $this->process_payment($txn);
        }
       
    }
       
        else {
        throw new Exception($error_str.' [PPF03]');
        }
    }

    public function validate_payment_form($errors) {
        return;
    }

    public function display_options_form() { 
        $mepr_options = MeprOptions::fetch();
        $test_mode = ($this->settings->test_mode == 'on' or $this->settings->test_mode == true);
        $test_mode_str = "{$mepr_options->integrations_str}[{$this->id}][test_mode]";
        require_once MEPR_FREE_SQUARE_PATH . 'includes/views/square-admin-form.php';
    }

    public function validate_options_form($errors) {
        return;
    }

    public function display_update_account_form($subscription_id, $errors = array(), $message = "") {
        return;
    }

    public function validate_update_account_form($errors = array()) {
        return;
    }

    public function process_update_account_form($subscription_id) {
        return;
    }

    public function is_test_mode() {
        return (isset($this->settings->test_mode) && $this->settings->test_mode);
    }

    public function force_ssl() {
        return true;
    }

    public static function gateways_dropdown($field_name, $curr_gateway, $obj_id) {
        return;
    }

}
