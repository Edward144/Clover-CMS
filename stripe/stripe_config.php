<?php

    require_once(dirname(__FILE__) . '/vendor/autoload.php');

    //Live or Test Mode
    define('STRIPE_LIVE', false);
    
    //Live API Key
    define('STRIPE_LIVE_PUBLIC', '');
    define('STRIPE_LIVE_SECRET', '');

    //Test API Key
    define('STRIPE_TEST_PUBLIC', 'pk_test_51Gs49zE479KZUyCP1WnaheM7tiN8iHGwgUYcW0qDTiSPYkdexEb7iFsI5uMZw2QZNzrYNiCx7jiEhkQXJad5BYHm00G7zjYSsJ');
    define('STRIPE_TEST_SECRET', 'sk_test_51Gs49zE479KZUyCPxDoDvwLnB69fdEm7wD0XCjRx12DvAsLX8CKSIW19REb5WGLfSdbgLZQ7kBMiwYDCWxe94eKa004VxcRvYR');

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