<?php declare(strict_types = 1);

namespace Tests\Fixtures;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;

final class MockAccessTokenProvider implements IAccessTokenProvider
{

	private string $token = 'A';

	public function getAccessToken(Config $config): AccessToken
	{
		$token = $this->token;
		$this->token = chr(ord($this->token) + 1);

		return new AccessToken($token, 31, 'type', 'scope');
	}

}
