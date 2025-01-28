<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Api\GoSmsApi;
use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\Message;
use stdClass;

final class MessageClient
{

	public function __construct(
		private GoSmsApi $smsGoApi,
		private Config $config,
	)
	{
	}

	public function send(Message $message): stdClass
	{
		return $this->smsGoApi->messageSend($this->config, $message);
	}

	public function test(Message $message): stdClass
	{
		return $this->smsGoApi->messageTest($this->config, $message);
	}

	public function detail(string $id): stdClass
	{
		return $this->smsGoApi->messageDetail($this->config, $id);
	}

	public function replies(string $id): stdClass
	{
		return $this->smsGoApi->messageReplies($this->config, $id);
	}

	public function delete(string $id): stdClass
	{
		return $this->smsGoApi->messageDelete($this->config, $id);
	}

}
