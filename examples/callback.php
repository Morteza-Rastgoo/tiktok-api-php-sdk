<?php

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Authentication\Authentication;

// Get TikTok app credentials from environment variables
$config = array(
    'client_key' => getenv('TIKTOK_CLIENT_KEY'),
    'client_secret' => getenv('TIKTOK_CLIENT_SECRET')
);

// Initialize Authentication
$auth = new Authentication($config);

// Verify state parameter to prevent CSRF attacks
$expectedState = 'random_state_string'; // Should match the state used in login_and_post.php
if (!isset($_GET['state']) || $_GET['state'] !== $expectedState) {
    die('Invalid state parameter');
}

// Get the authorization code from the callback
$authorizationCode = isset($_GET['code']) ? $_GET['code'] : '';
$domain = getenv('APP_DOMAIN') ?: 'tik.khosousi.com';
$redirectUri = 'https://' . $domain . '/examples/callback.php'; // Must match the redirect URI used in login_and_post.php

if ($authorizationCode) {
    // Exchange the code for an access token
    $tokenResponse = $auth->getAccessTokenFromCode($authorizationCode, $redirectUri);
    
    if (isset($tokenResponse['access_token'])) {
        echo "Successfully authenticated! Access token received.\n";
        echo "Access Token: " . $tokenResponse['access_token'] . "\n";
        echo "You can now use this access token to post videos.";
    } else {
        echo "Error getting access token:\n";
        print_r($tokenResponse);
    }
} else {
    echo "No authorization code received.";
}