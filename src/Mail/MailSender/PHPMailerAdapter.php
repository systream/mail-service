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
		$result = $this->mailer->send();
		$this->mailer->clearAllRecipients();
		$this->mailer->clearAddresses();
		$this->mailer->clearAttachments();
		$this->mailer->clearCustomHeaders();
		return $result;
	}

	/**
	 * @param RecipientInterface $recipient
	 */
	public function addRecipient(RecipientInterface $recipient)
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