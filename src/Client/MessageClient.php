<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Entity\Message;
use GuzzleHttp\Psr7\Utils;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Psr\Http\Message\RequestInterface;
use stdClass;

class MessageClient extends AbstractClient
{

	protected const BASE_MESSAGE_URL = self::BASE_URL . '/messages';

	public function send(Message $message): stdClass
	{
		$response = $this->doRequest(
			'POST',
			self::BASE_MESSAGE_URL,
			fn (RequestInterface $request) => $request->withHeader('Content-Type', 'application/json')
				->withBody(Utils::streamFor(Json::encode($message))),
		);

		$res = $this->decodeResponse($response, 201);

		$res->parsedId = str_replace('messages/', '', Strings::match($res->link, '~messages/\d+~')[0] ?? '');

		return $res;
	}

	public function test(Message $message): stdClass
	{
		$response = $this->doRequest(
			'POST',
			self::BASE_MESSAGE_URL . '/test',
			fn (RequestInterface $request) => $request->withHeader('Content-Type', 'application/json')
				->withBody(Utils::streamFor(Json::encode($message))),
		);

		return $this->decodeResponse($response);
	}

	public function detail(string $id): stdClass
	{
		$url = sprintf('%s/%d', self::BASE_MESSAGE_URL, $id);

		$response = $this->doRequest('GET', $url);

		return $this->decodeResponse($response);
	}

	public function replies(string $id): stdClass
	{
		$url = sprintf('%s/%d/replies', self::BASE_MESSAGE_URL, $id);

		$response = $this->doRequest('GET', $url);

		return $this->decodeResponse($response);
	}

	public function delete(string $id): void
	{
		$url = sprintf('%s/%d', self::BASE_MESSAGE_URL, $id);

		$response = $this->doRequest('DELETE', $url);

		$this->assertResponse($response);
	}

}
