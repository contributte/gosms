<?php declare(strict_types = 1);

namespace Contributte\Gosms\Http;

use Contributte\Guzzlette\ClientFactory;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class GuzzletteClient implements IHttpClient
{

	/** @var Client */
	private $client;

	public function __construct(ClientFactory $clientFactory)
	{
		$this->client = $clientFactory->createClient(['timeout' => 30, 'http_errors' => false]);
	}

	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		return $this->client->send($request);
	}

}
