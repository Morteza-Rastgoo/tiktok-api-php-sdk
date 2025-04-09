<?php
// Set PHP upload limits for video files
ini_set('upload_max_filesize', '50M');
ini_set('post_max_size', '52M');

// Enable output buffering to prevent premature output
ob_start();

// Standardized session configuration - must be first
ini_set('session.cookie_lifetime', 3600); // 1 hour
ini_set('session.gc_maxlifetime', 3600); // 1 hour
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

// Initialize or validate session state
if (empty($_SESSION['tiktok_auth_state'])) {
    $_SESSION['tiktok_auth_state'] = bin2hex(random_bytes(16));
    error_log('Callback.php - Initialized new auth state: ' . $_SESSION['tiktok_auth_state']);
}

// Log session information for debugging
error_log('Callback.php - Session ID: ' . session_id());
error_log('Callback.php - Session data: ' . json_encode($_SESSION));

require_once __DIR__ . '/../src/TikTok/autoload.php';

use TikTok\Authentication\Authentication;
use TikTok\Video\Video;

// Check for error parameters

// Get video file or URL from request
$videoUrl = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
$videoFile = isset($_FILES['video_file']) ? $_FILES['video_file'] : null;

// Validate input
if (!empty($videoUrl)) {
    if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
        $error = 'Invalid video URL format';
    } elseif (!preg_match('/\.(mp4|mov|avi)$/i', $videoUrl)) {
        $error = 'Invalid video URL: must end with .mp4, .mov or .avi';
    }
} elseif ($videoFile) {
    if ($videoFile['error'] !== UPLOAD_ERR_OK) {
        $error = 'File upload error: ' . $videoFile['error'];
    } elseif (!in_array($videoFile['type'], ['video/mp4', 'video/quicktime', 'video/x-msvideo'])) {
        $error = 'Invalid video format. Allowed formats: MP4, MOV, AVI';
    }
} else {
    $error = 'Please provide either a video URL or upload a file';
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
    $errorType = isset($_GET['error_type']) ? $_GET['error_type'] : '';
    $errorMessage = 'An error occurred during authentication.';
    
    if ($error === 'unauthorized_client' && $errorType === 'client_key') {
        $errorMessage = 'Error: Invalid client_key. Please check that your API credentials are correct in the .env file and match the registered details in the TikTok developer portal.';
    }
    
    // Display error on current page if it's a video upload error
    if (strpos($error, 'video') !== false || strpos($error, 'upload') !== false) {
        $error = $errorMessage;
    } else {
        // Redirect back to login page with error message
        header('Location: login_and_post.php?error=' . urlencode($error) . '&error_type=' . urlencode($errorType));
        exit;
    }
}

// Handle video upload if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['access_token'])) {
    error_log('Callback.php - Starting video upload process');
    error_log('Callback.php - Session data at upload start: ' . json_encode($_SESSION));
    
    $videoInstance = new Video(['access_token' => $_SESSION['access_token']]);
    $uploadResult = null;
    $error = null;

    // Handle both file upload and URL cases
    if (!empty($videoUrl)) {
        try {
            error_log('Callback.php - Processing video URL');
            
            // Validate URL format
            if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                throw new \Exception('Invalid video URL format');
            }
            
            // Download video from URL
            $tempFile = tempnam(sys_get_temp_dir(), 'tiktok_');
            file_put_contents($tempFile, file_get_contents($videoUrl));
            
            $videoPath = $tempFile;
            $videoSize = filesize($tempFile);
            $videoType = mime_content_type($tempFile);
            
        } catch (\Exception $e) {
            $error = 'Error processing video URL: ' . $e->getMessage();
            error_log('Callback.php - URL processing error: ' . $e->getMessage());
        }
    } else if (!isset($_FILES['video_file'])) {
        $error = 'No video file or URL was provided';
        error_log('Callback.php - No video file or URL was provided');
    } else if ($_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
        switch($_FILES['video_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error = 'The uploaded file exceeds the upload_max_filesize directive';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error = 'The uploaded file exceeds the MAX_FILE_SIZE directive';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'The uploaded file was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            default:
                $error = 'Unknown upload error';
        }
        error_log('Callback.php - File upload error: ' . $error);
    } else {
        try {
            error_log('Callback.php - Processing uploaded video file');
            error_log('Callback.php - File info: ' . json_encode($_FILES['video_file']));
            
            $videoPath = $_FILES['video_file']['tmp_name'];
            $videoSize = $_FILES['video_file']['size'];
            $videoType = $_FILES['video_file']['type'];
            
            // Validate file type
            $allowedTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
            if (!in_array($videoType, $allowedTypes)) {
                error_log('Callback.php - Invalid video format: ' . $videoType);
                throw new \Exception('Invalid video format. Allowed formats: MP4, MOV, AVI');
            }
            
            // Validate file size (max 50MB)
            if ($videoSize > 50 * 1024 * 1024) {
                error_log('Callback.php - Video file size exceeds limit: ' . $videoSize);
                throw new \Exception('Video file size must not exceed 50MB');
            }
            
            error_log('Callback.php - Attempting to upload video to TikTok API');
            $uploadResult = $videoInstance->uploadVideo([
                'video' => $videoPath,
                'title' => $_POST['title'] ?? 'My TikTok Video',
                'privacy_level' => 'SELF_ONLY' // For sandbox mode
            ]);
            
            error_log('Callback.php - TikTok API response: ' . json_encode($uploadResult));
            
            if (!isset($uploadResult['data'])) {
                throw new \Exception('Upload failed: Invalid response from TikTok API');
            }
            
            error_log('Callback.php - Video uploaded successfully');
        } catch (\Exception $e) {
            $error = 'Error uploading video: ' . $e->getMessage();
            error_log('Callback.php - TikTok upload error: ' . $e->getMessage());
            error_log('Callback.php - Stack trace: ' . $e->getTraceAsString());
        }
    }
}

// Get TikTok app credentials from environment variables
$config = array(
    'client_key' => getenv('TIKTOK_CLIENT_KEY'),
    'client_secret' => getenv('TIKTOK_CLIENT_SECRET')
);

// Initialize Authentication
try {
    $auth = new Authentication($config);
} catch (\Exception $e) {
    header('Location: login_and_post.php?error=authentication_failed');
    exit;
}

// State parameter validation
if (!isset($_GET['state']) || empty($_GET['state']) || !isset($_SESSION['tiktok_auth_state']) || empty($_SESSION['tiktok_auth_state'])) {
    unset($_SESSION['tiktok_auth_state']);
    header('Location: login_and_post.php?error=invalid_state&message=' . urlencode('Invalid or missing state parameter'));
    exit;
}

if ($_GET['state'] !== $_SESSION['tiktok_auth_state']) {
    error_log('State mismatch: Session state: ' . $_SESSION['tiktok_auth_state'] . ', GET state: ' . $_GET['state']);
    unset($_SESSION['tiktok_auth_state']);
    header('Location: login_and_post.php?error=invalid_state&message=' . urlencode('State parameter mismatch'));
    exit;
}

// Get the authorization code from the callback
$authorizationCode = isset($_GET['code']) ? $_GET['code'] : '';
$domain = getenv('APP_DOMAIN') ?: 'tik.khosousi.com';
$redirectUri = 'https://' . $domain . '/app/callback.php';

if ($authorizationCode) {
    // Exchange the code for an access token
    $tokenResponse = $auth->getAccessTokenFromCode($authorizationCode, $redirectUri);
    
    if (isset($tokenResponse['access_token'])) {
        $success = true;
        $accessToken = $tokenResponse['access_token'];
        $_SESSION['access_token'] = $accessToken; // Store token in session
        $expiresIn = isset($tokenResponse['expires_in']) ? $tokenResponse['expires_in'] : 'N/A';
        $refreshToken = isset($tokenResponse['refresh_token']) ? $tokenResponse['refresh_token'] : 'N/A';
        
        // Generate new state for next authentication attempt
        $_SESSION['tiktok_auth_state'] = bin2hex(random_bytes(16));
    } else {
        $error = 'Failed to get access token. Please try again.';
    }
} else {
    $error = 'No authorization code received from TikTok.';

    
    // Error has already been set above
    header('Location: login_and_post.php?error=' . urlencode($error));
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title>TikTok Authentication Result</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .token-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            word-break: break-all;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
        .upload-form {
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background: #fe2c55;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #e62a4d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>TikTok Authentication Result</h1>
        
        <?php if (isset($success) && $success): ?>
            <h2 class="success">✓ Authentication Successful!</h2>
            <div class="token-info">
                <p><strong>Access Token:</strong><br><?php echo htmlspecialchars($accessToken); ?></p>
                <p><strong>Expires In:</strong> <?php echo htmlspecialchars($expiresIn); ?> seconds</p>
                <p><strong>Refresh Token:</strong><br><?php echo htmlspecialchars($refreshToken); ?></p>
            </div>
            <p>You can now upload videos to TikTok (Sandbox Mode).</p>

            <?php if (isset($uploadResult)): ?>
                <div class="success">
                    <p>Video uploaded successfully!</p>
                    <pre><?php echo htmlspecialchars(json_encode($uploadResult, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="error">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <div class="upload-form">
                <h3>Upload Video (Sandbox Mode)</h3>
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
            </div>
        <?php else: ?>
            <h2 class="error">✗ Authentication Failed</h2>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <a href="login_and_post.php" class="back-link">← Back to Authentication Page</a>
    </div>
</body>
</html>