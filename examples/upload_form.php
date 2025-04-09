<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['access_token'])) {
    header('Location: login_and_post.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>TikTok Video Upload</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"] { width: 100%; padding: 8px; }
        button { background: #00f2ea; color: #000; padding: 10px 20px; border: none; cursor: pointer; }
        .note { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Video to TikTok</h1>
        
        <div class="note">
            <strong>Note:</strong> The video URL must be publicly accessible and in a supported format (MP4 recommended).
            For testing, you can use: https://download.samplelib.com/mp4/sample-5s.mp4
        </div>

        <form action="post_video_from_url.php" method="post">
            <div class="form-group">
                <label for="video_url">Video URL:</label>
                <input type="text" id="video_url" name="video_url" 
                       placeholder="https://example.com/video.mp4" 
                       value="https://download.samplelib.com/mp4/sample-5s.mp4" required>
            </div>
            <button type="submit">Upload to TikTok</button>
        </form>
    </div>
</body>
</html>