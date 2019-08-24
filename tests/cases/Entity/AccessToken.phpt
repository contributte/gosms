<?php declare(strict_types = 1);

use Contributte\Gosms\Entity\AccessToken;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	Assert::false(
		(new AccessToken('foo', 3600, 'asdf', 'scope'))->isExpired()
	);

	Assert::false(
		(new AccessToken('foo', 3600, 'asdf', 'scope', new DateTimeImmutable('+ 40 minutes')))->isExpired()
	);

	Assert::true(
		(new AccessToken('foo', 3600, 'asdf', 'scope', new DateTimeImmutable('+2 minutes')))->isExpired()
	);
});
