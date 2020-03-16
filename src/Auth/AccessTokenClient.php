<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Exception\ClientException;
use Contributte\Gosms\Http\IHttpClient;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;

class AccessTokenClient implements IAccessTokenProvider
{

	protected const URL = 'https://app.gosms.cz/oauth/v2/token';

	/** @var AccessToken|null */
	protected $accessToken;

	/** @var IHttpClient */
	private $client;

	public function __construct(IHttpClient $client)
	{
		$this->client = $client;
	}

	public function getAccessToken(Config $config): ?AccessToken
	{
		// Store AccessToken at least for one request
		if ($this->accessToken === null || $this->accessToken->isExpired()) {
			$this->accessToken = $this->generateAccessToken($config);
		}

		return $this->accessToken;
	}

	protected function generateAccessToken(Config $config): AccessToken
	{
		$body = sprintf('client_id=%s&client_secret=%s&grant_type=client_credentials', $config->getClientId(), $config->getClientSecret());

		$response = $this->client->sendRequest(
			new Request('POST', self::URL, ['Content-Type' => 'application/x-www-form-urlencoded'], $body)
		);

		if ($response->getStatusCode() !== 200) {
			throw new ClientException($response->getBody()->getContents(), $response->getStatusCode());
		}

		$data = Json::decode($response->getBody()->getContents());

		return new AccessToken(
			$data->access_token,
			$data->expires_in,
			$data->token_type,
			$data->scope
		);
	}

}
