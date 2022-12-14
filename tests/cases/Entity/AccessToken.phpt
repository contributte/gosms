<?php declare(strict_types = 1);

use Contributte\Gosms\Entity\AccessToken;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

test(function (): void {
	Assert::true(
		(new AccessToken('foo', 29, 'asdf', 'scope'))->isExpired(),
	);

	Assert::false(
		(new AccessToken('foo', 30, 'asdf', 'scope'))->isExpired(),
	);

	Assert::true(
		(new AccessToken('foo', 3600, 'asdf', 'scope', new DateTimeImmutable('+29 seconds')))->isExpired(),
	);
	Assert::false(
		(new AccessToken('foo', 3600, 'asdf', 'scope', new DateTimeImmutable('+30 seconds')))->isExpired(),
	);
});
