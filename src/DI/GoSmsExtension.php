<?php declare(strict_types = 1);

namespace Contributte\Gosms\DI;

use Contributte\Gosms\Auth\AccessTokenSessionProvider;
use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\Config;
use Contributte\Gosms\Http\GuzzletteClient;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\Utils\Validators;

class GoSmsExtension extends CompilerExtension
{

	/** @var mixed[] */
	private $defaults = [
		'clientId' => null,
		'clientSecret' => null,
		'httpClient' => GuzzletteClient::class,
		'accessTokenProvider' => AccessTokenSessionProvider::class,
	];

	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults);
		$builder = $this->getContainerBuilder();

		Validators::assertField($config, 'clientId', 'string|number');
		Validators::assertField($config, 'clientSecret', 'string');
		Validators::assertField($config, 'httpClient', 'string');
		Validators::assertField($config, 'accessTokenProvider', 'string');

		$configStatement = new Statement(Config::class, [
			$config['clientId'],
			$config['clientSecret'],
		]);

		// HttpClient
		$hc = $builder->addDefinition($this->prefix('httpClient'));
		Compiler::loadDefinition($hc, $config['httpClient']);

		// AccessTokenProvider
		$atp = $builder->addDefinition($this->prefix('accessTokenProvider'));
			Compiler::loadDefinition($atp, $config['accessTokenProvider']);

		// Message Client
		$builder->addDefinition($this->prefix('message'))
			->setFactory(MessageClient::class, [$configStatement]);

		// Account Client
		$builder->addDefinition($this->prefix('account'))
			->setFactory(AccountClient::class, [$configStatement]);
	}

}
