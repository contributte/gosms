<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenClient;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\DI\GoSmsExtension;
use Contributte\Gosms\Http\GuzzletteClient;
use Contributte\Guzzlette\DI\GuzzleExtension;
use Nette\Bridges\CacheDI\CacheExtension;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test if Extension and Config is created
test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('guz', new GuzzleExtension());
		$compiler->addExtension('caching', new CacheExtension(TMP_DIR));
		$compiler->addExtension('gosms', new GoSmsExtension())
			->addConfig([
				'gosms' => [
					'clientId' => 'X',
					'clientSecret' => 'Y',
				],
			]);
	}, 1);

	/** @var Container $container */
	$container = new $class();

	// Service created
	Assert::type(MessageClient::class, $container->getService('gosms.message'));
	Assert::type(AccountClient::class, $container->getService('gosms.account'));
});

test(function (): void {
	$loader = new ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('guz', new GuzzleExtension());
		$compiler->addExtension('gosms', new GoSmsExtension())
			->addConfig([
				'gosms' => [
					'clientId' => 'X',
					'clientSecret' => 'Y',
					'httpClient' => GuzzletteClient::class,
					'accessTokenProvider' => ['type' => AccessTokenClient::class],
				],
			]);
	}, 1);

	/** @var Container $container */
	$container = new $class();

	// Service created
	Assert::type(MessageClient::class, $container->getService('gosms.message'));
	Assert::type(AccountClient::class, $container->getService('gosms.account'));
});
