This method allows you to post directly or upload videos to TikTok by sending files to TikTok and the files do not need to be publicly accessible like they do when using the "from url" endpoint. We need an access token and the scope video.publish.

## Step 1: Obtain the upload_url

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=NbVNxdsj9AU1CvQ4&t=430)

The first step is to pass along all the post info with the source set to "FILE_UPLOAD". TikTok will return an "upload_url" which is the full endpoint we need to use in the next step for sending our file to the TikTok servers.
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
        Fields::TITLE => '<POST_TITLE>',
        Fields::VIDEO_COVER_TIMESTAMP_MS => 1000 // spot in video to use as cover photo
    ) ),
    Fields::SOURCE_INFO => json_encode( array(
        Fields::SOURCE => 'FILE_UPLOAD',
        Fields::VIDEO_SIZE => filesize( '<PATH_TO_FILE>' ), // size of file in bytes
        Fields::CHUNK_SIZE => filesize( '<PATH_TO_FILE>' ), // size of chunk
        Fields::TOTAL_CHUNK_COUNT => <TOTAL_CHUNK_COUNT> // total chunks to be sent
    ) )
);

// post video to tiktok
$publish = $post->publish( $params );
```
## Step 2: Send the file to the "upload_url" obtained in step 1

[YouTube Tutorial](https://youtu.be/M2b1rRlNDVQ?si=zypCNaQUTRl7K2tT&t=562)

The second step is where the file actually gets uploaded to TikTok.
```php
use TikTok\Post\Post;
use TikTok\Request\Fields;

$config = array( // instantiation config params
    'access_token' => '<USER_ACCESS_TOKEN>',
);

// instantiate a new post
$post = new Post( $config );

// url from step 1 response
$uploadUrl = $publish['upload_url'];

$fileParams = array( // file params
    'path' => '<PATH_TO_FILE>', // path to the file
    'mime_type' => '<MIME_TYPE>' // mime type of file i. e. "video/mp4"
);

// make the api call
$publish = $post->uploadFile( $uploadUrl, $fileParams );
```