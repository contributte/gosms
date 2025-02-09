<?php declare(strict_types = 1);

use Contributte\Gosms\Auth\AccessTokenProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\DI\GoSmsExtension;
use Contributte\Tester\Environment;
use Contributte\Tester\Toolkit;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nette\Bridges\CacheDI\CacheExtension;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test if Extension and Config is created
Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('caching', new CacheExtension(Environment::getTmpDir()));
		$compiler->addExtension('gosms', new GoSmsExtension())
			->addConfig([
				'services' => [
					'http.client' => Client::class,
					'http.factory' => HttpFactory::class,
					'cache.psr' => PsrCacheAdapter::class,
				],
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

Toolkit::test(function (): void {
	$loader = new ContainerLoader(Environment::getTestDir(), true);
	$class = $loader->load(function (Compiler $compiler): void {
		$compiler->addExtension('gosms', new GoSmsExtension())
			->addConfig([
				'services' => [
					'http.client' => Client::class,
					'http.factory' => HttpFactory::class,
					'gosms.accessTokenProvider' => AccessTokenProvider::class,
				],
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
