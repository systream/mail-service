<?php

namespace Systream\Mail\QueueHandler;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;

class RedisQueHandlerAdapter implements QueueHandlerInterface
{
	const DEFAULT_QUEUE_NAME = 'mail-que';

	/**
	 * @var \Redis
	 */
	private $redis;

	/**
	 * @var string
	 */
	private $queueName;

	/**
	 * RedisQueHandlerAdapter constructor.
	 * @param \Redis $redis
	 * @param string $queueName
	 */
	public function __construct(\Redis $redis, $queueName = self::DEFAULT_QUEUE_NAME)
	{
		$this->redis = $redis;
		$this->queueName = $queueName;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return void
	 */
	public function push(MailQueueItemInterface $mailQueueItem)
	{
		$this->redis->lPush($this->queueName, serialize($mailQueueItem));
	}

	/**
	 * @return MailQueueItemInterface
	 */
	public function pop()
	{
		return unserialize($this->redis->lPop($this->queueName));
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return Void
	 */
	public function ack(MailQueueItemInterface $mailQueueItem)
	{

	}
}