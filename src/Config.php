<?php declare(strict_types = 1);

namespace Contributte\Gosms;

class Config
{

	/** @var string */
	private $clientId;

	/** @var string */
	private $clientSecret;

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
