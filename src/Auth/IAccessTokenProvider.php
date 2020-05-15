<?php declare(strict_types = 1);

namespace Contributte\Gosms\Auth;

use Contributte\Gosms\Config;
use Contributte\Gosms\Entity\AccessToken;

interface IAccessTokenProvider
{

	public function getAccessToken(Config $config): AccessToken;

}
