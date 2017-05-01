<?php

namespace Systream\Mail\MailTemplate;


class StringMailTemplate implements MailTemplateInterface
{
	/**
	 * @var string
	 */
	protected $subject;

	/**
	 * @var string
	 */
	private $template;

	/**
	 * StringMailTemplate constructor.
	 * @param string $template
	 * @param string $subject
	 */
	public function __construct($template, $subject)
	{
		$this->template = $template;
		$this->subject = $subject;
	}

	/**
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param string $subject
	 * @return static|MailTemplateInterface
	 */
	public function withSubject($subject)
	{
		$new = clone $this;
		$new->subject = $subject;
		return $new;
	}

	/**
	 * @param string $body
	 * @return static|MailTemplateInterface
	 */
	public function withBody($body)
	{
		$new = clone $this;
		$new->template = $body;
		return $new;
	}
}