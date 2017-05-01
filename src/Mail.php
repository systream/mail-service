<?php

namespace Systream;

use Systream\Mail\Exception\InvalidMessageException;
use Systream\Mail\Exception\SendFailedException;
use Systream\Mail\LoggerTrait;
use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailSender\MailSenderInterface;
use Systream\Mail\QueueHandler\QueueHandlerInterface;

class Mail
{
	use LoggerTrait;

	/**
	 * @var MailSenderInterface
	 */
	private $mailer;

	/**
	 * @var QueueHandlerInterface
	 */
	private $queueHandler;

	/**
	 * PHPMailerAdapter constructor.
	 * @param MailSenderInterface $mailer
	 * @param QueueHandlerInterface $queueHandler
	 */
	public function __construct(MailSenderInterface $mailer, QueueHandlerInterface $queueHandler)
	{
		$this->mailer = $mailer;
		$this->queueHandler = $queueHandler;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return bool
	 * @throws SendFailedException
	 */
	public function send(MailQueueItemInterface $mailQueueItem)
	{
		if (
			$mailQueueItem->getScheduledSendingTime() &&
			$mailQueueItem->getScheduledSendingTime() > new \DateTime()
		) {
			$this->info('Not yet scheduled', $mailQueueItem);
			return false;
		}

		$this->setUpMailer($mailQueueItem);

		try {
			$return = $this->mailer->send();
		} catch (\Exception $exception) {
			$message = $exception->getMessage();
			$this->critical('Send failed: ' . $message, $mailQueueItem);
			throw new SendFailedException('Send failed: ' . $message, 1, $exception);
		}

		$this->info('Sent', $mailQueueItem);
		return $return;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	public function queue(MailQueueItemInterface $mailQueueItem)
	{
		$this->queueHandler->push($mailQueueItem);
	}

	/**
	 * consume
	 */
	public function consume()
	{
		/** @var MailQueueItemInterface $mailQueueItem */
		while ($mailQueueItem = $this->queueHandler->pop()) {
			if ($this->send($mailQueueItem)) {
				$this->queueHandler->ack($mailQueueItem);
			}
		}
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return Mail\Message\MessageInterface
	 */
	private function setUpMailer(MailQueueItemInterface $mailQueueItem)
	{
		$this->validateMailQueItem($mailQueueItem);
		$message = $mailQueueItem->getMessage();
		$this->mailer->reset();

		foreach ($message->getRecipients() as $recipient) {
			$this->mailer->addRecipient($recipient);
		}

		$mailTemplate = $message->getMailTemplate();

		foreach ($mailQueueItem->getMessageFormatters() as $messageFormatter) {
			$mailTemplate = $messageFormatter->process($mailTemplate);
		}

		$this->mailer->setMessage($mailTemplate->getTemplate());
		$this->mailer->setSubject($mailTemplate->getSubject());
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	private function validateMailQueItem(MailQueueItemInterface $mailQueueItem)
	{
		if (empty($mailQueueItem->getMessage()->getRecipients())) {
			$this->error('Recipients not set', $mailQueueItem);
			throw new InvalidMessageException('Recipients not set.');
		}

		$mailTemplate = $mailQueueItem->getMessage()->getMailTemplate();
		if (!$mailTemplate->getTemplate()) {
			$this->error('Message empty', $mailQueueItem);
			throw new InvalidMessageException('Message empty.');
		}

		if (!$mailTemplate->getSubject()) {
			$this->error('Subject empty', $mailQueueItem);
			throw new InvalidMessageException('Subject is empty.');
		}
	}
}