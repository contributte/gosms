<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenProvider;
use Contributte\Gosms\Auth\AccessTokenProviderCache;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\Client;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
class GoSmsExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'clientId' => Expect::string()->required(),
			'clientSecret' => Expect::string()->required(),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Config
		$builder
			->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [
				$config->clientId,
				$config->clientSecret,
			])
			->setAutowired(false);

		// Client
		$builder->addDefinition($this->prefix('client'))
			->setFactory(Client::class)
			->setAutowired(false);

		// Message client
		$builder
			->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [
				$this->prefix('@accessTokenProvider'),
				$this->prefix('@client'),
				$this->prefix('@config'),
			]);

		// Account client
		$builder
			->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [
				$this->prefix('@accessTokenProvider'),
				$this->prefix('@client'),
				$this->prefix('@config'),
			]);

		// Access token provider
		$builder->addDefinition($this->prefix('access.token.provider.source'))
			->setFactory(AccessTokenProvider::class, [
				$this->prefix('@client'),
			])
			->setAutowired(false);

		// Access token provider cache
		$builder->addDefinition($this->prefix('accessTokenProvider'))
			->setFactory(AccessTokenProviderCache::class, [$this->prefix('@access.token.provider.source')])
			->setAutowired(false);
	}

}
