<?php

namespace Tests\Systream\Unit;

use Systream\Mail\MailQueueItem;

class MailQueueItemTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @test
	 */
	public function toArray()
	{
		$item = MailQueueItem\MailQueueItemFactory::make(
			'subject',
			'hello',
			'foo@bar.hu',
			'Foo Bar'
		);
		$item->setPriority(2);

		$this->assertEquals([
			'id' => $item->getId(),
			'recipients' => [
				['email' => 'foo@bar.hu', 'name' => 'Foo Bar']
			],
			'priority' => 2,
			'subject' => 'subject',
			'template' => 'hello',
			'scheduled' => null
		], $item->toArray());
	}

}
