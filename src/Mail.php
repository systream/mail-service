<?php

namespace Systream;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailSender\MailSenderInterface;
use Systream\Mail\MailTemplate\MailTemplateInterface;
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
		foreach ($message->getRecipients() as $recipient) {
			$this->mailer->addAddress($recipient);
		}

		$mailTemplate = $message->getMailTemplate();
		$body = $mailTemplate->getTemplate();
		foreach ($mailQueueItem->getMessageFormatters() as $messageFormatter) {
			$body = $messageFormatter->process($body);
		}

		$this->mailer->setMessage($body);
		$this->mailer->setSubject($mailTemplate->getSubject());
		return $this->mailer->send();
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