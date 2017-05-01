<?php

namespace Systream;


use Systream\Mail\Exception\InvalidMessageException;
use Systream\Mail\Exception\SendFailedException;
use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailSender\MailSenderInterface;
use Systream\Mail\QueueHandler\QueueHandlerInterface;

class Mail
{
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
			return false;
		}

		$message = $mailQueueItem->getMessage();
		$recipients = $message->getRecipients();
		if (empty($recipients)) {
			throw new InvalidMessageException('Recipients not set.');
		}

		$this->mailer->reset();

		foreach ($recipients as $recipient) {
			$this->mailer->addRecipient($recipient);
		}

		$mailTemplate = $message->getMailTemplate();

		foreach ($mailQueueItem->getMessageFormatters() as $messageFormatter) {
			$mailTemplate = $messageFormatter->process($mailTemplate);
		}

		$body = $mailTemplate->getTemplate();
		$subject = $mailTemplate->getSubject();

		if (!$body) {
			throw new InvalidMessageException('Message empty.');
		}

		if (!$subject) {
			throw new InvalidMessageException('Subject is empty.');
		}

		$this->mailer->setMessage($body);
		$this->mailer->setSubject($subject);
		try {
			return $this->mailer->send();
		} catch (\Exception $exception) {
			throw new SendFailedException('Send failed: ' . $exception->getMessage(), 1, $exception);
		}
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	public function queue(MailQueueItemInterface $mailQueueItem)
	{
		$this->queueHandler->publish($mailQueueItem);
	}

	public function consume()
	{
		/** @var MailQueueItemInterface $mailQueueItem */
		$mailQueueItem = $this->queueHandler->getNext();
		if ($mailQueueItem->getScheduledSendingTime() && $mailQueueItem->getScheduledSendingTime() > new \DateTime()) {

		}

	}
}