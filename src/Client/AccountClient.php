<?php declare(strict_types = 1);

namespace Contributte\Gosms\Client;

use GuzzleHttp\Psr7\Request;
use Nette\Utils\Json;

class AccountClient extends AbstractClient
{

	public function detail(): object
	{
		$response = $this->doRequest(new Request('GET', self::BASE_URL . '/'));

		$this->assertResponse($response);

		return Json::decode($response->getBody()->getContents());
	}

}
