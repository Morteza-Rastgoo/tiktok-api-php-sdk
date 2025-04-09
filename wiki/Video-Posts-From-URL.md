This endpoint allows you to post directly or upload videos to TikTok. We need an access token and the scope video.publish.

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=wpw4k3-aTZ3J-5He&t=99)

## TikTok Post Videos From URL
Returns info on the post "publish_id" for your post along with any errors that might have occurred. Specify all post related fields and values in the $params array.
```php
use TikTok\Post\Post;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

$params = array(
    Fields::POST_INFO => json_encode(array(
        Fields::PRIVACY_LEVEL => 'SELF_ONLY',
        Fields::TITLE => '<POST_TITLE>',
        Fields::VIDEO_COVER_TIMESTAMP_MS => 1000 // spot in video to use as cover photo
    )),
    Fields::SOURCE_INFO => json_encode( array(
        Fields::SOURCE => 'PULL_FROM_URL',
        Fields::VIDEO_URL => '<VIDEO_URL>' // video URL that is publicly accessible
    ) )
);

// post video to tiktok
$publish = $post->publish( $params );
```