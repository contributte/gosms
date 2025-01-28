<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenProviderCache;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Tester;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Tester\Assert;
use Tester\Environment;
use Tests\Fixtures\MockAccessTokenProvider;

require_once __DIR__ . '/../../bootstrap.php';

Environment::bypassFinals();

test('Test Cache access token provider', function (): void {
	$storage = new FileStorage(Tester\Environment::getTmpDir());
	$storage->clean([Cache::All => true]);
	$cache = new PsrCacheAdapter($storage);
	$client = new AccessTokenProviderCache(new MockAccessTokenProvider(), $cache);
	$config = new Config('foo', 'bar');
	$responseToken = $client->getAccessToken($config);

	$token = $cache->get('Contributte/Gosms/foo');
	assert($token instanceof AccessToken);
	Assert::same($token->getAccessToken(), 'A');
	Assert::same($responseToken->getAccessToken(), 'A');

	sleep(2);
	$responseToken2 = $client->getAccessToken($config);
	Assert::same($responseToken2->getAccessToken(), 'B');
});
