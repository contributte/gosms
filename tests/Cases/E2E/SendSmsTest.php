<?php declare(strict_types = 1);

namespace Tests\Cases\E2E;

use Contributte\Gosms\Client\MessageClient;
use Contributte\Gosms\DI\GoSmsExtension;
use Contributte\Gosms\Entity\Message;
use Contributte\Gosms\Exception\ClientException;
use Contributte\Guzzlette\DI\GuzzleExtension;
use Nette\Caching\Storages\MemoryStorage;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\Neon\Neon;
use Nette\Utils\Validators;
use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;

require_once __DIR__ . '/../../bootstrap.php';

class SendSmsTest extends TestCase
{

	private MessageClient $client;

	/** @var mixed[] */
	private array $config = [];

	public function setUp(): void
	{
		parent::setUp();

		// Parse config
		$configPath = __DIR__ . '/../../Fixtures/gosms.neon';

		if (!is_file($configPath)) {
			Environment::skip(sprintf('Missing configuration file "%s".', $configPath));
		}

		$this->config = Neon::decodeFile($configPath);

		// Create client
		$container = $this->createContainer();
		$messageClient = $container->getService('gosms.message');
		assert($messageClient instanceof MessageClient);
		$this->client = $messageClient;
	}

	public function testClient(): void
	{
		$message = new Message('Automatic test message', [$this->config['phone']], $this->config['channel']);
		$response = $this->client->test($message);
		Assert::same('CONCEPT', $response->sendingInfo->status);

		if ($this->config['sendSms'] === true) {
			$response = $this->client->send($message);
			$messageId = $response->parsedId;
			Assert::true(Validators::isNumericInt($messageId));

			$response = $this->client->detail($messageId);
			Assert::same('SMS', $response->messageType);

			$response = $this->client->replies($messageId);
			Assert::false($response->reply->hasReplies);

			sleep(2); // wait for send sms
			$this->client->delete($messageId);
			Assert::exception(function () use ($messageId): void {
				$this->client->detail($messageId);
			}, ClientException::class);
		}
	}

	private function createContainer(): Container
	{
		$loader = new ContainerLoader(TEMP_DIR, true);
		$class = $loader->load(function (Compiler $compiler): void {
			$compiler->addExtension('guz', new GuzzleExtension());
			$compiler->addExtension('gosms', new GoSmsExtension())
				->addConfig([
					'services' => [
						'storage' => MemoryStorage::class,
					],
					'gosms' => [
						'clientId' => $this->config['clientId'],
						'clientSecret' => $this->config['clientSecret'],
					],
				]);
		}, __FILE__);

		$container = new $class();
		assert($container instanceof Container);

		return $container;
	}

}

(new SendSmsTest())->run();
