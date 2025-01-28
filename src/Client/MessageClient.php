<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\Message;
use Contributte\Gosms\Http\Client;
use Nette\Utils\Strings;
use stdClass;

final class MessageClient
{

	private const BASE_MESSAGE_URL = Config::BASE_URL . '/messages';

	public function __construct(
		private IAccessTokenProvider $accessTokenProvider,
		private Client $client,
		private Config $config,
	)
	{
	}

	public function send(Message $message): stdClass
	{
		$response = $this->client->json(self::BASE_MESSAGE_URL, $this->getAccessToken(), 'POST', $message, 201);
		$response->parsedId = str_replace('messages/', '', Strings::match($response->link, '~messages/\d+~')[0] ?? '');

		return $response;
	}

	public function test(Message $message): stdClass
	{
		return $this->client->json(self::BASE_MESSAGE_URL . '/test', $this->getAccessToken(), 'POST', $message);
	}

	public function detail(string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d', self::BASE_MESSAGE_URL, $id), $this->getAccessToken());
	}

	public function replies(string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d/replies', self::BASE_MESSAGE_URL, $id), $this->getAccessToken());
	}

	public function delete(string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d', self::BASE_MESSAGE_URL, $id), $this->getAccessToken(), 'DELETE');
	}

	private function getAccessToken(): string
	{
		return $this->accessTokenProvider->getAccessToken($this->config)->getAccessToken();
	}

}
