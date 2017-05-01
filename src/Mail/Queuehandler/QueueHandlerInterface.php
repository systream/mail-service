<?php

namespace Systream\Mail\QueueHandler;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;

interface QueueHandlerInterface
{
	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return void
	 */
	public function push(MailQueueItemInterface $mailQueueItem);

	/**
	 * @return MailQueueItemInterface
	 */
	public function pop();

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return Void
	 */
	public function ack(MailQueueItemInterface $mailQueueItem);
}