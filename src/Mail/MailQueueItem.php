<?php

namespace Systream\Mail;


use Ramsey\Uuid\Uuid;
use Systream\Mail\Formatters\MessageFormatterInterface;
use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\Message\MessageInterface;

class MailQueueItem implements MailQueueItemInterface
{
	const HIGH_PRIORITY = 4;
	const MED_PRIORITY = 3;
	const NORMAL_PRIORITY = 2;
	const LOW_PRIORITY = 1;

	/**
	 * @var MessageFormatterInterface[]
	 */
	protected $formatters = [];

	/**
	 * @var int
	 */
	protected $priority = self::NORMAL_PRIORITY;

	/**
	 * @var \Ramsey\Uuid\UuidInterface
	 */
	protected $uuid;

	/**
	 * @var MessageInterface
	 */
	private $message;

	/**
	 * @var \DateTimeInterface
	 */
	private $scheduledSendingTime;

	/**
	 * MailQueueItem constructor.
	 * @param MessageInterface $message
	 * @param \DateTimeInterface|null $scheduledSendingTime
	 */
	public function __construct(MessageInterface $message, \DateTimeInterface $scheduledSendingTime = null)
	{
		$this->message = $message;
		$this->scheduledSendingTime = $scheduledSendingTime;
		$this->uuid = Uuid::uuid4();
	}

	/**
	 * @param MessageFormatterInterface $formatter
	 */
	public function addMessageFormatter(MessageFormatterInterface $formatter)
	{
		$this->formatters[] = $formatter;
	}

	/**
	 * @return MessageInterface
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return MessageFormatterInterface[]
	 */
	public function getMessageFormatters()
	{
		return $this->formatters;
	}

	/**
	 * @return \DateTimeInterface|null
	 */
	public function getScheduledSendingTime()
	{
		return $this->scheduledSendingTime;
	}

	/**
	 * @param int $priority
	 */
	public function setPriority($priority)
	{
		$this->priority = (int)$priority;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->uuid;
	}

	/**
	 * @return array
	 */
	public function toArray()
	{
		$recipients = [];
		foreach ($this->getMessage()->getRecipients() as $recipient) {
			$recipients[] = [
				'email' => $recipient->getEmail(),
				'name' => $recipient->getName()
			];
		}

		return [
			'id' => $this->getId(),
			'recipients' => $recipients,
			'priority' => $this->getPriority(),
			'subject' => $this->getMessage()->getMailTemplate()->getSubject(),
			'template' => $this->getMessage()->getMailTemplate()->getTemplate(),
			'scheduled' => $this->getScheduledSendingTime()
		];
	}
}