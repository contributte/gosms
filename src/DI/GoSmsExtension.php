<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenCacheProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Exception\MissingClientException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\SimpleCache\CacheInterface;
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
			->setFactory(MessageClient::class, [$this->prefix('@config'), $this->prefix('@httpClient')]);

		// Account client
		$builder
			->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$this->prefix('@config'), $this->prefix('@httpClient')]);

		// Access token
		$accessTokenProvider = $config->accessTokenProvider;
		if (!$config->accessTokenProvider) {
			$builder
				->addDefinition($this->prefix('cache'))
				->setType(CacheInterface::class)
				->setFactory(PsrCacheAdapter::class)
				->setAutowired(false);

			$accessTokenProvider = new Statement(AccessTokenCacheProvider::class, [
				$this->prefix('@httpClient'),
				$this->prefix('@cache'),
			]);
		}

		$this->compiler->loadDefinitionsFromConfig([
			$this->prefix('accessTokenProvider') => $accessTokenProvider,
		]);
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		// client
		$client = $builder->getByType(ClientInterface::class);
		if ($client === null) {
			$httpClient = class_exists(Client::class)
				? Client::class
				: throw new MissingClientException('Install any psr-18 client and register.');

			$builder
				->addDefinition($this->prefix('httpClient'))
				->setFactory($httpClient)
				->setAutowired(false);
		} else {
			$builder->addAlias($this->prefix('httpClient'), $client);
		}

		$clientFactory = $builder->getByType(RequestFactoryInterface::class);
		if ($clientFactory === null) {
			$builder
				->addDefinition($this->prefix('httpClient.factory'))
				->setFactory(HttpFactory::class);
		}
	}

}
