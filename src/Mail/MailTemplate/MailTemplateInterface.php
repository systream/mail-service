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

	/**
	 * @param string $subject
	 * @return static|MailTemplateInterface
	 */
	public function withSubject($subject);

	/**
	 * @param string $body
	 * @return static|MailTemplateInterface
	 */
	public function withBody($body);
}