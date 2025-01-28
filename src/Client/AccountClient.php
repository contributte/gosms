<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\Client;
use stdClass;

final class AccountClient
{

	public function __construct(
		private IAccessTokenProvider $accessTokenProvider,
		private Client $client,
		private Config $config,
	)
	{
	}

	public function detail(): stdClass
	{
		$token = $this->accessTokenProvider->getAccessToken($this->config);

		return $this->client->json(Config::BASE_URL . '/', $token->getAccessToken());
	}

}
