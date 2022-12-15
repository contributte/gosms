<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\GuzzletteClient;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use stdClass;

/**
 * @method stdClass getConfig()
 */
class GoSmsExtension extends CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
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
		$config = $this->getConfig();

		$this->compiler->loadDefinitionsFromConfig([
			$this->prefix('httpClient') => $config->httpClient ?? GuzzletteClient::class,
			$this->prefix('accessTokenProvider') => $config->accessTokenProvider ?? $this->accessTokenCacheProviderStatement(),
		]);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [
				$config->clientId,
				$config->clientSecret,
			])
			->setAutowired(false);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [$this->prefix('@config')]);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$this->prefix('@config')]);
	}

	private function accessTokenCacheProviderStatement(): Nette\DI\Definitions\Statement
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('cache'))
			->setFactory(Nette\Caching\Cache::class, ['namespace' => 'Contributte/Gosms'])
			->setAutowired(false);

		return new Nette\DI\Definitions\Statement(AccessTokenCacheProvider::class, [$this->prefix('@httpClient'), $this->prefix('@cache')]);
	}

}
