# Contributte GoSMS

Integration of [GoSMS](https://gosms.eu) for Nette Framework.

## Content

- [Installation](#installation)
- [Configuration](#configuration)
  - [HTTP client](#http-client)
  - [Access tokens](#access-tokens)
- [Usage](#usage)

## Installation

Install package using composer.

```bash
composer require contributte/gosms
```

Register prepared [compiler extension](https://doc.nette.org/en/dependency-injection/nette-container) in your `config.neon` file.

```neon
extensions:
    gosms: Contributte\Gosms\DI\GoSmsExtension
```

## Configuration

```neon
gosms:
    clientId: fake_10185_2jz2pog5jtgkocs0oc0008kow8kkwsccsk8c8ogogggs44cskg
    clientSecret: fake_caajrzi80zs4cwgg8400swwo8wgc4kook0s8s48kw8s00sgws
```

### HTTP client

This library uses PSR-18 client for HTTP requests. You can define your own client and request factory in configuration.

> [!TIP]
> You can use [`contributte/guzzlette`](https://github.com/contributte/guzzlette) extension for easy guzzle client setup.

Other PSR-18 libraries you can find on [Packagist](https://packagist.org/providers/psr/http-client-implementation). For example:

- [guzzlehttp/guzzle](https://packagist.org/packages/guzzlehttp/guzzle)
- [symfony/http-client](https://packagist.org/packages/symfony/http-client)

After installation of your preferred PSR-18 client, you can define it in configuration.

```neon
services:
    my.http.client: GuzzleHttp\Client([timeout: 30, http_errors: false]) # define Psr\Http\Client\ClientInterface
    my.http.request.factory: GuzzleHttp\Psr7\HttpFactory # define Psr\Http\Message\RequestFactoryInterface
    my.psr16.cache: Nette\Bridges\Psr\PsrCacheAdapter # define Psr\SimpleCache\CacheInterface
```

### Access tokens

GoSMS access tokens are valid for 3600 seconds. Default `AccessTokenCacheProvider` stores them in cache using [nette/caching](https://github.com/nette/caching).

```neon
services:
    gosms.accessTokenProvider: App\MyCustomAccessTokenProvider
```

## Usage

We prepared 2 clients: `AccountClient` and `MessageClient`.

They mirror methods from [gosms.eu Api documentation](https://doc.gosms.eu/) so read documentation first. All methods except `send` return raw data as received from gosms.eu api.

All methods throw ClientException with error message and code as response status when response status is not 200/201;

### AccountClient

Get information about your account.

- `detail()` - [Organization detail](https://doc.gosms.eu/#detail-organizace)

### MessageClient

Send and manage messages.

- `send(Contributte\Gosms\Entity\Message)` - [Sends message](https://doc.gosms.eu/#jak-poslat-zpravu)
  - Unfortunately gosms.eu does not include newly created message ID. We parse their response for you and include it in result object as `parsedId`. This id is needed by other methods.
- `test(Contributte\Gosms\Entity\Message)` - [Test creating message withou sending](https://doc.gosms.eu/#testovaci-vytvoreni-zpravy-bez-odeslani)
- `detail(string $id)` - [Sent message detail](https://doc.gosms.eu/#detail-zpravy)
- `replies(string $id)` - [List sent message replies](https://doc.gosms.eu/#seznam-odpovedi-u-zpravy)
- `delete(string $id)` - [Delete sent message](https://doc.gosms.eu/#smazani-zpravy)

**Example**

```php
<?php declare(strict_types = 1);

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
