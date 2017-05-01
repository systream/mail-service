<?php

namespace Tests\Systream\Unit;

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
	protected function getMailer($PHPMailer): Mail
	{
		$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
		$queMock = $this->getMockBuilder(Mail\QueueHandler\QueueHandlerInterface::class)->getMock();
		$mail = new Mail($mailer, $queMock);
		return $mail;
	}


}
