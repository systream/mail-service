<?php

namespace Systream\Mail\MailQueueItem;


use Systream\Mail\Formatters\MessageFormatterInterface;
use Systream\Mail\Message\MessageInterface;

interface MailQueueItemInterface
{
	/**
	 * @return MessageInterface
	 */
	public function getMessage();

	/**
	 * @return MessageFormatterInterface[]
	 */
	public function getMessageFormatters();

	/**
	 * @return \DateTimeInterface|null
	 */
	public function getScheduledSendingTime();

	/**
	 * @return int
	 */
	public function getPriority();

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return array
	 */
	public function toArray();
}