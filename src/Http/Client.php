<?php declare(strict_types = 1);

namespace Contributte\Gosms\Http;

use Contributte\Gosms\Exception\ClientException;
use GuzzleHttp\Psr7\Utils;
use JsonSerializable;
use Nette\Utils\Json;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

final class Client
{

	public function __construct(
		private ClientInterface $client,
		private RequestFactoryInterface $requestFactory,
	)
	{
	}

	/**
	 * @param array<string, scalar|null>|JsonSerializable|null $body
	 */
	public function json(
		string $uri,
		string $accessToken,
		string $method = 'GET',
		null|array|JsonSerializable $body = null,
		int $code = 200,
	): stdClass
	{
		$request = $this->createRequest($uri, $accessToken, $method, $body);
		$response = $this->client->sendRequest($request->withHeader('X-Powered-By', 'contributte/gosms'));

		return $this->decodeResponse($response, $code);
	}

	/**
	 * @param array<string, scalar|null>|JsonSerializable|null $body
	 */
	private function createRequest(
		string $uri,
		string $accessToken,
		string $method = 'GET',
		null|array|JsonSerializable $body = null,
	): RequestInterface
	{
		$request = $this->requestFactory->createRequest($method, $uri);

		if ($request->getMethod() === 'POST') {
			if ($body instanceof JsonSerializable) {
				$request = $request->withHeader('Content-Type', 'application/json')
					->withBody(Utils::streamFor(Json::encode($body)));
			} elseif (is_array($body)) {
				$request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded')
					->withBody(Utils::streamFor(http_build_query($body)));
			}
		}

		return $accessToken === '' ? $request : $request->withHeader('Authorization', 'Bearer ' . $accessToken);
	}

	private function decodeResponse(
		ResponseInterface $response,
		int $code = 200,
	): stdClass
	{
		if ($response->getStatusCode() !== $code) {
			throw new ClientException($response->getBody()->getContents(), $response->getStatusCode());
		}

		$data = Json::decode($response->getBody()->getContents());
		assert($data instanceof stdClass);

		return $data;
	}

}
