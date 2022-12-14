<?php declare(strict_types = 1);

namespace Contributte\Gosms\Entity;

use DateTimeImmutable;

final class AccessToken
{
	public const PRE_FETCH_SECONDS = 30;

	/** @var string */
	private $accessToken;

	/** @var int */
	private $expiresIn;

	/** @var DateTimeImmutable */
	private $expiresAt;

	/** @var string */
	private $tokenType;

	/** @var string */
	private $scope;

	public function __construct(string $accessToken, int $expiresIn, string $tokenType, string $scope, ?DateTimeImmutable $expiresAt = null)
	{
		$this->accessToken = $accessToken;
		$this->expiresIn = $expiresIn;
		$this->tokenType = $tokenType;
		$this->scope = $scope;
		$this->expiresAt = $expiresAt ?? new DateTimeImmutable(sprintf('+%d seconds', $expiresIn));
	}

	public function isExpired(): bool
	{
		return $this->expiresAt->modify(sprintf('-%s seconds', self::PRE_FETCH_SECONDS))->getTimestamp() < time();
	}

	public function getAccessToken(): string
	{
		return $this->accessToken;
	}

	public function getExpiresIn(): int
	{
		return $this->expiresIn;
	}

	public function getTokenType(): string
	{
		return $this->tokenType;
	}

	public function getScope(): string
	{
		return $this->scope;
	}

	public function getExpiresAt(): DateTimeImmutable
	{
		return $this->expiresAt;
	}

	/**
	 * @param array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at?: ?DateTimeImmutable} $data
	 */
	public static function fromArray(array $data): self
	{
		return new self(
			$data['access_token'],
			$data['expires_in'],
			$data['token_type'],
			$data['scope'],
			$data['expires_at'] ?? null
		);
	}

	/**
	 * @return array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at: string}
	 */
	public function __serialize(): array
	{
		return [
			'access_token' => $this->accessToken,
			'expires_in' => $this->expiresIn,
			'token_type' => $this->tokenType,
			'scope' => $this->scope,
			'expires_at' => $this->expiresAt->format(DateTimeImmutable::ATOM),
		];
	}

	/**
	 * @param array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at: string} $data
	 */
	public function __unserialize(array $data): void
	{
		$this->accessToken = $data['access_token'];
		$this->expiresIn = $data['expires_in'];
		$this->tokenType = $data['token_type'];
		$this->scope = $data['scope'];
		$this->expiresAt = new DateTimeImmutable($data['expires_at']);
	}

}
