To query a users videos we need is an access token with the scope video.list, along with the fields we want to request for each video. If you don't pass in any fields, the SDK will request all possible fields for each video.

[YouTube Tutorial Playlist](https://www.youtube.com/watch?v=a3S4YhAU5Bk&list=PL0glhsZ01I-BWvKCRnZtkG4bBoOB3LivT&index=4)

## TikTok Query Videos
Given an authorized user and a list of video IDs, the endpoint verifies that the videos belong to the user and returns video details. It can be used to refresh the given videos' cover image URL TTL. Up to 20 video IDs can be included per request. Omit the $fields parameter and the SDK will return all possible video fields as defined in the Video class $fields array. Specify the $fields parameter to customize the fields you want back.
```php
use TikTok\Video\Video;
use TikTok\Request\Params;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>'
);

// instantiate the video
$video = new Video( $config );

$videoIds = array( // videos ids we want info (max 20 ids)
    '<TIKTOK_VIDEO_ID>',
    '<TIKTOK_VIDEO_ID>',
    '<TIKTOK_VIDEO_ID>',
    // ...
);

$fields = array( // customize fields for the videos
    Fields::ID,
    Fields::CREATE_TIME,
    Fields::TITLE,
    Fields::COVER_IMAGE_URL,
    Fields::SHARE_URL,
    Fields::VIDEO_DESCRIPTION,
    Fields::DURATION,
    Fields::HEIGHT,
    Fields::WIDTH,
    Fields::TITLE,
    Fields::EMBED_HTML,
    Fields::EMBED_LINK,
    Fields::LIKE_COUNT,
    Fields::COMMENT_COUNT,
    Fields::SHARE_COUNT,
    Fields::VIEW_COUNT
);

// get video list (fields can be omitted for default functionality)
$videoQuery = $video->query( $videoIds, $fields );