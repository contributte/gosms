<?php declare(strict_types = 1);

use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\DI\GoSmsExtension;
use Contributte\Gosms\Entity\Message;
use Contributte\Guzzlette\DI\GuzzleExtension;
use GuzzleHttp\Exception\ClientException;
use Nette\Caching\Storages;
use Nette\DI;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\Neon\Neon;
use Nette\Utils\Validators;
use Tester\Assert;
use Tester\Environment;

require_once __DIR__ . '/../../bootstrap.php';

$configPath = __DIR__ . '/../../Fixtures/gosms.neon';
if (!is_file($configPath)) {
	Environment::skip('No configuration file.');
}

$configData = Neon::decodeFile($configPath);

/**
 * @param array<string> $configData
 * @see gosms.neon.example
 */
function createMessageClient(string $clientId, string $clientSecret): MessageClient
{
	$loader = new DI\ContainerLoader(TEMP_DIR, true);
	$class = $loader->load(function (Compiler $compiler) use ($clientId, $clientSecret): void {
		$compiler->addExtension('guz', new GuzzleExtension());
		$compiler->addExtension('gosms', new GoSmsExtension())
			->addConfig([
				'services' => [
					'storage' => Storages\MemoryStorage::class,
				],
				'gosms' => [
					'clientId' => $clientId,
					'clientSecret' => $clientSecret,
				],
			]);
	}, 1);

	$container = new $class();
	assert($container instanceof Container);

	$messageClient = $container->getService('gosms.message');
	assert($messageClient instanceof MessageClient);

	return $messageClient;
}


test(function () use ($configData): void {
	$messageClient = createMessageClient($configData['clientId'], $configData['clientSecret']);

	$message = new Message('Automatic test message', [$configData['phone']], $configData['channel']);
	$response = $messageClient->test($message);
	Assert::same('CONCEPT', $response->sendingInfo->status);

	if ($configData['testOnly'] === false) {
		$response = $messageClient->send($message);
		$messageId = $response->parsedId;
		Assert::true(Validators::isNumericInt($messageId));

		$response = $messageClient->detail($messageId);
		Assert::same('SMS', $response->messageType);

		$response = $messageClient->replies($messageId);
		Assert::false($response->reply->hasReplies);

		sleep(2); // wait for send sms
		$messageClient->delete($messageId);
		Assert::exception(function () use ($messageClient, $messageId) {
			$messageClient->detail($messageId);
		}, ClientException::class);
	}
});
