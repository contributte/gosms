<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Entity\Message;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use stdClass;

class MessageClient extends AbstractClient
{

	protected const BASE_URL = 'https://app.gosms.cz/api/v1/messages';

	public function send(Message $message): stdClass
	{
		$body = Json::encode($message->toArray());

		$response = $this->doRequest(
			new Request('POST', self::BASE_URL, ['Content-Type' => 'application/json'], $body)
		);

		$res = $this->decodeResponse($response);

		$res->parsedId = str_replace('messages/', '', Strings::match($res->link, '~messages/\d+~')[0] ?? '');

		return $res;
	}

	public function test(Message $message): stdClass
	{
		$body = Json::encode($message->toArray());

		$response = $this->doRequest(
			new Request('POST', self::BASE_URL . '/test', ['Content-Type' => 'application/json'], $body)
		);

		return $this->decodeResponse($response);
	}

	public function detail(string $id): stdClass
	{
		$url = sprintf('%s/%d', self::BASE_URL, $id);

		$response = $this->doRequest(
			new Request('GET', $url)
		);

		return $this->decodeResponse($response);
	}

	public function replies(string $id): stdClass
	{
		$url = sprintf('%s/%d/replies', self::BASE_URL, $id);

		$response = $this->doRequest(
			new Request('GET', $url)
		);

		return $this->decodeResponse($response);
	}

	public function delete(string $id): void
	{
		$url = sprintf('%s/%d', self::BASE_URL, $id);

		$response = $this->doRequest(
			new Request('DELETE', $url)
		);

		$this->assertResponse($response);
	}

}
