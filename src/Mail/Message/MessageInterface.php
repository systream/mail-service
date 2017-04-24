<?php

namespace Systream\Mail\Message;


use Systream\Mail\MailTemplate\MailTemplateInterface;
use Systream\Mail\Recipient\RecipientInterface;

interface MessageInterface
{

	/**
	 * @return RecipientInterface[]
	 */
	public function getRecipients();

	/**
	 * @return MailTemplateInterface
	 */
	public function getMailTemplate();

	/**
	 * @return string[]
	 */
	//public function getAttachments();
}