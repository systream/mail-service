<?php

namespace Systream;



use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailSender\MailSenderInterface;

class Mail
{
	/**
	 * @var MailSenderInterface
	 */
	private $mailer;

	/**
	 * PHPMailerAdapter constructor.
	 * @param MailSenderInterface $mailer
	 */
	public function __construct(MailSenderInterface $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 */
	public function send(MailQueueItemInterface $mailQueueItem)
	{
		foreach ($mailQueueItem->getMessage()->getRecipients() as $recipient) {
			$this->mailer->addAddress($recipient);
		}

		$body = $mailQueueItem->getMessage()->getMailTemplate()->getTemplate();
		foreach ($mailQueueItem->getMessageFormatters() as $messageFormatter) {
			$body = $messageFormatter->process($body);
		}

		$this->mailer->setMessage($body);
		$this->mailer->setSubject($mailQueueItem->getMessage()->getMailTemplate()->getSubject());
		$this->mailer->send();
	}
}