

<div class="wrap"> 
    <div class="welcome-panel">
        <div id="mepr-square-connect-migrate-prompt" class="mepr-payment-option-prompt">
            <p><?php _e('You need a Square account to connect your memberpress. If you do not have an account, go to <a target="_blank" href="https://squareup.com/signup">https://squareup.com/signup</a> to create one.', 'square-for-memberpress'); ?></p>
            <?php 
			 
            $sandbox_connect_url = add_query_arg(
                    array(
                'app_name' => MEPR_FREE_SQUARE_APPNAME,
                'is_sandbox' =>  'yes' ,
                'request' => 'request_token',
                'state' => admin_url('admin.php'),
                    ), MEPR_FREE_SQUARE_CONNECTURL
            );
            $production_connect_url = add_query_arg(
                    array(
                'app_name' => MEPR_FREE_SQUARE_APPNAME,
                'is_sandbox' =>  'no' ,
                'request' => 'request_token',
                'state' => admin_url('admin.php'),
                    ), MEPR_FREE_SQUARE_CONNECTURL
            );
             $sandbox_disconnect_url = add_query_arg(
                    array(
                'access_token' => get_option('mepr_free_square_access_token_sandbox'),
                'app_name' => MEPR_FREE_SQUARE_APPNAME,
                'client_id' => get_option('mepr_free_square_app_id_sandbox'),
                'is_sandbox' =>  'yes' ,
                'request' => 'revoke_token',
                'site_url' => admin_url('admin.php')
                    ), MEPR_FREE_SQUARE_CONNECTURL
            );
             $production_disconnect_url = add_query_arg(
                    array(
                'access_token' => get_option('mepr_free_square_access_token'),
                'app_name' => MEPR_FREE_SQUARE_APPNAME,
                'client_id' => get_option('mepr_free_square_app_id'),
                'is_sandbox' =>  'no',
                'request' => 'revoke_token',
                'site_url' => admin_url('admin.php')
                    ), MEPR_FREE_SQUARE_CONNECTURL
            );
//        ?>

            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="<?php echo esc_attr($test_mode_str); ?>"><?php _e('Test Mode', 'square-for-memberpress'); ?></label></th>
                        <td>
                            <input class="mepr-square-testmode" type="checkbox" name="<?php echo esc_attr($test_mode_str); ?>" data-integration="<?php echo esc_attr($this->id); ?>"<?php echo checked($test_mode); ?> />
                        </td>
                    </tr> 
                    <tr valign="top">
                        <th scope="row">
                            <?php esc_html_e('Connect/Disconnect', 'square-for-memberpress'); ?>
                            <p><?php echo _e('Connect through auth square to make system more smooth.', 'square-for-memberpress'); ?></p>
                        </th>

                        <td class="mepr-square-sandbox-mode">
                            <?php 

                                if (!get_option('mepr_free_square_access_token_sandbox')) {
                                    ?>
                                    <a href="<?php echo esc_attr($sandbox_connect_url); ?>" class="wc-square-connect-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44" width="30" height="30">
                                            <path fill="#FFFFFF" d="M36.65 0h-29.296c-4.061 0-7.354 3.292-7.354 7.354v29.296c0 4.062 3.293 7.354 7.354 7.354h29.296c4.062 0 7.354-3.292 7.354-7.354v-29.296c.001-4.062-3.291-7.354-7.354-7.354zm-.646 33.685c0 1.282-1.039 2.32-2.32 2.32h-23.359c-1.282 0-2.321-1.038-2.321-2.32v-23.36c0-1.282 1.039-2.321 2.321-2.321h23.359c1.281 0 2.32 1.039 2.32 2.321v23.36z" />
                                            <path fill="#FFFFFF" d="M17.333 28.003c-.736 0-1.332-.6-1.332-1.339v-9.324c0-.739.596-1.339 1.332-1.339h9.338c.738 0 1.332.6 1.332 1.339v9.324c0 .739-.594 1.339-1.332 1.339h-9.338z" />
                                        </svg>
                                        <span><?php esc_html_e('Connect with Square Sandbox', 'square-for-memberpress'); ?></span>
                                    </a>
                                    <?php } else { ?>
                                    <a href="<?php echo esc_attr($sandbox_disconnect_url); ?>" class='button-primary'>
                                    <?php echo esc_html__('Disconnect from Square Sandbox', 'square-for-memberpress'); ?>
                                    </a>
                                <?php }
                            
                            ?>
                        </td>
                            <td class="mepr-square-live-mode">
                                <?php if (!get_option('mepr_free_square_access_token')) {
                                    ?>
                                    <a href="<?php echo esc_attr($production_connect_url); ?>" class="wc-square-connect-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44" width="30" height="30">
                                            <path fill="#FFFFFF" d="M36.65 0h-29.296c-4.061 0-7.354 3.292-7.354 7.354v29.296c0 4.062 3.293 7.354 7.354 7.354h29.296c4.062 0 7.354-3.292 7.354-7.354v-29.296c.001-4.062-3.291-7.354-7.354-7.354zm-.646 33.685c0 1.282-1.039 2.32-2.32 2.32h-23.359c-1.282 0-2.321-1.038-2.321-2.32v-23.36c0-1.282 1.039-2.321 2.321-2.321h23.359c1.281 0 2.32 1.039 2.32 2.321v23.36z" />
                                            <path fill="#FFFFFF" d="M17.333 28.003c-.736 0-1.332-.6-1.332-1.339v-9.324c0-.739.596-1.339 1.332-1.339h9.338c.738 0 1.332.6 1.332 1.339v9.324c0 .739-.594 1.339-1.332 1.339h-9.338z" />
                                        </svg>
                                        <span><?php esc_html_e('Connect with Square Live', 'square-for-memberpress'); ?></span>
                                    </a>
                                    <?php } else { ?>
                                    <a href="<?php echo esc_attr($production_disconnect_url); ?>" class='button-primary'>
                                    <?php echo esc_html__('Disconnect from Square Live', 'square-for-memberpress'); ?>
                                    </a>
                            <?php } ?>
                            </td>
                     </tr>

                </tbody>
            </table>
        </div>

    </div>
</div>








