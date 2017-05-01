<?php

namespace Systream\Mail\Formatters;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;
use Systream\Mail\MailTemplate\MailTemplateInterface;

interface MessageFormatterInterface
{
	/**
	 * @param MailTemplateInterface $mailTemplate
	 * @return MailQueueItemInterface
	 */
	public function process(MailTemplateInterface $mailTemplate);
}