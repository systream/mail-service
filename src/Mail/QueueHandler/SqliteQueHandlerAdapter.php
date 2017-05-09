<?php

namespace Systream\Mail\QueueHandler;

class SqliteQueHandlerAdapter extends PDOQueHandlerAdapter implements QueueHandlerInterface
{
	/**
	 * SqliteQueHandlerAdapter constructor.
	 * @param string $dbFileLocation
	 */
	public function __construct($dbFileLocation = __DIR__ . '/../../Var/mailq.db')
	{
		$pdo = new \PDO('sqlite:' . $dbFileLocation);
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
		$pdo->exec('
              CREATE TABLE IF NOT EXISTS `mail_que` (
               id VARCHAR(36) PRIMARY KEY,
              `priority` int DEFAULT 1,
              `data` TEXT not null,
              `timestamp` INTEGER 
            )
        ');

		parent::__construct($pdo, 'mail_que');

	}

}