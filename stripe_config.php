<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load keys from environment or a local config (not committed to Git)
$stripeSecretKey = getenv('STRIPE_SECRET_KEY') ?: 'sk_test_your_test_key_here';
$stripePublicKey = getenv('STRIPE_PUBLIC_KEY') ?: 'pk_test_your_test_key_here';

\Stripe\Stripe::setApiKey($stripeSecretKey);