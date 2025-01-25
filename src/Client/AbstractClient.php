<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Exception\ClientException;
use Nette\Utils\Json;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

abstract class AbstractClient
{

	protected const BASE_URL = Config::URL . '/api/v1';

	public function __construct(private Config $config, private ClientInterface $client, private IAccessTokenProvider $accessTokenProvider)
	{
	}

	protected function doRequest(RequestInterface $request): ResponseInterface
	{
		$token = $this->accessTokenProvider->getAccessToken($this->config);
		$request = $request->withHeader('Authorization', 'Bearer ' . $token->getAccessToken());

		return $this->client->sendRequest($request);
	}

	protected function assertResponse(ResponseInterface $response, int $code = 200): void
	{
		if ($response->getStatusCode() !== $code) {
			throw new ClientException($response->getBody()->getContents(), $response->getStatusCode());
		}
	}

	protected function decodeResponse(ResponseInterface $response, int $code = 200): stdClass
	{
		$this->assertResponse($response, $code);

		$data = Json::decode($response->getBody()->getContents());
		assert($data instanceof stdClass);

		return $data;
	}

}
