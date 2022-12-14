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
			$this->prefix('accessTokenProvider') => $config->accessTokenProvider ?? AccessTokenCacheProvider::class,
		]);

		$this->buildConfig($config->clientId, $config->clientSecret);
		$this->buildMessageClient();
		$this->buildAccountClient();
	}

	private function buildConfig(string $clientId, string $clientSecret): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('config'))
			->setFactory(Config::class, [
				$clientId,
				$clientSecret,
			])
			->setAutowired(false);
	}

	private function buildAccountClient(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$this->prefix('@config')]);
	}

	private function buildMessageClient(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [$this->prefix('@config')]);
	}

}
