<?php

namespace Systream\Mail;


use Systream\Mail\MailTemplate\MailTemplateInterface;
use Systream\Mail\Message\MessageInterface;
use Systream\Mail\Recipient\RecipientInterface;

class Message implements MessageInterface
{

	/**
	 * @var RecipientInterface[]
	 */
	protected $recipients = [];

	/**
	 * @var MailTemplateInterface
	 */
	private $mailTemplate;

	/**
	 * @param MailTemplateInterface $mailTemplate
	 */
	public function __construct(MailTemplateInterface $mailTemplate)
	{
		$this->mailTemplate = $mailTemplate;
	}

	/**
	 * @param RecipientInterface $recipient
	 */
	public function addRecipient(RecipientInterface $recipient)
	{
		$this->recipients[] = $recipient;
	}

	/**
	 * @return RecipientInterface[]
	 */
	public function getRecipients()
	{
		return $this->recipients;
	}

	/**
	 * @return MailTemplateInterface
	 */
	public function getMailTemplate()
	{
		return $this->mailTemplate;
	}
}