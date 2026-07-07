![](https://heatbadger.now.sh/github/readme/contributte/gosms/)

<p align=center>
  <a href="https://github.com/contributte/gosms/actions"><img src="https://badgen.net/github/checks/contributte/gosms/master"></a>
  <a href="https://coveralls.io/r/contributte/gosms"><img src="https://badgen.net/coveralls/c/github/contributte/gosms"></a>
  <a href="https://packagist.org/packages/contributte/gosms"><img src="https://badgen.net/packagist/dm/contributte/gosms"></a>
  <a href="https://packagist.org/packages/contributte/gosms"><img src="https://badgen.net/packagist/v/contributte/gosms"></a>
</p>
<p align=center>
  <a href="https://packagist.org/packages/contributte/gosms"><img src="https://badgen.net/packagist/php/contributte/gosms"></a>
  <a href="https://github.com/contributte/gosms"><img src="https://badgen.net/github/license/contributte/gosms"></a>
  <a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
  <a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
  <a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Integration of [GoSMS](https://gosms.eu) SMS gateway for Nette Framework.

## Versions

| State       | Version | Branch   | PHP      |
|-------------|---------|----------|----------|
| dev         | `^0.6`  | `master` | `>= 8.2` |
| stable      | `^0.5`  | `master` | `>= 8.2` |

## Installation

To install the latest version of `contributte/gosms` use [Composer](https://getcomposer.org).

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
> You can use [`contributte/guzzlette`](https://github.com/contributte/guzzlette) extension for easy Guzzle client setup.

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

They mirror methods from [gosms.eu API documentation](https://doc.gosms.eu/), so read documentation first. All methods except `send` return raw data as received from gosms.eu API.

All methods throw `ClientException` with error message and code as response status when response status is not `200`/`201`.

### AccountClient

Get information about your account.

- `detail()` - [Organization detail](https://doc.gosms.eu/#detail-organizace)

### MessageClient

Send and manage messages.

- `send(Contributte\Gosms\Entity\Message)` - [Sends message](https://doc.gosms.eu/#jak-poslat-zpravu)
  - Unfortunately gosms.eu does not include newly created message ID. We parse their response for you and include it in result object as `parsedId`. This id is needed by other methods.
- `test(Contributte\Gosms\Entity\Message)` - [Test creating message without sending](https://doc.gosms.eu/#testovaci-vytvoreni-zpravy-bez-odeslani)
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

final class SendSmsControl extends BaseControl
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

We have two built-in access token providers:

- `AccessTokenClient` - fetches and stores access token for one request
- `AccessTokenCacheProvider` - fetches and stores access token in cache until access token expires

## Development

See [how to contribute](https://contributte.org/contributing.html) to this package.

This package is currently maintained by these authors.

<a href="https://github.com/f3l1x">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/538058?v=3&s=80">
</a>

<a href="https://github.com/Vody105">
  <img width="80" height="80" src="https://avatars2.githubusercontent.com/u/22433893?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
