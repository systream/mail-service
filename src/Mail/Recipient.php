<?php

namespace Systream\Mail;


use Systream\Mail\Recipient\RecipientInterface;

class Recipient implements RecipientInterface
{
	/**
	 * @var
	 */
	private $email;

	/**
	 * @var
	 */
	private $name;

	/**
	 * @param string $email
	 * @param string $name
	 */
	public function __construct($email, $name)
	{
		$this->email = $email;
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}