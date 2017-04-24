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
		$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
		$mail = new Mail($mailer);
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
	public function Token_test()
	{
		$PHPMailer = $this->getPHPMailer();
		$mailer = new Mail\MailSender\PHPMailerAdapter($PHPMailer);
		$mail = new Mail($mailer);
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


}
