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
		$token = $this->accessToken;

		// If we have it in cache we retrieve it
		if ($this->accessToken === null) {
			$token = $this->accessToken = $this->loadAccessToken($config);
		}

		$this->accessToken = parent::getAccessToken($config);

		if ($token === null || $token->getAccessToken() !== $this->accessToken->getAccessToken()) {
			$this->saveAccessToken($config, $this->accessToken);
		}

		return $this->accessToken;
	}

	private function loadAccessToken(Config $config): ?AccessToken
	{
		$token = $this->cache->load($config->getClientId());
		if ($token === null) return null;

		/** @var DateTimeImmutable $expiresAt */
		$expiresAt = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $token['expiresAt']);

		return new AccessToken(
			$token['accessToken'],
			$token['expiresIn'],
			$token['tokenType'],
			$token['scope'],
			$expiresAt
		);
	}

	private function saveAccessToken(Config $config, AccessToken $token): void
	{
		$this->cache->save($config->getClientId(), $token->toArray(), [
			Cache::EXPIRE => $token->getExpiresAt()->getTimestamp(),
		]);
	}

}
