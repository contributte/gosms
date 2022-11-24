<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Http\IHttpClient;
use DateTimeImmutable;
use Nette\Caching\Cache;
use Nette\Caching\Storage;

class AccessTokenCacheProvider extends AccessTokenClient
{

	private const CACHE_NAMESPACE = 'Contributte/Gosms';

	/** @var AccessToken|null */
	protected $accessToken;

	/** @var Cache */
	protected $cache;

	public function __construct(IHttpClient $client, Storage $storage)
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
		assert(is_array($token) && isset($token['access_token'], $token['expires_in'], $token['token_type'], $token['scope']));

		$expiresAt = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $token['expires_at']);
		$token['expires_at'] = $expiresAt === false ? null : $expiresAt;

		 return AccessToken::fromArray($token);
	}

	private function saveAccessToken(Config $config, AccessToken $token): void
	{
		$this->cache->save($config->getClientId(), $token->toArray(), [
			Cache::EXPIRE => $token->getExpiresAt()->getTimestamp(),
		]);
	}

}
