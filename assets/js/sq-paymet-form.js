const paymentForm = new SqPaymentForm({
    // Initialize the payment form elements

   
    applicationId: MeprFreeSquareGateway.applicationId,
    inputClass: 'sq-input',
    autoBuild: false,
    // Customize the CSS for SqPaymentForm iframe elements
    inputStyles: [{
            fontSize: '16px',
            lineHeight: '24px',
            padding: '16px',
            placeholderColor: '#a0a0a0',
            backgroundColor: 'transparent',
        }],
    // Initialize the credit card placeholders
    cardNumber: {
        elementId: 'sq-card-number',
        placeholder: 'Card Number'
    },
    cvv: {
        elementId: 'sq-cvv',
        placeholder: 'CVV'
    },
    expirationDate: {
        elementId: 'sq-expiration-date',
        placeholder: 'MM/YY'
    },
    postalCode: {
        elementId: 'sq-postal-code',
        placeholder: 'Postal'
    },

    // SqPaymentForm callback functions
    callbacks: {
        /*
         * callback function: cardNonceResponseReceived
         * Triggered when: SqPaymentForm completes a card nonce request
         */
        cardNonceResponseReceived: function (errors, nonce, cardData) {
            if (errors) {
                console.log("errors!!!");
                //==================
                var error_html;
                error_html = '';
                jQuery('#square-errors').empty();

                jQuery("#square-errors").addClass("mepr_error");

                error_html += '<ul class="square-error-lists">';

                // handle errors
                jQuery(errors).each(function (index, error) {
                    error_html += '<li>' + error.message + '</li>';
                });

                error_html += '</ul>';
                console.log(error_html);
                // append it to DOM
                jQuery('#square-errors').append(error_html);

            } else {
                jQuery('#square-errors').empty();
                jQuery('#square-errors').removeClass('mepr_error');
//            alert(`The generated nonce is:\n${nonce}`);
                document.getElementById('card-nonce').value = nonce;
                console.log(nonce);
                // POST the nonce form to the payment processing page
                document.getElementsByClassName('mepr-square')[0].submit();
                //TODO: Replace alert with code in step 2.1
            }
        }
    },
});
//paymentForm.build();



