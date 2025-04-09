To get an access token, users must first login through the TikTok login dialog. TikTok will then send the user back to your redirect uri with a code variable in the url which you then exchange for an access token. We can also refresh or revoke an access token.


[YouTube Tutorial](https://www.youtube.com/watch?v=XLWU1uiPhLA&list=PL0glhsZ01I-BWvKCRnZtkG4bBoOB3LivT&index=2)

## TikTok Login Kit Dialog
Display a link for the user to click on and login with TikTok. Once the user logs in with TikTok, TikTok will send them to the redirect uri. The redirect uri in your code must also be entered in the TikTok Login settings of you app under "Redirect URIs" section under "Login Kit".
```php
use TikTok\Authentication\Authentication;

$authentication = new Authentication( array( // instantiate authentication
    'client_key' => '<CLIENT_KEY>', // client key from your app
    'client_secret' => '<CLIENT_SECRET>' // client secret from your app
) );

// uri TikTok will send the user to after they login that must match what you have in your app dashboard
$redirectUri = 'https://path/to/tiktok/login/redirect.php';

$scopes = array( // a list of approved scopes by tiktok for your app
    'user.info.basic',
    'user.info.profile',
    'user.info.stats',
    'video.publish',
    'video.upload',
    'video.list'
);

// get TikTok login url
$authenticationUrl = $authentication->getAuthenticationUrl( $redirectUri, $scopes );

// display login dialog link
echo '<a href="' . $authenticationUrl . '">' .
    '<img src="/path/to/tiktok/logo.png" /> Continue With TikTok' .
'</a>';
```
## TikTok User Access Token
Once the user logs in through TikTok, TikTokdirects them to your redirect uri and appends on a code. For the above example, once the user logs in, TikTok would redirect them to "[https://path/to/tiktok/login/redirect.php?code={code}](https://path/to/tiktok/login/redirect.php?code=%7Bcode%7D)". We then can exchange this code for an access token.
```php
use TikTok\Authentication\Authentication;

$authentication = new Authentication( array( // instantiate authentication
    'client_key' => '<CLIENT_KEY>', // client key from your app
    'client_secret' => '<CLIENT_SECRET>' // client secret from your app
) );

// uri TikTok will send the user to after they login that must match what you have in your app dashboard
$redirectUri = 'https://path/to/tiktok/login/redirect.php';

// get access token from code
$tokenFromCode = $authentication->getAccessTokenFromCode( $_GET['code'], $redirectUri );

// access token 
$userToken = $tokenFromCode['access_token'];
```
## Refresh TikTok Access Token
Although the fetched access_token expires within 24 hours, it can be refreshed without user consent. The developer's back-end server can schedule background jobs to keep tokens up to date.
```php
use TikTok\Authentication\Authentication;

$authentication = new Authentication( array( // instantiate authentication
    'client_key' => '<CLIENT_KEY>', // client key from your app
    'client_secret' => '<CLIENT_SECRET>' // client secret from your app
) );

// refresh token
$tokenRefresh = $authentication->getRefreshAccessToken( '<ACCESS_TOKEN>' );

// access token 
$userToken = $tokenRefresh['access_token'];
```
## Revoke TikTok Access Token
When a user wants to disconnect your application from TikTok, you can revoke their tokens so the user will no longer see your application on the Manage app permissions page of the TikTok for Developers website.
```php
use TikTok\Authentication\Authentication;

$authentication = new Authentication( array( // instantiate authentication
    'client_key' => '<CLIENT_KEY>', // client key from your app
    'client_secret' => '<CLIENT_SECRET>' // client secret from your app
) );

// revoke token
$revokeToken = $authentication->revokeAccessToken( '<ACCESS_TOKEN>' );
```
## TikTok Client Access Token
Client access token is a type of access token that does not need user authorization. This is typically used by clients to access resources about themselves or a TikTok application, rather than to access a user's resources. The use cases are to access [Research API](https://developers.tiktok.com/products/research-api/) and [Commercial Content API](https://developers.tiktok.com/products/commercial-content-api/).
```php
use TikTok\Authentication\Authentication;

$authentication = new Authentication( array( // instantiate authentication
    'client_key' => '<CLIENT_KEY>', // client key from your app
    'client_secret' => '<CLIENT_SECRET>' // client secret from your app
) );

// get client token
$newClientToken = $authentication->getClientAccessToken();

// client access token 
$clientToken = $newClientToken['access_token'];
```