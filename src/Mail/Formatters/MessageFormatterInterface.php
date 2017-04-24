<?php

namespace Systream\Mail\Formatters;


interface MessageFormatterInterface
{
	/**
	 * @param string $body
	 * @return string
	 */
	public function process($body);
}