<?php declare(strict_types = 1);

namespace Contributte\Gosms;

class Config
{

	public const URL = 'https://app.gosms.cz';

	private string $clientId;

	private string $clientSecret;

	public function __construct(string $clientId, string $clientSecret)
	{
		$this->clientId = $clientId;
		$this->clientSecret = $clientSecret;
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
