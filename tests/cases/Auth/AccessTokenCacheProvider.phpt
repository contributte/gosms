<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Http\IHttpClient;
use GuzzleHttp\Psr7\Response;
use Nette\Caching\IStorage;
use Nette\Caching\Storages\MemoryStorage;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

Environment::bypassFinals();

// New token
test(function (): void {
	$http = Mockery::mock(IHttpClient::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$client = new AccessTokenCacheProvider($http, new MemoryStorage());
	Closure::fromCallable(function (): void {
		$this->accessToken = Mockery::mock(AccessToken::class);
		$this->accessToken->shouldReceive('isExpired')
			->andReturn(true);
	})->call($client);

	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('token', $token->getAccessToken());
	Assert::same(123, $token->getExpiresIn());
	Assert::same('type', $token->getTokenType());
	Assert::same('scope', $token->getScope());
});

// Cached token
test(function (): void {
	$http = Mockery::mock(IHttpClient::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$storage = Mockery::mock(IStorage::class);
	$storage->shouldReceive('read')
		->andReturn(['access_token' => 'cached', 'expires_in' => 999, 'token_type' => 'cached', 'scope' => 'cached', 'expires_at' => (new DateTimeImmutable('+1 year'))->format(DateTimeImmutable::ATOM)]);

	$client = new AccessTokenCacheProvider($http, $storage);
	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('cached', $token->getAccessToken());
	Assert::same(999, $token->getExpiresIn());
	Assert::same('cached', $token->getTokenType());
	Assert::same('cached', $token->getScope());
});

// Cached token is expired
test(function (): void {
	$http = Mockery::mock(IHttpClient::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$storage = Mockery::mock(IStorage::class);
	$storage->shouldReceive('read')
		->andReturn(['access_token' => 'cached', 'expires_in' => 999, 'token_type' => 'cached', 'scope' => 'cached', 'expires_at' => (new DateTimeImmutable('-5 minutes'))->format(DateTimeImmutable::ATOM)]);
	$storage->shouldReceive('write')
		->once();

	$client = new AccessTokenCacheProvider($http, $storage);
	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('token', $token->getAccessToken());
	Assert::same(123, $token->getExpiresIn());
	Assert::same('type', $token->getTokenType());
	Assert::same('scope', $token->getScope());
});

// Cached token is expired so we request new one and save new one to cache
test(function (): void {
	$http = Mockery::mock(IHttpClient::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$storage = Mockery::mock(IStorage::class);
	$storage->shouldReceive('read')
		->andReturn(['access_token' => 'cached', 'expires_in' => 999, 'token_type' => 'cached', 'scope' => 'cached', 'expires_at' => (new DateTimeImmutable('-5 minutes'))->format(DateTimeImmutable::ATOM)]);
	$storage->shouldReceive('write')
		->withArgs(function (string $key, array $data): bool {
			Assert::same('token', $data['access_token']);

			return true;
		})->once();

	$client = new AccessTokenCacheProvider($http, $storage);
	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('token', $token->getAccessToken());
	Assert::same(123, $token->getExpiresIn());
	Assert::same('type', $token->getTokenType());
	Assert::same('scope', $token->getScope());
});
