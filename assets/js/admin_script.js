

setTimeout(function() {
 jQuery('.disapeared_msg').fadeOut('fast');
}, 10000); 


    jQuery(document).ready(function () {
         if (jQuery('.mepr-square-testmode').is(":checked")) {
                console.log('yess');
                jQuery('.mepr-square-sandbox-mode').show();
                jQuery('.mepr-square-live-mode').hide();
            } else {
                jQuery('.mepr-square-live-mode').show();
                jQuery('.mepr-square-sandbox-mode').hide();
                console.log('no');
            }
        jQuery('.mepr-square-testmode').on('change', function () {
//           console.log(jQuery('.mepr-square-testmode').val());
            if (jQuery('.mepr-square-testmode').is(":checked")) {
                console.log('yess');
                jQuery('.mepr-square-sandbox-mode').show();
                jQuery('.mepr-square-live-mode').hide();
            } else {
                jQuery('.mepr-square-live-mode').show();
                jQuery('.mepr-square-sandbox-mode').hide();
                console.log('no');
            }
        })
    });
