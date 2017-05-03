<?php

namespace Systream\Mail\QueueHandler;


use Systream\Mail\MailQueueItem\MailQueueItemInterface;

class SqliteQueHandlerAdapter implements QueueHandlerInterface
{
	/**
	 * @var \PDO
	 */
	protected $pdo;

	public function __construct()
	{
		$this->pdo = new \PDO('sqlite:' . __DIR__ . '/../../Var/mailq.db');
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
		$statement = $this->pdo->prepare('select * from mail_que where id = :dio limit 1');
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
			return 'insert into mail_que (id, priority, data, timestamp) values (:dio, :prio, :data, :ts)';
		}
		return 'update mail_que set priority = :prio, data = :data, timestamp = :ts where id = :dio';
	}
}