<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Entity\Message;
use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;
use Nette\Utils\Strings;

class MessageClient extends AbstractClient
{

	protected const BASE_URL = 'https://app.gosms.cz/api/v1/messages';

	public function send(Message $message): object
	{
		$body = Json::encode($message->toArray());

		$response = $this->doRequest(
			new Request('POST', self::BASE_URL, ['Content-Type' => 'application/json'], $body)
		);

		$this->assertResponse($response, 201);

		$res = Json::decode($response->getBody()->getContents());

		$res->parsedId = $res->link;
		if (isset(Strings::match($res->link, '~messages/\d+~')[0])) {
			$res->parseId = str_replace('messages/', '', Strings::match($res->link, '~messages/\d+~')[0]);
		}

		return $res;
	}

	public function test(Message $message): object
	{
		$body = Json::encode($message->toArray());

		$response = $this->doRequest(
			new Request('POST', self::BASE_URL . '/test', ['Content-Type' => 'application/json'], $body)
		);

		$this->assertResponse($response);

		return Json::decode($response->getBody()->getContents());
	}

	public function detail(string $id): object
	{
		$url = sprintf('%s/%d', self::BASE_URL, $id);

		$response = $this->doRequest(
			new Request('GET', $url)
		);

		$this->assertResponse($response);

		return Json::decode($response->getBody()->getContents());
	}

	public function replies(string $id): object
	{
		$url = sprintf('%s/%d/replies', self::BASE_URL, $id);

		$response = $this->doRequest(
			new Request('GET', $url)
		);

		$this->assertResponse($response);

		return Json::decode($response->getBody()->getContents());
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
