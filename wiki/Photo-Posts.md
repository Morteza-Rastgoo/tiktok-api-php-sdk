This endpoint allows you to post directly or upload videos to TikTok. We need an access token and the scope video.publish or video.upload.

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=J6Okubn0Ocd9YVBA&t=651)

## TikTok Post Photos
Allows for up to 35 imgage URLs that are publicly accessible and verified with your app. Returns info on the post "publish_id" for your post along with any errors that might have occurred. Specify all post related fields and values in the $params array.
```php
use TikTok\Post\Post;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

$params = array(
    Fields::POST_INFO => json_encode( array(
        Fields::PRIVACY_LEVEL => 'SELF_ONLY',
        Fields::TITLE => '<POST_TITLE>'
    ) ),
    Fields::SOURCE_INFO => json_encode( array(
        Fields::SOURCE => 'PULL_FROM_URL',
        Fields::PHOTO_COVER_INDEX => 1, // index of the image to use as the cover photo in the photo images array
        Fields::PHOTO_IMAGES => array( // up to 35 URLs that are publicly accessible
            '<URL_TO_IMAGE_1>',
            '<URL_TO_IMAGE_2>'
        )
    ) ),
    Fields::POST_MODE => 'DIRECT_POST',
    Fields::MEDIA_TYPE => 'PHOTO'
);

// post photos to tiktok
$photos = $post->photos( $params );
```