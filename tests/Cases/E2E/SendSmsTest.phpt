<?php declare(strict_types = 1);

namespace Tests\Cases\E2E;

use Contributte\Gosms\Client\AccountClient;
use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\DI\GoSmsExtension;
use Contributte\Gosms\Entity\Message;
use Contributte\Gosms\Exception\ClientException;
use Contributte\Tester\Toolkit;
use Contributte\Tester\Utils\ContainerBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Nette\Bridges\Psr\PsrCacheAdapter;
use Nette\Caching\Storages\MemoryStorage;
use Nette\DI\Compiler;
use Nette\Neon\Neon;
use Nette\Utils\Validators;
use stdClass;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

const CONFIG_PATH = __DIR__ . '/../../Fixtures/gosms.neon';

if (!is_file(CONFIG_PATH)) {
	Environment::skip(sprintf('Missing configuration file "%s".', CONFIG_PATH));
}

Toolkit::test(function (): void {
	$config = Neon::decodeFile(CONFIG_PATH);

	$container = ContainerBuilder::of()
		->withCompiler(static function (Compiler $compiler) use ($config): void {
			$compiler->addExtension('gosms', new GoSmsExtension())
				->addConfig([
					'services' => [
						'http.client' => Client::class,
						'http.factory' => HttpFactory::class,
						'storage' => MemoryStorage::class,
						'cache' => PsrCacheAdapter::class,
					],
					'gosms' => [
						'clientId' => $config['clientId'],
						'clientSecret' => $config['clientSecret'],
					],
				]);
		})->build();

	$messageClient = $container->getService('gosms.message');
	assert($messageClient instanceof MessageClient);

	$accountClient = $container->getService('gosms.account');
	assert($accountClient instanceof AccountClient);

	$message = new Message('Automatic test message', [$config['phone']], $config['channel']);
	$response = $messageClient->test($message);
	Assert::same('CONCEPT', $response->sendingInfo->status);

	$data = $accountClient->detail();
	Assert::type(stdClass::class, $data);

	if ($config['sendSms'] === true) {
		$response = $messageClient->send($message);
		$messageId = $response->parsedId;
		Assert::true(Validators::isNumericInt($messageId));

		$response = $messageClient->detail($messageId);
		Assert::same('SMS', $response->messageType);

		$response = $messageClient->replies($messageId);
		Assert::false($response->reply->hasReplies);

		sleep(2); // wait for send sms
		$messageClient->delete($messageId);
		Assert::exception(function () use ($messageId, $messageClient): void {
			$messageClient->detail($messageId);
		}, ClientException::class);
	}
});
