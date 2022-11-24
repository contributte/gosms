<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Exception\ClientException;
use Contributte\Gosms\Http\IHttpClient;
use Nette\Utils\Json;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

abstract class AbstractClient
{

	protected const BASE_URL = 'https://app.gosms.cz/api/v1';

	/** @var Config */
	private $config;

	/** @var IHttpClient */
	private $client;

	/** @var IAccessTokenProvider */
	private $accessTokenProvider;

	public function __construct(Config $config, IHttpClient $client, IAccessTokenProvider $accessTokenProvider)
	{
		$this->client = $client;
		$this->config = $config;
		$this->accessTokenProvider = $accessTokenProvider;
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

	protected function decodeResponse(ResponseInterface $response): stdClass
	{
		$this->assertResponse($response);

		$data = Json::decode($response->getBody()->getContents());
		assert($data instanceof stdClass);

		return $data;
	}

}
