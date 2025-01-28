<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use Contributte\Gosms\Api\GoSmsApi;
use Contributte\Gosms\Config;
use stdClass;

final class AccountClient
{

	public function __construct(
		private GoSmsApi $smsGoApi,
		private Config $config,
	)
	{
	}

	public function detail(): stdClass
	{
		return $this->smsGoApi->accountDetail($this->config);
	}

}
