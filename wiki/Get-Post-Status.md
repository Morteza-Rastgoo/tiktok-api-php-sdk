When working with the TikTok API and making posts, it is crucial to check the status of posts you are making through the TikTok API and handling what happens next based on the status of a post. To get the status of a post we need an access token and either the video.upload scope or the video.publish scope. The status could comeback as still processing, failed, completed, and a few others (see official documentation) so it is important your app is setup to handle each of these statuses. 

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=ozosuDqjrh8TiEBh&t=361)

## TikTok Get Post Status
Returns the status of a single post.
```php
use TikTok\Post\Post;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

$params = array(
    Fields::PUBLISH_ID => '<POST_ID>' 
);

// get post status
$fetchStatus = $post->fetchStatus( $params );
```