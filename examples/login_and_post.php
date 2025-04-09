<?php

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Authentication\Authentication;
use TikTok\Post\Post;

// Get TikTok app credentials from environment variables
$config = array(
    'client_key' => getenv('TIKTOK_CLIENT_KEY'),
    'client_secret' => getenv('TIKTOK_CLIENT_SECRET')
);

// Initialize Authentication
$auth = new Authentication($config);

// Step 1: Get the authentication URL
// The scope should include video.upload and video.publish
$domain = getenv('APP_DOMAIN') ?: 'tik.khosousi.com';
// Ensure the redirect URI matches exactly what's registered in TikTok developer portal
$redirectUri = 'https://' . $domain . '/examples/callback.php';
// Generate a random state string for security
$state = bin2hex(random_bytes(16));
$scope = 'user.info.basic,video.upload,video.publish';
$state = 'random_state_string'; // For security, use a random string

$authUrl = $auth->getAuthenticationUrl($redirectUri, $scope, $state);

echo "<html><body>";
echo "<h3>Step 1: Click the link below to authenticate with TikTok:</h3>";
echo "<a href='" . htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') . "' target='_blank'>" . htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') . "</a>";
echo "</body></html>";

// Step 2: After user authorizes, TikTok will redirect to your callback URL with a code
// You would get this code from $_GET['code'] in your callback script
$authorizationCode = isset($_GET['code']) ? $_GET['code'] : '';

if ($authorizationCode) {
    // Exchange the code for an access token
    $tokenResponse = $auth->getAccessTokenFromCode($authorizationCode, $redirectUri);
    
    if (isset($tokenResponse['access_token'])) {
        $accessToken = $tokenResponse['access_token'];
        echo "Step 2: Successfully got access token\n";
        
        // Step 3: Initialize Post with access token
        $config['access_token'] = $accessToken;
        $post = new Post($config);
        
        // Step 4: Post a video
        // Note: This is a simplified example. You'll need to handle the actual video upload
        // process according to TikTok's documentation
        $videoPath = '/path/to/your/video.mp4';
        if (file_exists($videoPath)) {
            $postResponse = $post->post(array(
                'endpoint' => Post::ENDPOINT . '/video/init/',
                'params' => array(
                    'post_info' => array(
                        'title' => 'Test Video Upload',
                        'privacy_level' => 'PUBLIC',
                        'disable_duet' => false,
                        'disable_comment' => false,
                        'disable_stitch' => false
                    )
                )
            ));
            
            echo "Step 3: Video post initialization response:\n";
            print_r($postResponse);
        } else {
            echo "Error: Video file not found\n";
        }
    } else {
        echo "Error getting access token:\n";
        print_r($tokenResponse);
    }
}