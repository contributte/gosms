<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Psr\SimpleCache\CacheInterface;

final class AccessTokenProviderCache implements IAccessTokenProvider
{

	public function __construct(private IAccessTokenProvider $accessTokenClient, private CacheInterface $cache)
	{
	}

	public function getAccessToken(Config $config): AccessToken
	{
		$key = 'Contributte/Gosms/' . $config->getClientId();

		$accessToken = $this->cache->get($key);
		assert($accessToken instanceof AccessToken || $accessToken === null);

		if ($accessToken === null) {
			$accessToken = $this->accessTokenClient->getAccessToken($config);
			$ttl = $accessToken->getExpiresAt() - AccessToken::PRE_FETCH_SECONDS - time();

			$this->cache->set($key, $accessToken, $ttl);
		}

		return $accessToken;
	}

}
