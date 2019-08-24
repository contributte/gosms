# GoSMS.cz Api Integration

## Content

- [Requirements - what do you need](#requirements)
- [Installation - how to register an extension](#installation)
- [Usage - how to use it](#usage)


## Requirements

Create account on GoSMS.cz and copy clientId and clientSecret from administration.

If you use default HTTP client, you need to install and register [guzzlette](https://github.com/contributte/guzzlette/) extension.

GoSMS.cz access tokens are valid for 3600 seconds. Default AccessTokenCacheProvider stores them in cache using [nette/caching](https://github.com/nette/caching); 

* **clientId**
* **clientSecret**
* **httpClient**
* **accessTokenProvider**


## Installation

```yaml
extensions:
    guzzlette: Contributte\Guzzlette\DI\GuzzleExtension # optional for default HTTP client
    gosms: App\Model\GoSMS\DI\GoSmsExtension
    
gosms:
    # Required
    clientId: 10185_2jz2pog5jtgkocs0oc0008kow8kkwsccsk8c8ogogggs44cskg
    clientSecret: caajrzi80zs4cwgg8400swwo8wgc4kook0s8s48kw8s00sgws
    
    # Optional
    httpClient:
    accessTokenProvider:
```


## Usage

We prepared 2 clients: `AccountClient` and `MessageClient`. They mirror methods from [GoSMS.cz Api documentation](https://doc.gosms.cz/) so read documentation first. All methods except `send` return raw data as received from GoSMS.cz api.

All methods throw ClientException with error message and code as response status when response status is not 200/201;

### AccountClient

* `detail()` - [Organization detail](https://doc.gosms.cz/#detail-organizace)

### MessageClient

* `send(Contributte\Gosms\Entity\Message)` - [Sends message](https://doc.gosms.cz/#jak-poslat-zpravu)
  * Unfortunately GoSMS.cz does not include newly created message ID. We parse their response for you and include it in result object as `parsedId`. This id is needed by other methods.
* `test(Contributte\Gosms\Entity\Message)` - [Test creating message withou sending](https://doc.gosms.cz/#testovaci-vytvoreni-zpravy-bez-odeslani)
* `detail(string $id)` - [Sent message detail](https://doc.gosms.cz/#detail-zpravy)
* `replies(string $id)` - [List sent message replies](https://doc.gosms.cz/#seznam-odpovedi-u-zpravy)
* `delete(string $id)` - [Delete sent message](https://doc.gosms.cz/#smazani-zpravy)


```php
<?php

namespace App;

use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Entity\Message;
use Contributte\Gosms\Exception\ClientException;

final class SendPaymentsControl extends BaseControl
{

    /** @var MessageClient */
    private $messageClient;

    public function __construct(MessageClient $messageClient)
    {
        $this->messageClient = $messageClient;
    }

    public function handleSend(): void
    {
    	$result = NULL;
        $msg = new Message('Message body', ['+420711555444'], 1);
        
        try {
            $result = $this->messageClient->send($msg);
        } catch (ClientException $e) {
        	// Response status
        	$e->getCode();
        	// Response body
        	$e->getMessage();
        	exit;
        }

        // Process successful result as you like
        $this->saveSentMessage($result->parsedId, $msg);
    }

}

```


### AccessTokenProvider

We have two build in AccessToken providers;

* `AccessTokenClient` - fetches and stores accessToken for 1 request
* `AccessTokenCacheProvider` - fetches and stores accessToken in cache until access token expires
