<?php

declare(strict_types=1);

namespace stekycz\CriticalSection\Driver;

use stekycz\CriticalSection\Exception\CriticalSectionException;

class FileDriver implements IDriver
{

	/** @var resource[] */
	private $handles = [];

	/** @var string */
	private $lockFilesDir;


	public function __construct(string $lockFilesDir)
	{
		$lockFilesDir = rtrim($lockFilesDir, DIRECTORY_SEPARATOR);
		self::createDir($lockFilesDir);
		$this->lockFilesDir = $lockFilesDir;
	}

	public function acquireLock(string $label): bool
	{
		$handle = fopen($this->getFilePath($label), 'w+b');
		if ($handle === false) {
			return false;
		}

		$locked = flock($handle, LOCK_EX | LOCK_NB);
		if ($locked === false) {
			fclose($handle);

			return false;
		}

		$this->handles[$label] = $handle;

		return true;
	}

	public function releaseLock(string $label): bool
	{
		if (!isset($this->handles[$label])) {
			return false;
		}

		$unlocked = flock($this->handles[$label], LOCK_UN);
		if ($unlocked === false) {
			return false;
		}

		fclose($this->handles[$label]);
		unset($this->handles[$label]);

		return true;
	}

	private function getFilePath(string $label): string
	{
		return $this->lockFilesDir . DIRECTORY_SEPARATOR . sha1($label);
	}

	private static function createDir(string $dir)
	{
		if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) { // @ - dir may already exist
			throw new CriticalSectionException("Unable to create directory '$dir'. " . error_get_last()['message']);
		}
	}

}
