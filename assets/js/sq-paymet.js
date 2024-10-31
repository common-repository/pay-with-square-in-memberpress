  paymentForm.build();

  function requestCardNonce(event) {
 //
      //  // Don't submit the form until SqPaymentForm returns with a nonce
  event.preventDefault();
                //
                //  // Request a nonce from the SqPaymentForm object
 paymentForm.requestCardNonce();
  }