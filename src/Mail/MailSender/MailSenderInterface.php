<?php

namespace Systream\Mail\MailSender;


use Systream\Mail\Recipient\RecipientInterface;

interface MailSenderInterface
{

	/**
	 * @return bool
	 */
	public function send();

	/**
	 * @param RecipientInterface $recipient
	 */
	public function addRecipient(RecipientInterface $recipient);

	/**
	 * @param string $body
	 */
	public function setMessage($body);

	/**
	 * @param string $subject
	 */
	public function setSubject($subject);

}