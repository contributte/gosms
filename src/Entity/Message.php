<?php declare(strict_types = 1);

namespace Contributte\Gosms\Entity;

use DateTimeImmutable;

class Message
{

	private string $message;

	/** @var array<mixed> */
	private array $recipients = [];

	private int $channel;

	private ?DateTimeImmutable $expectedSendStart = null;

	/**
	 * @param mixed[] $recipients
	 */
	public function __construct(string $message, array $recipients, int $channel)
	{
		$this->message = $message;
		$this->recipients = $recipients;
		$this->channel = $channel;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @return mixed[]
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
	 * @return mixed[]
	 */
	public function toArray(): array
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
