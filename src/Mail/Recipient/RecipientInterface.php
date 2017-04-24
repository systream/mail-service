<?php

namespace Systream\Mail\Recipient;


interface RecipientInterface
{

	/**
	 * @return string
	 */
	public function getEmail();

	/**
	 * @return string
	 */
	public function getName();
}