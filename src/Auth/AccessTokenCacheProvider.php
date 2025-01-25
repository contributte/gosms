<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;

class AccessTokenCacheProvider extends AccessTokenClient
{

	public function __construct(ClientInterface $client, protected CacheInterface $cache)
	{
		parent::__construct($client);
	}

	protected function generateAccessToken(Config $config): AccessToken
	{
		$key = 'Contributte/Gosms/' . $config->getClientId();

		$accessToken = $this->cache->get($key);
		assert($accessToken instanceof AccessToken || $accessToken === null);

		if ($accessToken === null) {
			$accessToken = parent::generateAccessToken($config);
			$ttl = $accessToken->getExpiresAt() - AccessToken::PRE_FETCH_SECONDS - time();

			$this->cache->set($key, $accessToken, $ttl);
		}

		return $accessToken;
	}

}
