<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\GuzzletteClient;
use Nette\Caching\Cache;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
class GoSmsExtension extends CompilerExtension
{

	public const CACHE_NAMESPACE = 'Contributte/Gosms';

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'clientId' => Expect::string()->required(),
			'clientSecret' => Expect::string()->required(),
			'httpClient' => Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class)),
			'accessTokenProvider' => Expect::anyOf(Expect::string(), Expect::array(), Expect::type(Statement::class)),
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

		// Message client
		$builder
			->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [$this->prefix('@config')]);

		// Account client
		$builder
			->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$this->prefix('@config')]);

		// Http client
		$this->compiler->loadDefinitionsFromConfig([
			$this->prefix('httpClient') => $config->httpClient ?? GuzzletteClient::class,
		]);

		// Access token
		$accessTokenProvider = $config->accessTokenProvider;
		if (!$config->accessTokenProvider) {
			$builder
				->addDefinition($this->prefix('cache'))
				->setFactory(Cache::class, ['namespace' => self::CACHE_NAMESPACE])
				->setAutowired(false);

			$accessTokenProvider = new Statement(AccessTokenCacheProvider::class, [$this->prefix('@httpClient'), $this->prefix('@cache')]);
		}

		$this->compiler->loadDefinitionsFromConfig([
			$this->prefix('accessTokenProvider') => $accessTokenProvider,
		]);
	}

}
