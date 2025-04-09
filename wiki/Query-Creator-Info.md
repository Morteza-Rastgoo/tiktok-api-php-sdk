This API returns profile and permission information of the current user. When rendering the Export to TikTok page, your app must invoke the API and use the latest creator information returned to display the account's available privacy level options and video/photo interaction settings.

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=0_Db73aphzHto7Ka&t=842)

## TikTok Query Creator Info
Returns profile and permission information of the current user.
```php
use TikTok\Post\Post;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

// get creator info
$creatorInfo = $post->queryCreatorInfo();
```