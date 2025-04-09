<?php

// Set session cookie parameters for better persistence
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Start the session with a specific name to avoid conflicts
session_name('tiktok_sdk_session');
session_start();

// Log session information for debugging
error_log('Login_and_post.php - Session ID: ' . session_id());
error_log('Login_and_post.php - Session data: ' . json_encode($_SESSION));

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Authentication\Authentication;

// Get TikTok app credentials from environment variables
$clientKey = getenv('TIKTOK_CLIENT_KEY');
$clientSecret = getenv('TIKTOK_CLIENT_SECRET');

// Validate credentials
if (!$clientKey || !$clientSecret) {
    die('Error: TikTok API credentials missing. Please check your .env file and ensure TIKTOK_CLIENT_KEY and TIKTOK_CLIENT_SECRET are properly configured.');
}

// Validate client key format
if (!preg_match('/^[a-zA-Z0-9]+$/', $clientKey)) {
    die('Error: Invalid client_key format. Please ensure client_key contains only alphanumeric characters.');
}

$config = array(
    'client_key' => $clientKey,
    'client_secret' => $clientSecret
);

// Initialize Authentication
try {
    $auth = new Authentication($config);
} catch (\Exception $e) {
    die('Fel: Kunde inte initiera TikTok-autentisering. Kontrollera att dina inloggningsuppgifter är korrekta.');
}

// Clear any existing state if there was an error in the callback
if (isset($_GET['error'])) {
    unset($_SESSION['tiktok_auth_state']);
}

// Generate a cryptographically secure random state
$state = bin2hex(random_bytes(16));

// Store the state in session for verification in callback
$_SESSION['tiktok_auth_state'] = $state;

// For debugging
error_log('Generated new state: ' . $state);

// Set up the authentication parameters
$domain = getenv('APP_DOMAIN') ?: 'tik.khosousi.com';
$redirectUri = 'https://' . $domain . '/examples/callback.php';
$scope = 'user.info.basic,video.upload,video.publish';

// Get the authentication URL with sandbox mode
$authUrl = $auth->getAuthenticationUrl($redirectUri, $scope, $state);

// Output a simple HTML page with the auth link
?><!DOCTYPE html>
<html>
<head>
    <title>TikTok SDK Authentication Example</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .auth-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #fe2c55;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .auth-button:hover { background-color: #e62a4d; }
        .error-message {
            color: #fe2c55;
            padding: 10px;
            border: 1px solid #fe2c55;
            border-radius: 4px;
            margin: 20px 0;
            display: none;
        }
        .sandbox-notice {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TikTok SDK Authentication Example</h1>
        <div class="error-message" id="errorMessage"></div>
        <div class="sandbox-notice">
            <strong>Sandbox Mode Notice:</strong>
            <p>This application is running in sandbox mode. You can only test with authorized test accounts.</p>
        </div>
        <p>Click the button below to authenticate with TikTok. This will allow the application to:</p>
        <ul>
            <li>Access your basic profile information</li>
            <li>Upload videos to your account</li>
            <li>Publish videos on your behalf</li>
        </ul>
        <a href="<?php echo htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8'); ?>" class="auth-button">Authenticate with TikTok</a>
    </div>
    <script>
        // Check for error parameters in URL
        const urlParams = new URLSearchParams(window.location.search);
        const error = urlParams.get('error');
        const errorType = urlParams.get('error_type');
        
        if (error) {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.style.display = 'block';
            if (error === 'unauthorized_client' && errorType === 'client_key') {
                errorMessage.innerHTML = 'Error: Invalid client_key. Please contact the administrator to verify API credentials.';
            } else {
                errorMessage.innerHTML = 'An error occurred during authentication. Please try again later.';
            }
        }
    </script>
</body>
</html>