<?php declare(strict_types = 1);

namespace Contributte\Gosms;

class Config
{

	public const URL = 'https://app.gosms.cz';

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
