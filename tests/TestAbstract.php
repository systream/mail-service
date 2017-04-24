<?php

namespace Tests\Systream;

use Guzzle\Http\Client;

abstract class TestAbstract extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		$this->mailCatcher = new Client($this->getMailCatcherAPIAddress());
		// clean emails between tests
		$this->cleanMessages();

		parent::setUp();
	}

	/**
	 * @return string
	 */
	protected function getMailCatcherAPIAddress()
	{
		return 'http://127.0.0.1:1080';
	}

	/**
	 * @var \Guzzle\Http\Client
	 */
	private $mailCatcher;


	public function cleanMessages()
	{
		$this->mailCatcher->delete('/messages')->send();
	}

	public function getLastMessage()
	{
		$messages = $this->getMessages();
		if (empty($messages)) {
			$this->fail("No messages received");
		}
		// messages are in descending order
		return reset($messages);
	}

	/**
	 * @param string $senderEmail
	 * @return array
	 */
	public function getMessagesBySender($senderEmail)
	{
		$messages = $this->getMessages();
		if (empty($messages)) {
			$this->fail("No messages received");
		}

		$return = array();
		foreach ($messages as $message) {
			if ($message->sender == '<' . $senderEmail . '>') {
				$return[] = $message;
			}
		}
		return $return;
	}

	/**
	 * @return array
	 */
	public function getMessages()
	{
		$jsonResponse = $this->mailCatcher->get('/messages')->send();
		return json_decode($jsonResponse->getBody());
	}

	public function assertEmailIsSent($description = '')
	{
		$this->assertNotEmpty($this->getMessages(), $description);
	}

	public function assertEmailSubjectContains($needle, $email, $description = '')
	{
		$this->assertContains($needle, $email->subject, $description);
	}

	public function assertEmailSubjectEquals($expected, $email, $description = '')
	{
		$this->assertContains($expected, $email->subject, $description);
	}

	public function assertEmailHtmlContains($needle, $email, $description = '')
	{
		$response = $this->mailCatcher->get("/messages/{$email->id}.html")->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}

	public function assertEmailTextContains($needle, $email, $description = '')
	{
		$response = $this->mailCatcher->get("/messages/{$email->id}.plain")->send();
		$this->assertContains($needle, (string)$response->getBody(), $description);
	}

	public function assertEmailSenderEquals($expected, $email, $description = '')
	{
		$response = $this->mailCatcher->get("/messages/{$email->id}.json")->send();
		$email = json_decode($response->getBody());
		$this->assertEquals($expected, $email->sender, $description);
	}

	public function assertEmailRecipientsContain($needle, $email, $description = '')
	{
		$response = $this->mailCatcher->get("/messages/{$email->id}.json")->send();
		$email = json_decode($response->getBody());
		$this->assertContains($needle, $email->recipients, $description);
	}
}
