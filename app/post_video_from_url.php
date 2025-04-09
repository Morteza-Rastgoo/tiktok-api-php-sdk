<?php
// Prevent any output before session starts
ob_start();

// Initialize session first
session_name('tiktok_sdk_session');

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    if ($envVars === false) {
        die('Error loading .env file');
    }
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

// Get domain from environment
$domain = getenv('APP_DOMAIN') ?: 'localhost';

// Configure session parameters
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $domain,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Start session before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} else {
    session_regenerate_id(true);
}

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Post\Post;

// Check if we have an access token
if (!isset($_SESSION['access_token'])) {
    header('Location: login_and_post.php?error=no_token');
    exit;
}

// Initialize Post instance with access token
$config = array('access_token' => $_SESSION['access_token']);
$post = new Post($config);

// Get video file or URL from request
$videoUrl = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
$videoFile = isset($_FILES['video_file']) ? $_FILES['video_file'] : null;

// Validate input
if (!empty($videoUrl) && !filter_var($videoUrl, FILTER_VALIDATE_URL)) {
    $error = 'Invalid video URL format';
} elseif ($videoFile && $videoFile['error'] !== UPLOAD_ERR_OK) {
    $error = 'File upload error: ' . $videoFile['error'];
} elseif (!$videoUrl && !$videoFile) {
    $error = 'Please provide either a video URL or upload a file';
}

$success = false;
$error = '';
$publishId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$error)) {
    try {
        // Set up common post parameters
        $postInfo = array(
            'post_info' => array(
                'title' => isset($_POST['title']) ? $_POST['title'] : 'Video Upload',
                'privacy_level' => 'SELF_ONLY',
                'disable_duet' => false,
                'disable_comment' => false,
                'disable_stitch' => false,
                'video_cover_timestamp_ms' => 1000
            ),
            'source_info' => array(
                'source' => $videoFile ? 'FILE_UPLOAD' : 'PULL_FROM_URL',
                'video_url' => $videoUrl
            )
        );

        // Add file-specific parameters if uploading a file
        if ($videoFile) {
            $postInfo['source_info']['video_size'] = $videoFile['size'];
            $postInfo['source_info']['chunk_size'] = $videoFile['size'];
            $postInfo['source_info']['total_chunk_count'] = 1;
        }
        // Make the post request
        $response = $post->publish($postInfo);

        // Check the response
        if (isset($response['data']['publish_id'])) {
            $success = true;
            $publishId = $response['data']['publish_id'];
        } else {
            $error = isset($response['error']['message']) 
                ? $response['error']['message'] 
                : 'Upload failed. Please try again.';
        }
    } catch (\Exception $e) {
        $error = 'Error uploading video: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TikTok Video Upload</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .submit-btn {
            background: #fe2c55;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover { background: #e62a4d; }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Video to TikTok</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <h2>✓ Upload Initiated Successfully!</h2>
                <p>Publish ID: <?php echo htmlspecialchars($publishId); ?></p>
                <p><a href="check_post_status.php?publish_id=<?php echo urlencode($publishId); ?>">Check Upload Status</a></p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error">
                <p>Error: <?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Video Title:</label>
                <input type="text" id="title" name="title" placeholder="Enter video title" required>
            </div>
            <div class="form-group">
                <label for="video_url">Video URL (or upload file below):</label>
                <input type="url" id="video_url" name="video_url" placeholder="Enter video URL">
            </div>
            <div class="form-group">
                <label for="video_file">Or Upload Video File:</label>
                <input type="file" id="video_file" name="video_file" accept="video/mp4,video/quicktime">
            </div>
            <button type="submit" class="submit-btn">Upload Video</button>
        </form>
        
        <a href="login_and_post.php" class="back-link">← Back to Main Page</a>
    </div>
</body>
</html>