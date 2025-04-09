<?php

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Post\Post;

session_start();

// Check if we have an access token
if (!isset($_SESSION['access_token'])) {
    die('Error: No access token found. Please authenticate first.');
}

// Initialize Post instance with access token
$config = array('access_token' => $_SESSION['access_token']);
$post = new Post($config);

// Get video URL from request or use a test video URL
$videoUrl = isset($_POST['video_url']) ? $_POST['video_url'] : 'https://download.samplelib.com/mp4/sample-5s.mp4';

// Validate video URL
if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
    die('Error: Invalid video URL format');
}

try {
    // Set up post parameters
    $postInfo = json_encode(array(
        'post_info' => array(
            'title' => 'Test Video Upload from URL',
            'privacy_level' => 'SELF_ONLY', // Required for sandbox mode
            'disable_duet' => true, // Recommended for sandbox testing
            'disable_stitch' => true, // Recommended for sandbox testing
            'disable_comment' => true, // Recommended for sandbox testing
            'disable_duet' => false,
            'disable_comment' => false,
            'disable_stitch' => false,
            'video_cover_timestamp' => 0
        ),
        'source_info' => array(
            'source' => 'PULL_FROM_URL',
            'video_url' => $videoUrl
        )
    ));

    // Make the post request
    $response = $post->createPost($postInfo);

    // Check the response
    if (isset($response['data']['publish_id'])) {
        $publishId = $response['data']['publish_id'];
        echo "Video upload initiated successfully!<br>";
        echo "Publish ID: " . htmlspecialchars($publishId) . "<br>";
        
        // You can use this publish_id to check the status of the upload
        echo "<a href='check_post_status.php?publish_id=" . urlencode($publishId) . "'>Check Upload Status</a>";
    } else {
        echo "Error: Upload failed<br>";
        echo "Response: " . json_encode($response, JSON_PRETTY_PRINT) . "<br>";
        if (isset($response['error'])) {
            echo "Error details: " . htmlspecialchars($response['error']['message']) . "<br>";
        }
        echo "Response: " . htmlspecialchars(print_r($response, true));
    }
} catch (\Exception $e) {
    echo "Error uploading video: " . htmlspecialchars($e->getMessage());
}