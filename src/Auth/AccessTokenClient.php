<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;
use Contributte\Gosms\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;
use Psr\Http\Client\ClientInterface;

class AccessTokenClient implements IAccessTokenProvider
{

	protected const URL = Config::URL . '/oauth/v2/token';

	private ?AccessToken $accessToken = null;

	private ClientInterface $client;

	public function __construct(ClientInterface $client)
	{
		$this->client = $client;
	}

	final public function getAccessToken(Config $config): AccessToken
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
			new Request('POST', self::URL, ['Content-Type' => 'application/x-www-form-urlencoded'], $body),
		);

		if ($response->getStatusCode() !== 200) {
			throw new ClientException($response->getBody()->getContents(), $response->getStatusCode());
		}

		// @phpstan-ignore-next-line
		$data = Json::decode($response->getBody()->getContents(), Json::FORCE_ARRAY);
		assert(is_array($data) && isset($data['access_token'], $data['expires_in'], $data['token_type'], $data['scope']));

		return AccessToken::fromArray($data);
	}

}
