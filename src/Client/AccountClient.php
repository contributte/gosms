<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use GuzzleHttp\Psr7\Request;
use stdClass;

class AccountClient extends AbstractClient
{

	public function detail(): stdClass
	{
		$response = $this->doRequest(new Request('GET', self::BASE_URL . '/'));

		return $this->decodeResponse($response);
	}

}
