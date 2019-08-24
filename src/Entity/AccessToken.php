<?php declare(strict_types = 1);

namespace Contributte\Gosms\Entity;

use DateTimeImmutable;

final class AccessToken
{

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
		return $this->expiresAt->modify('-5 minutes')->getTimestamp() < time();
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
	 * @return mixed[]
	 */
	public function toArray(): array
	{
		return [
			'accessToken' => $this->accessToken,
			'expiresIn' => $this->expiresIn,
			'tokenType' => $this->tokenType,
			'scope' => $this->scope,
			'expiresAt' => $this->expiresAt->format(DateTimeImmutable::ATOM),
		];
	}

}
