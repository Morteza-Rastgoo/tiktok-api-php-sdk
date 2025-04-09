# tiktok-api-php-sdk

================================================
File: README.md
================================================
# tiktok-api-php-sdk

This repository contains the open source PHP SDK that allows you to access the TikTok API from your PHP app.

## Installation

### Composer

Run this command:

```
composer require jstolpe/tiktok-api-php-sdk
```

Require the the autoloader.

```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed
```

### No Composer

Get the repository

```
git clone git@github.com:jstolpe/tiktok-api-php-sdk.git
```

Require the custom autoloader.

```php
require_once '/tiktok-api-php-sdk/src/tiktok/autoload.php'; // change path as needed
```



================================================
File: composer.json
================================================
{
    "name": "jstolpe/tiktok-api-php-sdk",
    "description": "TikTok API PHP SDK",
    "keywords": [
        "tiktok", 
        "tiktok api", 
        "api", 
        "sdk", 
        "php", 
        "tiktok api php sdk"
    ],
    "type": "library",
    "homepage": "https://github.com/jstolpe/tiktok-api-php-sdk",
    "license": "MIT",
    "authors": [
        {
            "name": "Justin Stolpe",
            "homepage": "https://github.com/jstolpe/tiktok-api-php-sdk"
        }
    ],
    "require": {
        "php": "^5.6|^7.0|^8.0"
    },
    "autoload": {
        "psr-4": {
            "TikTok\\": "src/TikTok/"
        }
    }
}



================================================
File: LICENSE
================================================
MIT License

Copyright (c) 2024 Justin Stolpe

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.



================================================
File: src/TikTok/autoload.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Register the autoloader for the TikTok API PHP SDK classes.
 *
 * @param string $class rhe fully-qualified class name.
 * @return void
 */
spl_autoload_register( function ( $class ) {
    // project-specific namespace prefix
    $prefix = 'TikTok\\';

    // require our file
    require str_replace( '\\', '/', substr( $class, strlen( $prefix ) )) . '.php';
} );
?>


================================================
File: src/TikTok/TikTok.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok;

// other classes to use
use TikTok\Request\Request;
use TikTok\Request\Curl;

/**
 * TikTok
 *
 * Core functionality for talking to the TikTok API.
 * Developer Docs: https://developers.tiktok.com/doc/overview/
 *
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class TikTok {
    /**
     * @const string Default Graph API version for requests.
     */
    const DEFAULT_GRAPH_VERSION = 'v2';

    /**
     * @var string $graphVersion the graph version we want to use.
     */
    protected $graphVersion;

    /**
     * @var object $client the client service.
     */
    protected $client;

    /**
     * @var string $accessToken access token to use with requests.
     */
    protected $accessToken;

    /**
     * @var Request $request the request to the api.
     */
    protected $request = '';

    /**
     * @var string $cursor cursor next for more info
     */
    public $cursorNext = '';

    /**
     * Contructor for instantiating a new TikTok object.
     *
     * @param array $config for the class
     * @return void
     */
    public function __construct( $config ) {
        // set our access token
        $this->setAccessToken( isset( $config['access_token'] ) ? $config['access_token'] : '' );

        // instantiate the client
        $this->client = new Curl();

        // set graph version
        $this->graphVersion = isset( $config['graph_version'] ) ? $config['graph_version'] : self::DEFAULT_GRAPH_VERSION;
    }

    /**
     * Sends a GET request returns the result.
     *
     * @param array $params params for the GET request.
     * @return response.
     */
    public function get( $params ) {
        // check for params
        $endpointParams = isset( $params['params'] ) ? $params['params'] : array();

        // perform GET request
        return $this->sendRequest( Request::METHOD_GET, $params['endpoint'], $endpointParams );
    }

    /**
     * Sends a POST request and returns the result.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function post( $params ) {
        // check for params
        $endpointParams = isset( $params['params'] ) ? $params['params'] : array();

        // perform POST request
        return $this->sendRequest( Request::METHOD_POST, $params['endpoint'], $endpointParams );
    }

    /**
     * Send a custom GET request to the API and returns the result.
     *
     * @param string $customUrl the entire url for the request.
     * @param string $requestType type of request.
     * @param array $headers request headers.
     * @param array $file file being uploaded.
     * @return response.
     */
    public function sendCustomRequest( $customUrl, $requestType = Request::METHOD_GET, $headers = array(), $file = array() ) {
        // create our request
        $this->request = new Request( $requestType );

        // set our custom url for the request
        $this->request->setUrl( $this->graphVersion, $customUrl );

        // set headers
        $this->request->setHeaders( $headers );

        // set files
        $this->request->setFile( $file );

        // return the response
        $response = $this->client->send( $this->request );

        // append the request to the response
        $response['debug'] = $this;

        // return the response
        return $response;
    }

    /**
     * Send a request to the API and returns the result.
     *
     * @param string $method HTTP method.
     * @param string $endpoint endpoint for the request.
     * @param string $params parameters for the endpoint.
     * @return response.
     */
    public function sendRequest( $method, $endpoint, $params ) {
        // create our request
        $this->request = new Request( $method, $endpoint, $params, $this->graphVersion, $this->accessToken );

        // send the request to the client for processing
        $response = $this->client->send( $this->request );

        // set cursors
        $this->setCursors( $response );

        // append the request to the response
        $response['debug'] = $this;

        // return the response
        return $response;
    }

    /**
     * Set the access token.
     *
     * @param string $accessToken set the access token.
     * @return void.
     */
    public function setAccessToken( $accessToken ) {
        $this->accessToken = $accessToken;
    }

    /**
     * Set cursor.
     *
     * @param array &$response response from the api.
     * @return void.
     */
    public function setCursors( &$response ) {
        if ( !empty( $response['data']['has_more'] ) ) { // check for has more
            $this->cursorNext = $response['cursor_next'] = $response['data']['cursor'];
        }
    }
}

?>


================================================
File: src/TikTok/Authentication/Authentication.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Authentication;

// other classes we need to use
use TikTok\TikTok;
use TikTok\Request\Request;
use TikTok\Request\Params;

/**
 * Authentication
 *
 * Perform authentication.
 *     - Endpoints: 
 *          - Authorization
 *              - Docs: https://developers.tiktok.com/doc/login-kit-web/
 *          - /oauth/token/ (user access) POST
 *              - Docs: https://developers.tiktok.com/doc/oauth-user-access-token-management/
 *          - /oauth/revoke/ POST
 *              - Docs: https://developers.tiktok.com/doc/oauth-user-access-token-management/
 *          - /oauth/token/ (client access) POST
 *              - Docs: https://developers.tiktok.com/doc/client-access-token-management/
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Authentication extends TikTok {
    /**
     * @const string grant_type value for authorization_code.
     */
    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';

    /**
     * @const string grant_type value for client_credentials.
     */
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';

    /**
     * @const string grant_type value for refresh_token.
     */
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * @const string response_type value for code.
     */
    const RESPONSE_TYPE_CODE = 'code';

    /**
     * @var string $clientKey client key to use with requests.
     */
    protected $clientKey;

    /**
     * @var string $clientSecret client secret to use with requests.
     */
    protected $clientSecret;

    /**
     * Contructor for instantiating a new TikTok authentication object.
     *
     * @param array $config for the class.
     *      'client_key'     => string client key for the TikTok app
     *      'client_secret'  => string client secret for the TikTok app
     * @return void
     */
    public function __construct( $config ) {
        // call parent for setup
        parent::__construct( $config );

        // set client key
        $this->setClientKey( $config['client_key'] );

        // set client secret
        $this->setClientSecret( $config['client_secret'] );
    }

    /**
     * Return the client key.
     *
     * @return string
     */
    public function getClientKey() {
        return $this->clientKey;
    }

    /**
     * Return the client secret.
     *
     * @return string
     */
    public function getClientSecret() {
        return $this->clientSecret;
    }

    /**
     * Set the client key.
     *
     * @param string $clientKey set the client key.
     * @return void
     */
    public function setClientKey( $clientKey ) {
        $this->clientKey = $clientKey;
    }

    /**
     * Set the client secret.
     *
     * @param string $clientSecret set the client secret.
     * @return void
     */
    public function setClientSecret( $clientSecret ) {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Get an access token from the authorization code.
     *
     * @param string $authorizationCode code returned by tiktok along with the redirect uri.
     * @param string $redirectUri uri the user gets sent to after authenticating with TikTok must match redirect uri set in your app.
     * @return object
     */
    public function getAccessTokenFromCode( $authorizationCode, $redirectUri ) {
        return $this->post( array( // make request
            'endpoint' => '/oauth/token/',
            'params' => array( // params required to generate the authorization url
                Params::CLIENT_KEY => $this->getClientKey(),
                Params::CLIENT_SECRET => $this->getClientSecret(),
                Params::CODE => $authorizationCode,
                Params::GRANT_TYPE => self::GRANT_TYPE_AUTHORIZATION_CODE,
                Params::REDIRECT_URI => $redirectUri
            )
        ) );
    }

    /**
     * Get the url for a user to prompt them with the authorization dialog.
     *
     * @param string $redirectUri uri the user gets sent to after authenticating with TikTok.
     * @param string $scope string a comma separated string of authorization scope.
     * @param string $state this gets passed back from TikTok when the use authenticates, optional.
     * @return string the full authentication url
     */
    public function getAuthenticationUrl( $redirectUri, $scope, $state = '' ) {
        $params = array( // params required to generate the authorization url
            Params::CLIENT_KEY => $this->getClientKey(),
            Params::RESPONSE_TYPE => self::RESPONSE_TYPE_CODE,
            Params::REDIRECT_URI => $redirectUri,
            Params::SCOPE => Params::commaStringToArray( $scope ),
            Params::STATE => $state
        );

        // return the login dialog url
        return Request::BASE_AUTHORIZATION_URL . '/' . $this->graphVersion . '/auth/authorize/' . '?' . http_build_query( $params );
    }

   /**
     * Get a client access token.
     *
     * @return object
     */
    public function getClientAccessToken() {
        return $this->post( array( // make request
            'endpoint' => '/oauth/token/',
            'params' => array( // params required to generate the authorization url
                Params::CLIENT_KEY => $this->getClientKey(),
                Params::CLIENT_SECRET => $this->getClientSecret(),
                Params::GRANT_TYPE => self::GRANT_TYPE_CLIENT_CREDENTIALS
            )
        ) );
    } 

    /**
     * Refresh an access token.
     *
     * @param string $accessToken token to refresh.
     * @return object
     */
    public function getRefreshAccessToken( $accessToken ) {
        return $this->post( array( // make request
            'endpoint' => '/oauth/token/',
            'params' => array( // params required to generate the authorization url
                Params::CLIENT_KEY => $this->getClientKey(),
                Params::CLIENT_SECRET => $this->getClientSecret(),
                Params::GRANT_TYPE => self::GRANT_TYPE_REFRESH_TOKEN,
                Params::REFRESH_TOKEN => $accessToken
            )
        ) );
    }

    /**
     * Revoke access.
     *
     * @param string $accessToken token to revoke.
     * @return object
     */
    public function revokeAccessToken( $accessToken ) {
        return $this->post( array( // make request
            'endpoint' => '/oauth/revoke/',
            'params' => array( // params required to generate the authorization url
                Params::CLIENT_KEY => $this->getClientKey(),
                Params::CLIENT_SECRET => $this->getClientSecret(),
                Params::TOKEN => $accessToken
            )
        ) );
    }
}

?>


================================================
File: src/TikTok/Post/Post.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Post;

// other classes we need to use
use TikTok\TikTok;
use TikTok\Request\Request;

/**
 * Post
 *
 * Content posting.
 *     - Endpoints: 
 *          - /post/publish/video/init/ POST
 *              - Docs: https://developers.tiktok.com/doc/content-posting-api-reference-direct-post/
 *          - /post/publish/inbox/video/init/ POST
 *              - Docs: https://developers.tiktok.com/doc/content-posting-api-reference-upload-video/
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Post extends TikTok {
    /**
     * @const endpoint for the request.
     */
    const ENDPOINT = 'post/publish';

    /**
     * Contructor for instantiating a new object.
     *
     * @param array $config for the class.
     * @return void
     */
    public function __construct( $config ) {
        // call parent for setup
        parent::__construct( $config );
    }

    /**
     * Create draft video.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function draft( $params = array() ) {
        $postParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/inbox/video/init/',
            'params' => $params
        );

        // post request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Post photos.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function photos( $params = array() ) {
        $postParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/content/init/',
            'params' => $params
        );

        // post request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Publish video.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function publish( $params = array() ) {
        $postParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/video/init/',
            'params' => $params
        );

        // post request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Get creator info.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function queryCreatorInfo( $params = array() ) {
        $postParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/creator_info/query/',
            'params' => $params
        );

        // post request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Publish video.
     *
     * @param array $params params for the POST request.
     * @return response.
     */
    public function fetchStatus( $params = array() ) {
        $postParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/status/fetch/',
            'params' => $params
        );

        // post request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Upload file to TikTok Server.
     *
     * @param string $uploadUrl upload_url from publish response.
     * @param array $file params of the file being uploaded.
     * @return response.
     */
    public function uploadFile( $uploadUrl, $file ) {
        // get file size
        $fileSize = fileSize( $file['path'] );

        $headers = array( // set headers for request
            'Content-Range' => 'bytes 0-' . ( $fileSize - 1 ) . '/' . $fileSize,
            'Content-Length' => $fileSize,
            'Content-Type' => $file['mime_type']
        );

        // make request to the api
        $response = $this->sendCustomRequest( $uploadUrl, Request::METHOD_PUT, $headers, $file );

        // return response
        return $response;
    }
}

?>


================================================
File: src/TikTok/Request/Curl.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Request;

// other classes we need to use
use TikTok\Request\Request;

/**
 * Curl
 *
 * Handle curl functionality for requests.
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Curl {
	/**
     * @var object $curl
     */
    protected $curl;

    /**
     * @var int The curl client error code.
     */
    protected $curlErrorCode = 0;
    
	/**
     * @var string $rawResponse The raw response from the server.
     */
    protected $rawResponse;

    /**
     * Perform a curl call.
     * 
     * @param Request $request
     * @return array The curl response.
     */
    public function send( $request ) {
        $options = array( // curl options for the connection
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_RETURNTRANSFER => true, // Return response as string
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST => $request->getMethod(),
            CURLOPT_CAINFO => __DIR__ . '/certs/cacert.pem',
        );

        if ( $request->getMethod() == Request::METHOD_POST ) { // need to add on post fields
            $options[CURLOPT_POSTFIELDS] = $request->getUrlBody();
        }

        if ( $request->getMethod() == Request::METHOD_PUT ) { // needed for put
            $options[CURLOPT_PUT] = true;
        }

        if ( $request->getAccessToken() ) { // pass along access token
            $options[CURLOPT_HTTPHEADER] = array(
                'Authorization: Bearer ' . $request->getAccessToken()
            );
        }

        if ( $request->getHeaders() ) { // we have headers to send
            foreach ( $request->getHeaders() as $headerKey => $headerValue ) { // loop over headers
                // generate header
                $header = $headerKey . ': ' . $headerValue;

                if ( isset( $options[CURLOPT_HTTPHEADER] ) ) { // add on to existing headers
                    $options[CURLOPT_HTTPHEADER][] = $header; 
                } else { // setup new header array
                    $options[CURLOPT_HTTPHEADER] = array(
                        $header
                    );
                }
            }
        }

        if ( $request->getFile() ) { // we have file being uploaded
            // get file info
            $fileInfo = $request->getFile();

            // open the file
            $openFile = fopen( $fileInfo['path'], 'rb' );

            // add file data to curl options
            $options[CURLOPT_INFILE] = $openFile;
            $options[CURLOPT_INFILESIZE] = filesize( $fileInfo['path'] );
        }

        // initialize curl
        $this->curl = curl_init();

        // set the options
        curl_setopt_array( $this->curl, $options );

        // send the request
        $this->rawResponse = curl_exec( $this->curl );

        // close curl connection
        curl_close( $this->curl );

        if ( $request->getFile() ) { // close our file
            fclose( $openFile );
        }

        // return nice json decoded response
        return json_decode( $this->rawResponse, true );
    }
}

?>


================================================
File: src/TikTok/Request/Fields.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Request;

/**
 * Fields
 *
 * Functionality and defines for the TikTok API fields query parameter.
 *
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Fields {
	const AUTO_ADD_MUSIC = 'auto_add_music';
	const AVATAR_LARGE_URL = 'avatar_large_url';
	const AVATAR_URL = 'avatar_url';
	const AVATAR_URL_100 = 'avatar_url_100';
	const BIO_DESCRIPTION = 'bio_description';
	const BRAND_CONTENT_TOGGLE = 'brand_content_toggle';
	const BRAND_ORGANIC_TOGGLE = 'brand_organic_toggle';
	const CHUNK_SIZE = 'chunk_size';
	const COMMENT_COUNT = 'comment_count';
	const COVER_IMAGE_URL = 'cover_image_url';
	const CREATE_TIME = 'create_time';
	const DESCRIPTION = 'description';
	const DISABLE_COMMENT = 'disable_comment';
	const DISABLE_DUET = 'disable_duet';
	const DISABLE_STITCH = 'disable_stitch';
	const DISPLAY_NAME = 'display_name';
	const DURATION = 'duration';
	const EMBED_HTML = 'embed_html';
	const EMBED_LINK = 'embed_link';
	const FOLLOWER_COUNT = 'follower_count';
	const FOLLOWING_COUNT = 'following_count'; 
	const HEIGHT = 'height';
	const ID = 'id';
	const IS_AIGC = 'is_aigc';
	const IS_VERIFIED = 'is_verified';
	const LIKE_COUNT = 'like_count';
	const LIKES_COUNT = 'likes_count';
	const MEDIA_TYPE = 'media_type';
	const OPEN_ID = 'open_id';
	const PHOTO_COVER_INDEX = 'photo_cover_index';
	const PHOTO_IMAGES = 'photo_images';
	const POST_INFO = 'post_info';
	const POST_MODE = 'post_mode';
	const PRIVACY_LEVEL = 'privacy_level';
	const PROFILE_DEEP_LINK = 'profile_deep_link';
	const PUBLISH_ID = 'publish_id';
	const SHARE_COUNT = 'share_count';
	const SHARE_URL = 'share_url';
	const SOURCE = 'source';
	const SOURCE_INFO = 'source_info';
	const TITLE = 'title';
	const TOTAL_CHUNK_COUNT = 'total_chunk_count';
	const UNION_ID = 'union_id';
	const VIDEO_COUNT = 'video_count';
	const VIDEO_COVER_TIMESTAMP_MS = 'video_cover_timestamp_ms';
	const VIDEO_DESCRIPTION = 'video_description';
	const VIDEO_URL = 'video_url';
	const VIDEO_SIZE = 'video_size';
	const VIEW_COUNT = 'view_count';
	const WIDTH = 'width';
}

?>


================================================
File: src/TikTok/Request/Params.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Request;

/**
 * Params
 *
 * Functionality and defines for query parameters.
 *
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Params {
    /**
     * @const strings of the query parameters.
     */
    const CLIENT_KEY = 'client_key';
    const CLIENT_SECRET = 'client_secret';
    const CODE = 'code';
    const CODE_CHALLENGE = 'code_challenge';
    const GRANT_TYPE = 'grant_type';
    const FIELDS = 'fields'; 
    const FILTERS = 'filters';
    const REDIRECT_URI = 'redirect_uri';
    const RESPONSE_TYPE = 'response_type';
    const SCOPE = 'scope';
    const STATE = 'state';
    const REFRESH_TOKEN = 'refresh_token';
    const VIDEO_IDS = 'video_ids';

    /**
     * Get fields for a request.
     * 
     * @param array $fields list of fields for the request.
     * @return array fields array with comma separated string.
     */
    public static function getFieldsParam( $fields ) {
        return array( // return fields param
            self::FIELDS => self::commaStringToArray( $fields )
        );
    }

    /**
     * Turn array into a comma separated string.
     * 
     * @param array $array elements to be comma separated.
     * @return string comma separated list of fields.
     */
    public static function commaStringToArray( $array = array() ) {
        // imploded string on commas and return
        return implode( ',', $array );
    }
}

?>


================================================
File: src/TikTok/Request/Request.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Request;

/**
 * Request
 *
 * Responsible for setting up the request to the API.
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Request {
    /**
     * @const string production Graph API URL.
     */
    const BASE_GRAPH_URL = 'https://open.tiktokapis.com';

    /**
     * @const string production Graph API URL.
     */
    const BASE_AUTHORIZATION_URL = 'https://www.tiktok.com';

    /**
     * @const string METHOD_GET HTTP GET method.
     */
    const METHOD_GET = 'GET';

    /**
     * @const string METHOD_POST HTTP POST method.
     */
    const METHOD_POST = 'POST';

    /**
     * @const string METHOD_PUT HTTP PUT method.
     */
    const METHOD_PUT = 'PUT';

    /**
     * @const string METHOD_DELETE HTTP DELETE method.
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string $accessToken the access token to use for this request.
     */
    protected $accessToken;

    /**
     * @var string $method the HTTP method for this request.
     */
    protected $method;

    /**
     * @var string $endpoint the Graph endpoint for this request.
     */
    protected $endpoint;

    /**
     * @var array $params the parameters to send with this request.
     */
    protected $params = array();

    /**
     * @var array $headers the headers to send with this request.
     */
    protected $headers = array();

    /**
     * @var array $file the file to send with this request.
     */
    protected $file = array();

    /**
     * @var string $url enptoint url.
     */
    protected $url;

    /**
     * Contructor for instantiating a request.
     *
     * @param string $method the method type for the request.
     * @param string $endpoint the endpoint for the request.
     * @param array  $params the parameters to be sent along with the request.
     * @param string $graphVersion the graph version for the request.
     * @param string $accessToken the access token to go along with the request.
     * @return void
     */
    public function __construct( $method, $endpoint = '', $params = array(), $graphVersion = '', $accessToken = '' ) {
        // set HTTP method
        $this->method = strtoupper( $method );

        // set endptoint
        $this->endpoint = $endpoint;

        // set any params
        $this->params = $params;

        // set access token
        $this->accessToken = $accessToken;

        // set url
        $this->setUrl( $graphVersion );
    }

    /**
     * Set the full url for the request.
     *
     * @param string $graphVersion the graph version we are using.
     * @param string $customUrl custom url for the request.
     * @return void
     */
    public function setUrl( $graphVersion, $customUrl = '' ) {
        // generate the full url
        $this->url = $customUrl ? $customUrl : self::BASE_GRAPH_URL . '/' . $graphVersion . $this->endpoint;

        if ( $this->getMethod() !== Request::METHOD_POST && !$customUrl ) { // not post or custom request so we have work to do
            // get the params
            $params = $this->getParams();

            // build the query string and append to url
            $this->url .= '?' . http_build_query( $params );
        }
    }

    /**
     * Set request headers.
     *
     * @param array $headers headers for the request.
     * @return void
     */
    public function setHeaders( $headers ) {
        // set request headres
        $this->headers = $headers;
    }

    /**
     * Return the headers for this request.
     *
     * @return string
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Set request file.
     *
     * @param array $file file for the request.
     * @return void
     */
    public function setFile( $file ) {
        // set request headres
        $this->file = $file;
    }

    /**
     * Return the file for this request.
     *
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Returns the body of the request.
     *
     * @return string
     */
    public function getUrlBody() {
        // get params
        $params = $this->getPostParams();

        return http_build_query( $this->params );
    }  

    /**
     * Return the params for this request.
     *
     * @return array
     */
    public function getParams() {
        if ( $this->accessToken ) { // append access token to params
            $this->params['access_token'] = $this->accessToken;
        }

        // return params array
        return $this->params;
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * Only return params on POST requests.
     *
     * @return array
     */
    public function getPostParams() {
        if ( $this->getMethod() === 'POST' ) { // request is a post
            // return the array of params
            return $this->getParams();
        }

        // return emtpy array
        return array();
    }

    /**
     * Return the endpoint URL this request.
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Return the access token.
     *
     * @return string
     */
    public function getAccessToken() {
        return $this->accessToken;
    }
}

?>


================================================
File: src/TikTok/Request/certs/cacert.pem
================================================



================================================
File: src/TikTok/User/User.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\User;

// other classes we need to use
use TikTok\TikTok;

/**
 * User
 *
 * Get the Users info.
 *     - Endpoints: 
 *          - /user/info/ GET
 *              - Docs: https://developers.tiktok.com/doc/tiktok-api-v2-get-user-info/
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class User extends TikTok {
    /**
     * @const endpoint for the request.
     */
    const ENDPOINT = 'user';

    /**
     * Contructor for instantiating a new object.
     *
     * @param array $config for the class.
     * @return void
     */
    public function __construct( $config ) {
        // call parent for setup
        parent::__construct( $config );
    }

    /**
     * Get the users info.
     *
     * @param array $params params for the GET request.
     * @return response.
     */
    public function getSelf( $params = array() ) {
        $getParams = array( // parameters for our endpoint
            'endpoint' => '/' . self::ENDPOINT . '/info/',
            'params' => $params
        );

        // get request
        $response = $this->get( $getParams );

        // return response
        return $response;
    }
}

?>


================================================
File: src/TikTok/Video/Video.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Video;

// other classes we need to use
use TikTok\TikTok;
use TikTok\Request\Params;
use TikTok\Request\Fields;

/**
 * Video
 *
 * Get videos.
 *     - Endpoints: 
 *          - /video/list/ POST
 *              - Docs: https://developers.tiktok.com/doc/tiktok-api-v2-video-list/
 *          - /video/query/ POST
 *              - Docs: https://developers.tiktok.com/doc/tiktok-api-v2-video-query
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Video extends TikTok {
    /**
     * @const endpoint for the request.
     */
    const ENDPOINT = 'video';

    /**
     * @var array $fields a list of all the fields we are requesting to get back.
     */
    protected $fields = array(
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

    /**
     * Contructor for instantiating a new object.
     *
     * @param array $config for the class.
     * @return void
     */
    public function __construct( $config ) {
        // call parent for setup
        parent::__construct( $config );
    }

    /**
     * Get videos list.
     *
     * @param array $params params for the GET request.
     * @param array $fields fields to get back for the GET request.
     * @return response.
     */
    public function getList( $params = array(), $fields = array() ) {
        // endpoint
        $endpoint = '/' . self::ENDPOINT . '/list/';

        // add on required query params
        $endpoint .= '?' . Params::FIELDS . '=' . Params::commaStringToArray( $fields ? $fields : $this->fields );

        $postParams = array( // parameters for our endpoint
            'endpoint' => $endpoint,
            'params' => $params
        );

        // get request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }

    /**
     * Check videos by ids.
     *
     * @param array $videoIds video ids for the request.
     * @param array $fields fields to get back for the GET request.
     * @return response.
     */
    public function query( $videoIds, $fields = array() ) {
        // endpoint
        $endpoint = '/' . self::ENDPOINT . '/query/';

        // add on required query params
        $endpoint .= '?' . Params::FIELDS . '=' . Params::commaStringToArray( $fields ? $fields : $this->fields );

        $postParams = array( // parameters for our endpoint
            'endpoint' => $endpoint,
            'params' => array(
                Params::FILTERS => json_encode( // need json on the ids
                    array( // ids to look up
                        Params::VIDEO_IDS => $videoIds
                    ) 
                )
            )
        );

        // get request
        $response = $this->post( $postParams );

        // return response
        return $response;
    }
}

?>


================================================
File: src/TikTok/Webhooks/Webhooks.php
================================================
<?php

/**
 * Copyright 2024 Justin Stolpe.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace TikTok\Webhooks;

/**
 * Webhooks
 *
 * Perform webhooks duties.
 *     - Docs: https://developers.tiktok.com/doc/webhooks-overview?enter_method=left_navigation
 * 
 * @package     tiktok-api-php-sdk
 * @author      Justin Stolpe
 * @link        https://github.com/jstolpe/tiktok-api-php-sdk
 * @license     https://opensource.org/licenses/MIT
 * @version     1.0
 */
class Webhooks {
    /**
     * Get raw JSON payload from TikTok.
     *
     * @return string
     */
    public function getRawPayload() {
        // get json contents
       return file_get_contents( 'php://input' );
    }

    /**
     * Get JSON payload from TikTok in a nice php array.
     *
     * @return string
     */
    public function getJsonPayloadData() {
        // decode json to nice php array
        return json_decode( $this->getRawPayload(), true );
    }
}

?>

