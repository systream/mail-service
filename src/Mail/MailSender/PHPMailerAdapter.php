<?php


namespace Systream\Mail\MailSender;


use Systream\Mail\Recipient\RecipientInterface;

class PHPMailerAdapter implements MailSenderInterface
{

	/**
	 * @var \PHPMailer
	 */
	private $mailer;

	/**
	 * PHPMailerAdapter constructor.
	 * @param \PHPMailer $mailer
	 */
	public function __construct(\PHPMailer $mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * @return bool
	 */
	public function send()
	{
		return $this->mailer->send();
	}

	/**
	 * @param RecipientInterface $recipient
	 */
	public function addAddress(RecipientInterface $recipient)
	{
		$this->mailer->addAddress($recipient->getEmail(), $recipient->getName());
	}

	/**
	 * @param string $body
	 */
	public function setMessage($body)
	{
		$this->mailer->msgHTML($body);
	}

	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->mailer->Subject = $subject;
	}
}