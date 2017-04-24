<?php

namespace Systream\Mail\MailQueueItem;


use Systream\Mail\Formatters\TokenFormatter;
use Systream\Mail\MailQueueItem;
use Systream\Mail\MailTemplate\StringMailTemplate;
use Systream\Mail\Message;
use Systream\Mail\Recipient;

class MailQueueItemFactory
{
	/**
	 * @param string $subject
	 * @param string $body
	 * @param string $recipientEmail
	 * @param string|null $recipientName
	 * @param array $tokens
	 * @return MailQueueItem
	 */
	public static function make($subject, $body, $recipientEmail, $recipientName = null, array $tokens = array())
	{
		$tokenFormatter = new TokenFormatter($tokens);
		$mailTemplate = new StringMailTemplate($body, $subject);
		$message = new Message($mailTemplate);
		$message->addRecipient(new Recipient($recipientEmail, $recipientName));
		$mailQueueItem = new MailQueueItem($message);
		$mailQueueItem->addMessageFormatter($tokenFormatter);
		return $mailQueueItem;
	}
}