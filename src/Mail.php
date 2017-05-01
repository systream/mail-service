<?php

namespace Systream;


use Psr\Log\LoggerInterface;
use Systream\Mail\Exception\InvalidMessageException;
use Systream\Mail\Exception\SendFailedException;
use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailSender\MailSenderInterface;
use Systream\Mail\QueueHandler\QueueHandlerInterface;

class Mail
{
	const LOG_INFO = 'info';
	const LOG_WARN = 'warn';
	const LOG_ERROR = 'error';
	const LOG_CRITICAL = 'critical';

	/**
	 * @var LoggerInterface
	 */
	protected $logger;

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
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
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
			$this->log('Not yet scheduled', $mailQueueItem, self::LOG_INFO);
			return false;
		}

		$message = $mailQueueItem->getMessage();
		$recipients = $message->getRecipients();
		if (empty($recipients)) {
			$this->log('Recipients not set', $mailQueueItem, self::LOG_ERROR);
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
			$this->log('Message empty', $mailQueueItem, self::LOG_ERROR);
			throw new InvalidMessageException('Message empty.');
		}

		if (!$subject) {
			$this->log('Subject empty', $mailQueueItem, self::LOG_ERROR);
			throw new InvalidMessageException('Subject is empty.');
		}

		$this->mailer->setMessage($body);
		$this->mailer->setSubject($subject);
		try {
			$return = $this->mailer->send();
			$this->log('Sent', $mailQueueItem, self::LOG_INFO);
			return $return;
		} catch (\Exception $exception) {
			$message = $exception->getMessage();
			$this->log('Send failed: ' . $message, $mailQueueItem, self::LOG_CRITICAL);
			throw new SendFailedException('Send failed: ' . $message, 1, $exception);
		}
	}

	/**
	 * @param string $message
	 * @param MailQueueItemInterface $mailQueueItem
	 * @param string $facility
	 */
	private function log($message, MailQueueItemInterface $mailQueueItem, $facility = self::LOG_INFO)
	{
		if (!$this->logger) {
			return;
		}

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

		switch ($facility) {
			case self::LOG_INFO:
				$this->logger->info($message, $context);
				break;
			case self::LOG_WARN:
				$this->logger->warning($message, $context);
				break;
			case self::LOG_ERROR:
				$this->logger->error($message, $context);
				break;
			case self::LOG_CRITICAL:
				$this->logger->critical($message, $context);
				break;
			default:
				$this->logger->debug($message);
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