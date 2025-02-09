<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenProvider;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\Client;
use Contributte\Tester\Toolkit;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Client\ClientInterface;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Check client creates token and requests a new one when saved is expired
Toolkit::test(function (): void {
	$http = Mockery::mock(ClientInterface::class);
	$http->shouldReceive('sendRequest')
		->andReturn(new Response(200, [], '{"access_token":"token","expires_in":30,"token_type":"type","scope":"scope"}'));

	$client = new AccessTokenProvider(new Client($http, new HttpFactory()));

	$token = $client->getAccessToken(new Config('foo', 'bar'));

	Assert::same('token', $token->getAccessToken());
	Assert::same(30, $token->getExpiresIn());
	Assert::same('type', $token->getTokenType());
	Assert::same('scope', $token->getScope());
});
