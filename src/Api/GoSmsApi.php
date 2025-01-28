<?php declare(strict_types = 1);

namespace Contributte\Gosms\Api;

use Contributte\Gosms\Auth\IAccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\Message;
use Contributte\Gosms\Http\Client;
use Nette\Utils\Strings;
use stdClass;

final class GoSmsApi
{

	private const BASE_MESSAGE_URL = Config::BASE_URL . '/messages';

	public function __construct(
		private IAccessTokenProvider $accessTokenProvider,
		private Client $client,
	)
	{
	}

	public function messageSend(Config $config, Message $message): stdClass
	{
		$response = $this->client->json(self::BASE_MESSAGE_URL, $this->getAccessToken($config), 'POST', $message, 201);
		$response->parsedId = str_replace('messages/', '', Strings::match($response->link, '~messages/\d+~')[0] ?? '');

		return $response;
	}

	public function messageTest(Config $config, Message $message): stdClass
	{
		return $this->client->json(self::BASE_MESSAGE_URL . '/test', $this->getAccessToken($config), 'POST', $message);
	}

	public function messageDetail(Config $config, string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d', self::BASE_MESSAGE_URL, $id), $this->getAccessToken($config));
	}

	public function messageReplies(Config $config, string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d/replies', self::BASE_MESSAGE_URL, $id), $this->getAccessToken($config));
	}

	public function messageDelete(Config $config, string $id): stdClass
	{
		return $this->client->json(sprintf('%s/%d', self::BASE_MESSAGE_URL, $id), $this->getAccessToken($config), 'DELETE');
	}

	public function accountDetail(Config $config): stdClass
	{
		return $this->client->json(Config::BASE_URL . '/', $this->getAccessToken($config));
	}

	private function getAccessToken(Config $config): string
	{
		return $this->accessTokenProvider->getAccessToken($config)->getAccessToken();
	}

}
