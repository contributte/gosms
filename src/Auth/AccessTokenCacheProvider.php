<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Exception\RuntimeException;
use Contributte\Gosms\Http\IHttpClient;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

class AccessTokenCacheProvider extends AccessTokenClient
{

	private const CACHE_NAMESPACE = 'Contributte/Gosms';

	/** @var Cache */
	protected $cache;

	public function __construct(IHttpClient $client, Storage $storage)
	{
		parent::__construct($client);
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
	}

	protected function generateAccessToken(Config $config): AccessToken
	{
		$accessToken = $this->loadAccessToken($config);

		// MemoryStorage does not accept $dependencies in cache
		if ($accessToken->isExpired()) {
			$this->cache->remove($config->getClientId());
			$accessToken = $this->loadAccessToken($config);
			if ($accessToken->isExpired()) {
				throw new RuntimeException('Could not load access token.');
			}
		}

		return $accessToken;
	}


	private function loadAccessToken(Config $config): AccessToken
	{
		$accessToken = $this->cache->load($config->getClientId(), function (&$dependecies) use ($config): AccessToken {
			$token = parent::generateAccessToken($config);
			$dependecies[Cache::EXPIRE] = $token->getExpiresAt()->getTimestamp();

			return $token;
		});
		assert($accessToken instanceof AccessToken);

		return $accessToken;
	}

}
