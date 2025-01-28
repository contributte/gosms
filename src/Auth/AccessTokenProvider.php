<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Http\Client;

final class AccessTokenProvider implements IAccessTokenProvider
{

	public function __construct(private Client $client)
	{
	}

	final public function getAccessToken(Config $config): AccessToken
	{
		$data = $this->client->json(Config::URL . '/oauth/v2/token', '', 'POST', [
			'client_id' => $config->getClientId(),
			'client_secret' => $config->getClientSecret(),
			'grant_type' => 'client_credentials',
		]);

		return new AccessToken(
			$data->access_token,
			$data->expires_in,
			$data->token_type,
			$data->scope,
			$data->expires_at ?? null
		);
	}

}
