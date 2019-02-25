<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Http\IHttpClient;
use DateTimeImmutable;
use Nette\Http\Session;

class AccessTokenSessionProvider extends AccessTokenClient
{

	private const SESSION_NAME = 'Contributte/Gosms';

	/** @var AccessToken|null */
	protected $accessToken;

	/** @var Session */
	private $session;

	public function __construct(IHttpClient $client, Session $session)
	{
		parent::__construct($client);
		$this->session = $session;
	}

	public function getAccessToken(Config $config): AccessToken
	{
		$token = $this->accessToken;

		// If we have it in session we retrieve it
		if ($this->accessToken === null) {
			$token = $this->accessToken = $this->getSessionAccessToken();
		}

		$this->accessToken = parent::getAccessToken($config);

		if ($token === null || $token->getAccessToken() !== $this->accessToken->getAccessToken()) {
			$this->setSessionAccessToken($this->accessToken);
		}

		return $this->accessToken;
	}

	private function getSessionAccessToken(): ?AccessToken
	{
		if (!$this->session->exists()) return null;

		$section = $this->session->getSection(self::SESSION_NAME);
		if (!isset($section['accessToken'])) return null;

		/** @var DateTimeImmutable $expiresAt */
		$expiresAt = DateTimeImmutable::createFromFormat(DateTimeImmutable::ATOM, $section['expiresAt']);

		return new AccessToken(
			$section['accessToken'],
			$section['expiresIn'],
			$section['tokenType'],
			$section['scope'],
			$expiresAt
		);
	}

	private function setSessionAccessToken(AccessToken $token): void
	{
		$section = $this->session->getSection(self::SESSION_NAME);
		$section->setExpiration($token->getExpiresAt());

		foreach ($token->toArray() as $k => $v) {
			$section[$k] = $v instanceof DateTimeImmutable ? $v->format(DateTimeImmutable::ATOM) : $v;
		}
	}

}
