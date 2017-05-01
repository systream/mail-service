<?php

namespace Systream\Mail;


use Psr\Log\LoggerInterface;
use Systream\Mail\MailQueueItem\MailQueueItemInterface;

trait LoggerTrait
{
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	/**
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @param string $message
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	protected function info($message, MailQueueItemInterface $mailQueueItem)
	{
		if (!$this->logger) {
			return;
		}

		$context = $this->getLogContext($mailQueueItem);
		$this->logger->info($message, $context);
	}

	/**
	 * @param string $message
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	protected function warning($message, MailQueueItemInterface $mailQueueItem)
	{
		if (!$this->logger) {
			return;
		}

		$context = $this->getLogContext($mailQueueItem);
		$this->logger->warning($message, $context);
	}

	/**
	 * @param string $message
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	protected function error($message, MailQueueItemInterface $mailQueueItem)
	{
		if (!$this->logger) {
			return;
		}

		$context = $this->getLogContext($mailQueueItem);
		$this->logger->error($message, $context);
	}

	/**
	 * @param string $message
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	protected function critical($message, MailQueueItemInterface $mailQueueItem)
	{
		if (!$this->logger) {
			return;
		}

		$context = $this->getLogContext($mailQueueItem);
		$this->logger->critical($message, $context);
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return array
	 */
	private function getLogContext(MailQueueItemInterface $mailQueueItem): array
	{
		$context = array(
			'subject' => $mailQueueItem->getMessage()->getMailTemplate()->getSubject(),
			'message' => $mailQueueItem->getMessage()->getMailTemplate()->getTemplate(),
			'recipients' => []
		);

		$dateTime = $mailQueueItem->getScheduledSendingTime();
		if ($dateTime) {
			$context['scheduled'] = $dateTime->format(DATE_ISO8601);
		}

		$recipients = $mailQueueItem->getMessage()->getRecipients();
		foreach ($recipients as $recipient) {
			$context['recipients'][] = ['email' => $recipient->getEmail(), 'name' => $recipient->getName()];
		}
		return $context;
	}

}