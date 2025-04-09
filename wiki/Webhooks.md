Webhook is a subscription that notifies your application via a callback URL when an event happens in TikTok. Rather than requiring you to pull information via API, you can use webhooks to get information on events that occur. Notifications are delivered via HTTPS POST in JSON format to the callback url configured for your app in the Developer Portal. This information can be used to update your system or to trigger business processes.

[YouTube Tutorial](https://www.youtube.com/watch?v=zoCeJmKFRDQ&list=PL0glhsZ01I-BWvKCRnZtkG4bBoOB3LivT&index=7)

## Getting Payload Data
TikTok will send payload data as json to the webhook endpoint you have specified in your TikTok application. Here is the code snippet for getting the payload data with the SDK.
```php
// use webhooks
use TikTok\Webhooks\Webhooks;

// webhook create
$webhooks = new Webhooks();

// get payload data in nice php array
$webhookJsonData = $webhooks->getJsonPayloadData();
```