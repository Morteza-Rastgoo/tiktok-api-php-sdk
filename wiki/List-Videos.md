To get a list of a users videos we need is an access token with the scope video.list, along with the fields we want to request for each video. If you don't pass in any fields, the sdk will request all possible fields for each video.

[YouTube Tutorial Playlist](https://www.youtube.com/watch?v=a3S4YhAU5Bk&list=PL0glhsZ01I-BWvKCRnZtkG4bBoOB3LivT&index=4)

## TikTok List Videos
Returns a paginated list for the given user's public TikTok video posts, sorted by create_time in descending order. Omit the $params array and the api will return the default otherwise you can specify request params. Omit the $fields parameter and the SDK will return all possible video fields as defined in the Video class $fields array. Specify the $fields parameter to customize the fields you want back. If the response "cursor_next" then you need to add on 'cursor' with the value from the response 'cursor_next' to your $params and then call getSelf() again to get the next batch of videos.
```php
use TikTok\Video\Video;
use TikTok\Request\Params;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>'
);

// instantiate the video
$video = new Video( $config );

$params = array( // customize params for the request
    'max_count' => 5 // customize how many videos we want in each request (max is 20)
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

// get video list (params and fields can both be omitted for default functionality)
$videoList = $video->getList( $params, $fields );

if ( $videoList['cursor_next'] ) { // we have more videos
    // add on cursor for the next request
    $params['cursor'] = $videoList['cursor_next'];

    // get video list with new cursor (next page)
    $videoList = $video->getList( $params, $fields );
}
```