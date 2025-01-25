<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Nette\Caching\Cache;
use Psr\Http\Client\ClientInterface;

class AccessTokenCacheProvider extends AccessTokenClient
{

	protected Cache $cache;

	public function __construct(ClientInterface $client, Cache $cache)
	{
		parent::__construct($client);

		$this->cache = $cache;
	}

	protected function generateAccessToken(Config $config): AccessToken
	{
		// phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
		$accessToken = $this->cache->load($config->getClientId(), function (&$dependecies) use ($config): AccessToken {
			$token = parent::generateAccessToken($config);
			$dependecies[Cache::Expire] = $token->getExpiresAt() - AccessToken::PRE_FETCH_SECONDS;

			return $token;
		});
		assert($accessToken instanceof AccessToken);

		return $accessToken;
	}

}
