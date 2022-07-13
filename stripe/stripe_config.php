<?php

    require_once(dirname(__FILE__) . '/vendor/autoload.php');

    //Live or Test Mode
    define('STRIPE_LIVE', false);
    
    //Live API Key
    define('STRIPE_LIVE_PUBLIC', '');
    define('STRIPE_LIVE_SECRET', '');

    //Test API Key
    define('STRIPE_TEST_PUBLIC', '');
    define('STRIPE_TEST_SECRET', '');

    //Initialize Client
    define('STRIPE_SECRET_KEY', (STRIPE_LIVE === true ? STRIPE_LIVE_SECRET : STRIPE_TEST_SECRET));

    try {
        $stripeClient =  new \Stripe\StripeClient(STRIPE_SECRET_KEY);
    }
    catch(Exception $e) {
        //Stripe couldn't be loaded so prevent further errors and warnings by setting the client to null
        $stripeClient = null;
    }

?>