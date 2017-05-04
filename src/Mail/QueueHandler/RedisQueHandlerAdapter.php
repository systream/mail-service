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
	 * @return MailQueueItemInterface|null
	 */
	public function pop()
	{
		$data = $this->redis->lPop($this->queueName);
		if (!$data) {
			return null;
		}
		return unserialize($data);
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return Void
	 */
	public function ack(MailQueueItemInterface $mailQueueItem)
	{

	}
}