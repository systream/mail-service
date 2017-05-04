<?php

namespace Tests\Systream\Unit;

use Psr\Log\LoggerInterface;
use Systream\Mail;
use Tests\Systream\TestAbstract;

class MailTest extends TestAbstract
{

	/**
	 * @test
	 */
	public function SendTest_factory()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSenderEquals('<test@unit.test>', $email);
		$this->assertEmailRecipientsContain('<foo@bar.hu>', $email);
		$this->assertEmailSubjectEquals('subject', $email);
		$this->assertEmailHtmlContains('hello', $email);
		$this->assertEmailTextContains('hello', $email);
	}

	/**
	 * @test
	 */
	public function recipients()
	{
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$this->assertEquals('foo@bar.hu', $item->getMessage()->getRecipients()[0]->getEmail());
		$this->assertEquals('Foo Bar', $item->getMessage()->getRecipients()[0]->getName());
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function withoutSubject()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$mail->send($item);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function withoutMessage()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'',
			'foo@bar.hu',
			'Foo Bar'
		);

		$mail->send($item);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function withoutRecipient()
	{
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', 'Subject');
		$message = new Mail\Message($mailTemplate);
		$mailQueueItem = new Mail\MailQueueItem($message);

		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 * @_depends SendTest_factory
	 */
	public function sendDouble()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSenderEquals('<test@unit.test>', $email);
		$this->assertEmailRecipientsContain('<foo@bar.hu>', $email);
		$this->assertEmailSubjectEquals('subject', $email);
		$this->assertEmailHtmlContains('hello', $email);
		$this->assertEmailTextContains('hello', $email);

		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject 2',
			'hello 2',
			'foo2@bar2.hu',
			'Foo2 Bar2'
		);

		$this->cleanMessages();
		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSenderEquals('<test@unit.test>', $email);
		$this->assertEmailRecipientsContain('<foo2@bar2.hu>', $email);
		$this->assertCount(1, $email->recipients);
		$this->assertNotContains('foo@bar.hu', $email->recipients);
		$this->assertEmailSubjectEquals('subject 2', $email);
		$this->assertEmailHtmlContains('hello 2', $email);
		$this->assertEmailTextContains('hello 2', $email);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\SendFailedException
	 */
	public function sendFailed()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mailer->method('send')->will($this->throwException(new \phpmailerException('fooo')));
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$mail->send($item);
	}

	/**
	 * @test
	 */
	public function TokenAtBody()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello {$name}',
			'foo@bar.hu',
			'Foo Bar',
			array('name' => 'test')
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailHtmlContains('hello test', $email);
		$this->assertEmailTextContains('hello test', $email);
	}

	/**
	 * @test
	 */
	public function TokenAtSubject()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject {$name}',
			'hello {$name}',
			'foo@bar.hu',
			'Foo Bar',
			array('name' => 'test')
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSubjectContains('subject test', $email);
	}

	/**
	 * @test
	 */
	public function multipleTokens_unused()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject {$name}',
			'hello {$name}',
			'foo@bar.hu',
			'Foo Bar',
			array('name' => 'test', 'f' => 'b')
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSubjectContains('subject test', $email);
		$this->assertEmailHtmlContains('hello test', $email);
	}

	/**
	 * @test
	 */
	public function multipleTokens_unused2()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject {$name}',
			'hello {$name}',
			'foo@bar.hu',
			'Foo Bar',
			array('name2' => 'test', 'f' => 'b')
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailSubjectContains('subject {$name}', $email);
		$this->assertEmailHtmlContains('hello {$name}', $email);
	}

	/**
	 * @test
	 */
	public function removeHtmlText()
	{
		$PHPMailer = $this->getPHPMailer();
		$mail = $this->getMailer($PHPMailer);
		$item = Mail\MailQueueItem\MailQueueItemFactory::make(
			'subject {$name}',
			'<h1>hello {$name}</h1> <p>foo</p>',
			'foo@bar.hu',
			'Foo Bar',
			array('name' => 'test2')
		);

		$mail->send($item);

		$email = $this->getLastMessage();
		$this->assertEmailHtmlContains('<h1>hello test2</h1> <p>foo</p>', $email);
		$this->assertEmailTextContains('hello test2 foo', $email);
	}


	/**
	 * @test
	 */
	public function log_notYetScheduled()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', 'subject');
		$message = new Mail\Message($mailTemplate);
		$message->addRecipient(new Mail\Recipient('test@mail.hu', 'foo bar'));
		$mailQueueItem = new Mail\MailQueueItem($message, new \DateTime('+10 minutes'));
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('info');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function log_recipientsNotSet()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', 'subject');
		$message = new Mail\Message($mailTemplate);
		$mailQueueItem = new Mail\MailQueueItem($message);
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('error');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function log_messageNotSet()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('', 'subject');
		$message = new Mail\Message($mailTemplate);
		$message->addRecipient(new Mail\Recipient('foo@bar.hu', 'name'));
		$mailQueueItem = new Mail\MailQueueItem($message);
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('error');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\InvalidMessageException
	 */
	public function log_subjectNotSet()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', '');
		$message = new Mail\Message($mailTemplate);
		$message->addRecipient(new Mail\Recipient('foo@bar.hu', 'name'));
		$mailQueueItem = new Mail\MailQueueItem($message);
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('error');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 */
	public function log_sendSuccessful()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', 'subject');
		$message = new Mail\Message($mailTemplate);
		$message->addRecipient(new Mail\Recipient('foo@bar.hu', 'name'));
		$mailQueueItem = new Mail\MailQueueItem($message);
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('info');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}

	/**
	 * @test
	 * @expectedException \Systream\Mail\Exception\SendFailedException
	 */
	public function log_failed()
	{
		$mailer = $this->getMockBuilder(Mail\MailSender\MailSenderInterface::class)->getMock();
		$mailer->method('send')->will($this->throwException(new \phpmailerException('fooo')));
		$mail = new Mail($mailer, $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock());
		$mailTemplate = new Mail\MailTemplate\StringMailTemplate('body', 'subject');
		$message = new Mail\Message($mailTemplate);
		$message->addRecipient(new Mail\Recipient('foo@bar.hu', 'name'));
		$mailQueueItem = new Mail\MailQueueItem($message);
		$logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
		$logger->expects($this->atLeastOnce())->method('critical');
		$mail->setLogger($logger);

		$mail->send($mailQueueItem);
	}


	/**
	 * @test
	 */

	public function mailQueTest()
	{
		$PHPMailer = $this->getPHPMailer();
		$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
		$mail = new Mail($mailer, new Mail\QueueHandler\SqliteQueHandlerAdapter());

		$queCount = 10;

		/** @var Mail\MailQueueItem[] $items */
		$items = [];
		while ($queCount--) {
			$item = Mail\MailQueueItem\MailQueueItemFactory::make(
				'subject ' . $queCount,
				'hello' . $queCount,
				'foo' . $queCount . '@bar.hu',
				'Foo Bar ' . $queCount
			);

			$items[] = $item;
			$mail->queue($item);
		}

		$this->assertEmpty($this->getMessages());

		$mail->consume();

		$messages = $this->getMessages();
		$this->assertCount(10, $messages);

		foreach ($messages as $key => $message) {
			$this->assertEmailSenderEquals('<test@unit.test>', $message);
			$this->assertEmailRecipientsContain('<' . $items[$key]->getMessage()->getRecipients()[0]->getEmail() . '>', $message);
			$this->assertEmailSubjectEquals($items[$key]->getMessage()->getMailTemplate()->getSubject(), $message);
			$this->assertEmailHtmlContains($items[$key]->getMessage()->getMailTemplate()->getTemplate(), $message);
		}

		$this->cleanMessages();
		$mail->consume();

		$this->assertEmpty($this->getMessages());
	}


	/**
	 * @return \PHPMailer
	 */
	private function getPHPMailer()
	{
		$mailer = new \PHPMailer(true);
		$mailer->isSMTP();
		$mailer->Host       = "127.0.0.1";
		$mailer->Port       = 1025;
		$mailer->SetFrom('test@unit.test');
		return $mailer;
	}

	/**
	 * @param $PHPMailer
	 * @return Mail
	 */
	protected function getMailer($PHPMailer)
	{
		$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
		$queMock = $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock();
		$mail = new Mail($mailer, $queMock);
		return $mail;
	}


}
