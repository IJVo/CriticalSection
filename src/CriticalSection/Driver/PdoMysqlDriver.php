<?php

declare(strict_types=1);

namespace stekycz\CriticalSection\Driver;

use PDO;
use PDOStatement;

/**
 * @see https://mariadb.com/kb/en/mariadb/get_lock
 * @see https://dev.mysql.com/doc/refman/5.7/en/miscellaneous-functions.html#function_get-lock
 */
class PdoMysqlDriver implements IDriver
{

	public const NO_WAIT = 0;

	/** @var PDO */
	private $pdo;

	/** @var int */
	private $lockTimeout;


	public function __construct(PDO $pdo, int $lockTimeout = self::NO_WAIT)
	{
		$this->pdo = $pdo;
		$this->lockTimeout = $lockTimeout;
	}


	public function acquireLock(string $label): bool
	{
		$lockName = self::transformLabelToKey($label);

		return $this->runQuery('SELECT GET_LOCK(?, ?)', $lockName, $this->lockTimeout);
	}


	public function releaseLock(string $label): bool
	{
		$lockName = self::transformLabelToKey($label);

		return $this->runQuery('SELECT RELEASE_LOCK(?)', $lockName);
	}


	private static function transformLabelToKey(string $label): string
	{
		return sha1($label);
	}


	private function runQuery(string $query, string $lockName, int $lockTimeout = null): bool
	{
		/** @var PDOStatement|bool $statement */
		$statement = $this->pdo->prepare($query);
		if (is_bool($statement) && $statement === false) {
			return false;
		}

		$executeParameters = [$lockName];
		if ($lockTimeout !== null) {
			$executeParameters[] = $lockTimeout;
		}
		$executionResult = $statement->execute($executeParameters);

		return $executionResult ? (bool) $statement->fetch(PDO::FETCH_COLUMN) : false;
	}
}
