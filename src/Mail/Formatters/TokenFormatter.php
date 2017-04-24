<?php

namespace Systream\Mail\Formatters;



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
	 * @param string $body
	 * @return string
	 */
	public function process($body)
	{
		foreach ($this->tokens as $tokenName => $tokenValue) {
			$body = str_replace('{$' . $tokenName . '}', $tokenValue, $body);
		}

		return $body;
	}
}