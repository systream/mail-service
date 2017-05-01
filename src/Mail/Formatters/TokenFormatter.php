<?php

namespace Systream\Mail\Formatters;


use Systream\Mail\MailTemplate\MailTemplateInterface;

class TokenFormatter implements MessageFormatterInterface
{
	/**
	 * @var array
	 */
	private $tokens = array();

	/**
	 * @param array $tokens
	 */
	public function __construct(array $tokens)
	{
		$this->tokens = $tokens;
	}

	/**
	 * @param MailTemplateInterface $mailTemplate
	 * @return MailTemplateInterface
	 */
	public function process(MailTemplateInterface $mailTemplate)
	{
		$body = $mailTemplate->getTemplate();
		$subject = $mailTemplate->getSubject();
		foreach ($this->tokens as $tokenName => $tokenValue) {
			$body = str_replace('{$' . $tokenName . '}', $tokenValue, $body);
			$subject = str_replace('{$' . $tokenName . '}', $tokenValue, $subject);
		}

		$mailTemplate = $mailTemplate->withBody($body);
		$mailTemplate = $mailTemplate->withSubject($subject);

		return $mailTemplate;
	}
}