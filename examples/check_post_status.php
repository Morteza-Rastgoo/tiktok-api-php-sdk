<?php

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Post\Post;

session_start();

// Check if we have an access token and publish_id
if (!isset($_SESSION['access_token'])) {
    die('Error: No access token found. Please authenticate first.');
}

if (!isset($_GET['publish_id'])) {
    die('Error: No publish_id provided.');
}

// Initialize Post instance with access token
$config = array('access_token' => $_SESSION['access_token']);
$post = new Post($config);

try {
    // Get the publish ID from the URL parameter
    $publishId = $_GET['publish_id'];
    
    // Query the post status
    $response = $post->queryPostStatus($publishId);
    
    // Set up the refresh interval (in seconds)
    $refreshInterval = 5;
    
    // Output the status in a nice format
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>TikTok Upload Status</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
            .container { max-width: 800px; margin: 0 auto; }
            .status-box {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 4px;
                margin: 20px 0;
                border: 1px solid #ddd;
            }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .pending { color: #ffc107; }
        </style>
        <?php if ($response['data']['status'] === 'IN_PROGRESS') { ?>
        <meta http-equiv="refresh" content="<?php echo $refreshInterval; ?>">
        <?php } ?>
    </head>
    <body>
        <div class="container">
            <h1>Upload Status</h1>
            <div class="status-box">
                <?php
                if (isset($response['data'])) {
                    $status = $response['data']['status'];
                    $statusClass = '';
                    
                    switch ($status) {
                        case 'IN_PROGRESS':
                            $statusClass = 'pending';
                            echo "<h2 class='$statusClass'>⏳ Upload in Progress</h2>";
                            echo "<p>Your video is being processed. This page will refresh every $refreshInterval seconds.</p>";
                            break;
                            
                        case 'SUCCESS':
                            $statusClass = 'success';
                            echo "<h2 class='$statusClass'>✓ Upload Complete!</h2>";
                            if (isset($response['data']['share_url'])) {
                                echo "<p>Video URL: <a href='" . htmlspecialchars($response['data']['share_url']) . "' target='_blank'>" . 
                                     htmlspecialchars($response['data']['share_url']) . "</a></p>";
                            }
                            break;
                            
                        case 'FAILED':
                            $statusClass = 'error';
                            echo "<h2 class='$statusClass'>✗ Upload Failed</h2>";
                            if (isset($response['data']['error_code'])) {
                                echo "<p>Error Code: " . htmlspecialchars($response['data']['error_code']) . "</p>";
                            }
                            break;
                            
                        default:
                            echo "<h2>Unknown Status: " . htmlspecialchars($status) . "</h2>";
                    }
                    
                    echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";
                } else {
                    echo "<h2 class='error'>Error: Invalid Response</h2>";
                    echo "<pre>" . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT)) . "</pre>";
                }
                ?>
            </div>
            <p><a href="post_video_from_url.php">← Back to Upload Page</a></p>
        </div>
    </body>
    </html>
    <?php
} catch (\Exception $e) {
    echo "Error checking status: " . htmlspecialchars($e->getMessage());
}