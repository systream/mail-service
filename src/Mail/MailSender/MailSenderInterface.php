<?php

namespace Systream\Mail\MailSender;


use Systream\Mail\Recipient\RecipientInterface;

interface MailSenderInterface
{

	public function send();


	/**
	 * @param RecipientInterface $recipient
	 */
	public function addAddress(RecipientInterface $recipient);

	/**
	 * @param string $body
	 */
	public function setMessage($body);

	/**
	 * @param string $subject
	 */
	public function setSubject($subject);

}