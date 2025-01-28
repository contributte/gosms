<?php declare(strict_types = 1);

namespace Contributte\Gosms;

final class Config
{

	public const URL = 'https://app.gosms.cz';
	public const BASE_URL = self::URL . '/api/v1';

	public function __construct(private string $clientId, private string $clientSecret)
	{
	}

	public function getClientId(): string
	{
		return $this->clientId;
	}

	public function getClientSecret(): string
	{
		return $this->clientSecret;
	}

}
