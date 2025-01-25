<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use GuzzleHttp\Psr7\Response;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\Caching\Storages\MemoryStorage;
use Psr\Http\Client\ClientInterface;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

Environment::bypassFinals();

// New token
test('AccessTokenCacheProvider', function (): void {
	$http = Mockery::mock(ClientInterface::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$client = new AccessTokenCacheProvider($http, new Cache(new MemoryStorage()));
	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('token', $token->getAccessToken());
	Assert::same(123, $token->getExpiresIn());
	Assert::same('type', $token->getTokenType());
	Assert::same('scope', $token->getScope());
});

// Cached token
test('AccessTokenCacheProvider with storage', function (): void {
	$http = Mockery::mock(ClientInterface::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"type","scope":"scope"}'));

	$storage = Mockery::mock(Storage::class);
	$storage->shouldReceive('read')
		->andReturn(AccessToken::fromArray([
			'access_token' => 'cached',
			'expires_in' => 999,
			'token_type' => 'cached',
			'scope' => 'cached',
			'expires_at' => (new DateTimeImmutable('+1 year'))->getTimestamp(),
		]));

	$client = new AccessTokenCacheProvider($http, new Cache($storage));
	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('cached', $token->getAccessToken());
	Assert::same(999, $token->getExpiresIn());
	Assert::same('cached', $token->getTokenType());
	Assert::same('cached', $token->getScope());
});

// Cached token is expired
test('AccessTokenCacheProvider, test expire', function (): void {
	$http = Mockery::mock(ClientInterface::class);
	$http->shouldReceive('sendRequest')
		->andReturn(
			new Response(200, [], '{"access_token":"token","expires_in":30,"token_type":"first","scope":"scope"}'),
			new Response(200, [], '{"access_token":"token","expires_in":123,"token_type":"second","scope":"scope"}'),
		);

	$storage = new MemoryStorage();
	$client = new AccessTokenCacheProvider($http, new Cache($storage));
	$config = new Config('foo', 'bar');

	$token = $client->getAccessToken($config);
	Assert::same('first', $token->getTokenType());
	Assert::same('token', $token->getAccessToken());
	Assert::same(30, $token->getExpiresIn());
	Assert::same('scope', $token->getScope());
	sleep(1); // expire token by method $token->isExpired()
	Assert::same('second', $client->getAccessToken($config)->getTokenType());
});
