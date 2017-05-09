<?php

namespace Tests\Systream\Unit;

use Systream\Mail\MailQueueItem\MailQueueItemFactory;
use Systream\Mail\QueueHandler\PDOQueHandlerAdapter;
use Systream\Mail\QueueHandler\SqliteQueHandlerAdapter;

class PDOQueHandlerAdapterTest extends \PHPUnit_Framework_TestCase
{

	protected $pdo;

	public function setUp()
	{
		parent::setUp();
		$this->pdo = new \PDO('sqlite:' . __DIR__ . '/../../src/Var/mailq.db');
		$this->pdo->exec('drop table if EXISTS mail_que');
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$this->pdo->exec('
              CREATE TABLE IF NOT EXISTS `mail_que` (
               id VARCHAR(36) PRIMARY KEY,
              `priority` int DEFAULT 1,
              `data` TEXT not null,
              `timestamp` INTEGER 
            )
        ');
	}


	/**
	 * @test
	 */
	public function add()
	{
		$item = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		$handler->push($item);
		$this->assertEquals($item, $handler->pop());
	}

	/**
	 * @test
	 */
	public function pop_double()
	{
		$item = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		$handler->push($item);
		$this->assertEquals($item, $handler->pop());
		$this->assertEquals($item, $handler->pop());
	}

	/**
	 * @test
	 */
	public function pop_ack()
	{
		$item = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		$handler->push($item);
		$this->assertEquals($item, $handler->pop());
		$handler->ack($item);
		$this->assertNull($handler->pop());
	}

	/**
	 * @test
	 */
	public function ack_notPushed()
	{
		$item = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		$handler->ack($item);
		$this->assertNull($handler->pop());
	}

	/**
	 * @test
	 */
	public function push_twice_getOnce_override()
	{
		$item = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		$handler->push($item);
		$handler->push($item);
		$this->assertEquals($item, $handler->pop());
		$handler->ack($item);
		$this->assertNull($handler->pop());
	}


	/**
	 * @test
	 */
	public function getPendingMails()
	{
		$items = [];
		$items[] = MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);

		$items[] = MailQueueItemFactory::make(
			'subject2',
			'hello3',
			'foo3@bar.hu',
			'Foo3 Bar'
		);
		$handler = new PDOQueHandlerAdapter($this->pdo, 'mail_que');
		foreach ($items as $item) {
			$handler->push($item);
		}

		$this->assertEquals($items, $handler->getPendingMails());
	}


}
