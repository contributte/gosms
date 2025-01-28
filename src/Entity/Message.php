<?php declare(strict_types = 1);

namespace Contributte\Gosms\Entity;

use DateTimeImmutable;

final class Message implements \JsonSerializable
{

	private ?DateTimeImmutable $expectedSendStart = null;

	/**
	 * @param array<mixed> $recipients
	 */
	public function __construct(private string $message, private array $recipients, private int $channel)
	{
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @return array<mixed>
	 */
	public function getRecipients(): array
	{
		return $this->recipients;
	}

	public function getChannel(): int
	{
		return $this->channel;
	}

	public function getExpectedSendStart(): ?DateTimeImmutable
	{
		return $this->expectedSendStart;
	}

	public function setExpectedSendStart(?DateTimeImmutable $expectedSendStart): void
	{
		$this->expectedSendStart = $expectedSendStart;
	}

	/**
	 * @return array<string, mixed>
	 */
	public function jsonSerialize(): mixed
	{
		$arr = [
			'message' => $this->message,
			'recipients' => $this->recipients,
			'channel' => $this->channel,
		];

		if ($this->expectedSendStart !== null) {
			$arr['expectedSendStart'] = $this->expectedSendStart->format($this->expectedSendStart::ATOM);
		}

		return $arr;
	}

}
