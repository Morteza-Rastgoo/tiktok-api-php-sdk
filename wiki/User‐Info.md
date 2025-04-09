To get user info all we need is an access token, and the fields we want to request. The fields you want to request are dependent on you having requested the correct scopes in your TikTok app. For example, if you do not have the scope "user.info.stats" approved in your application, the api will not allow or return you fields that require that scope such as "follower_count".

[YouTube Tutorial](https://www.youtube.com/watch?v=W9QmRFqLpuA&list=PL0glhsZ01I-BWvKCRnZtkG4bBoOB3LivT&index=3)

## TikTok Get User Info
Returns some basic information for a given TikTok user.
```php
use TikTok\User\User;
use TikTok\Request\Params;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>'
);

// instantiate the user
$user = new User( $config );

$params = Params::getFieldsParam( // params keys the field array and implodes array to string on comma
    array( // user fields to request
        'open_id', 		// scope user.info.basic
	'union_id', 		// scope user.info.basic
	'avatar_url', 		// scope user.info.basic
	'avatar_url_100',       // scope user.info.basic
	'avatar_large_url', 	// scope user.info.basic
	'display_name', 	// scope user.info.basic
	'bio_description', 	// scope user.info.profile
	'profile_deep_link', 	// scope user.info.profile
	'is_verified', 		// scope user.info.profile
	'follower_count', 	// scope user.info.stats
	'following_count', 	// scope user.info.stats
	'likes_count', 		// scope user.info.stats
	'video_count' 		// scope user.info.stats
    )
);

// get user info
$userInfo = $user->getSelf( $params );
```