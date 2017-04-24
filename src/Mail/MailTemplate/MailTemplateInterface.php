<?php

namespace Systream\Mail\MailTemplate;


interface MailTemplateInterface
{
	/**
	 * @return string
	 */
	public function getTemplate();

	/**
	 * @return string
	 */
	public function getSubject();
}