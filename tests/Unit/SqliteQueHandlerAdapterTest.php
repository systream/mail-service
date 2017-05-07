<?php

namespace Tests\Systream\Unit;

use Systream\Mail\MailQueueItem\MailQueueItemFactory;
use Systream\Mail\QueueHandler\SqliteQueHandlerAdapter;

class SqliteQueHandlerAdapterTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		parent::setUp();
		$pdo = new \PDO('sqlite:' . __DIR__ . '/../../src/Var/mailq.db');
		$pdo->exec('drop table if EXISTS mail_que');
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

		$handler = new SqliteQueHandlerAdapter();
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

		$handler = new SqliteQueHandlerAdapter();
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

		$handler = new SqliteQueHandlerAdapter();
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

		$handler = new SqliteQueHandlerAdapter();
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

		$handler = new SqliteQueHandlerAdapter();
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
		$handler = new SqliteQueHandlerAdapter();
		foreach ($items as $item) {
			$handler->push($item);
		}

		$this->assertEquals($items, $handler->getPendingMails());
	}


}
