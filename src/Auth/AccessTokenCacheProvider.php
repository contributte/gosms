<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Http\IHttpClient;
use DateTimeImmutable;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;

class AccessTokenCacheProvider extends AccessTokenClient
{

	private const CACHE_NAMESPACE = 'Contributte/Gosms';

	/** @var AccessToken|null */
	protected $accessToken;

	/** @var Cache */
	protected $cache;

	public function __construct(IHttpClient $client, IStorage $storage)
	{
		parent::__construct($client);
		$this->cache = new Cache($storage, self::CACHE_NAMESPACE);
	}

	public function getAccessToken(Config $config): AccessToken
	{
		// We have token
		if ($this->accessToken !== null && !$this->accessToken->isExpired()) {
			return $this->accessToken;
		}

		// Load token from cache
		$token = $this->loadAccessToken($config);
		if ($token !== null && !$token->isExpired()) {
			return $this->accessToken = $token;
		}

		// We need to request token
		$token = parent::getAccessToken($config);
		$this->saveAccessToken($config, $token);

		return $this->accessToken = $token;
	}

	private function loadAccessToken(Config $config): ?AccessToken
	{
		$token = $this->cache->load($config->getClientId());
		if ($token === null) {
			return null;
		}

		$token['expires_at'] = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $token['expires_at']);

		 return AccessToken::fromArray($token);
	}

	private function saveAccessToken(Config $config, AccessToken $token): void
	{
		$this->cache->save($config->getClientId(), $token->toArray(), [
			Cache::EXPIRE => $token->getExpiresAt()->getTimestamp(),
		]);
	}

}
