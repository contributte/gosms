<?php declare(strict_types = 1);

namespace Contributte\Gosms\Entity;

/**
 * @phpstan-type SourceType array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at?: ?int}
 */
final class AccessToken
{

	public const PRE_FETCH_SECONDS = 30;

	private string $accessToken;

	private int $expiresIn;

	private int $expiresAt;

	private string $tokenType;

	private string $scope;

	public function __construct(string $accessToken, int $expiresIn, string $tokenType, string $scope, ?int $expiresAt = null)
	{
		$this->accessToken = $accessToken;
		$this->expiresIn = $expiresIn;
		$this->tokenType = $tokenType;
		$this->scope = $scope;
		$this->expiresAt = $expiresAt ?? time() + $expiresIn;
	}

	/**
	 * @param SourceType $data
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

	public function isExpired(): bool
	{
		return $this->expiresAt - self::PRE_FETCH_SECONDS < time();
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

	public function getExpiresAt(): int
	{
		return $this->expiresAt;
	}

	/**
	 * @return array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at: int}
	 */
	public function __serialize(): array
	{
		return [
			'access_token' => $this->accessToken,
			'expires_in' => $this->expiresIn,
			'token_type' => $this->tokenType,
			'scope' => $this->scope,
			'expires_at' => $this->expiresAt,
		];
	}

	/**
	 * @param array{access_token: string, expires_in: int, token_type: string, scope: string, expires_at: int} $data
	 */
	public function __unserialize(array $data): void
	{
		$this->accessToken = $data['access_token'];
		$this->expiresIn = $data['expires_in'];
		$this->tokenType = $data['token_type'];
		$this->scope = $data['scope'];
		$this->expiresAt = $data['expires_at'];
	}

}
