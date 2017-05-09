<?php

namespace Systream\Mail\QueueHandler;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;

class PDOQueHandlerAdapter implements QueueHandlerInterface
{
	/**
	 * @var \PDO
	 */
	protected $pdo;
	/**
	 * @var
	 */
	private $tableName;

	/**
	 * SqliteQueHandlerAdapter constructor.
	 * @param \PDO $pdo
	 * @param string $tableName
	 */
	public function __construct(\PDO $pdo, $tableName)
	{
		$this->pdo = $pdo;
		$this->tableName = $tableName;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return void
	 */
	public function push(MailQueueItemInterface $mailQueueItem)
	{
		$statement = $this->pdo->prepare($this->getPushSqlQuery($this->getById($mailQueueItem->getId())));
		$statement->bindValue('dio', $mailQueueItem->getId(), \PDO::PARAM_STR);
		$statement->bindValue('prio', $mailQueueItem->getPriority(), \PDO::PARAM_INT);
		$statement->bindValue('data', serialize($mailQueueItem), \PDO::PARAM_STR);
		$statement->bindValue('ts', time(), \PDO::PARAM_INT);
		$statement->execute();
		$statement->closeCursor();
	}

	/**
	 * @return MailQueueItemInterface|null
	 */
	public function pop()
	{
		$statement = $this->pdo->prepare('select data from mail_que order by priority DESC, timestamp ASC limit 1');
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();

		if ($result) {
			return unserialize($result['data']);
		}

		return null;
	}

	/**
	 * @param MailQueueItemInterface $mailQueueItem
	 * @return Void
	 */
	public function ack(MailQueueItemInterface $mailQueueItem)
	{
		$statement = $this->pdo->prepare('delete from mail_que where id = :dio ');
		$statement->bindValue('dio', $mailQueueItem->getId(), \PDO::PARAM_STR);
		$statement->execute();
		$statement->closeCursor();
	}

	/**
	 * @param string $id
	 * @return array
	 */
	private function getById($id)
	{
		$statement = $this->pdo->prepare('select * from ' . $this->tableName . ' where id = :dio limit 1');
		$statement->bindValue('dio', $id, \PDO::PARAM_STR);
		$statement->execute();
		$result = $statement->fetch();
		$statement->closeCursor();
		return $result;
	}

	/**
	 * @param $result
	 * @return string
	 */
	private function getPushSqlQuery($result)
	{
		if (!$result) {
			return 'insert into ' . $this->tableName . ' (id, priority, data, timestamp) values (:dio, :prio, :data, :ts)';
		}
		return 'update ' . $this->tableName . ' set priority = :prio, data = :data, timestamp = :ts where id = :dio';
	}

	/**
	 * @return MailQueueItemInterface[]
	 */
	public function getPendingMails()
	{
		$statement = $this->pdo->prepare('select data from ' . $this->tableName . ' order by "timestamp" ASC ');
		$statement->execute();
		$results = [];
		while ($result = $statement->fetch()) {
			$results[] = unserialize($result['data']);
		}
		$statement->closeCursor();
		return $results;
	}

}