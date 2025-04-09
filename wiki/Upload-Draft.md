Use this endpoint to upload a video without posting it. It will appear in the drafts folder on TikTok. Requires a user access token and the video.upload scope.

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=4jAL491B4Lhyf8pp&t=751)

## TikTok Upload Draft
Returns info on the post "publish_id" for your post along with any errors that might have occurred.
```php
use TikTok\Post\Post;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

$params = array(
    Fields::SOURCE_INFO => json_encode( array(
        Fields::SOURCE => 'PULL_FROM_URL',
       Fields::VIDEO_URL => '<VIDEO_URL>' // video URL that is publicly accessible
    ) )
);

// draft video to tiktok
$draft = $post->draft( $params );
```