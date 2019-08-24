<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\GuzzletteClient;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Schema\Expect;
use stdClass;

/**
 * @property-read stdClass $config
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
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$configStatement = new Statement(Config::class, [
			$config->clientId,
			$config->clientSecret,
		]);

		// HttpClient, AccessTokenProvider
		$this->compiler->loadDefinitionsFromConfig([
			$this->prefix('httpClient') => $config->httpClient ?? GuzzletteClient::class,
			$this->prefix('accessTokenProvider') => $config->accessTokenProvider ?? AccessTokenCacheProvider::class,
		]);

		// Message Client
		$builder->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [$configStatement]);

		// Account Client
		$builder->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$configStatement]);
	}

}
