<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use stdClass;

class AccountClient extends AbstractClient
{

	public function detail(): stdClass
	{
		$response = $this->doRequest('GET', self::BASE_URL . '/');

		return $this->decodeResponse($response);
	}

}
