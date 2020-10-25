<?php

declare(strict_types=1);

/**
 * @testCase
 */

namespace stekycz\CriticalSection\tests\Driver;

use Mockery;
use PDO;
use PDOStatement;
use stekycz\CriticalSection\Driver\PdoPgsqlDriver;
use TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class PdoPgsqlDriverTest extends TestCase
{

	public const TEST_LABEL = 'test';

	/** @var PdoPgsqlDriver */
	private $driver;

	/** @var PDO */
	private $pdo;


	protected function setUp()
	{
		parent::setUp();
		$this->pdo = Mockery::mock(PDO::class);
		$this->driver = new PdoPgsqlDriver($this->pdo);
	}


	public function testCanAcquireOnce()
	{
		$statement = Mockery::mock(PDOStatement::class);
		$statement->shouldReceive('execute')->once()->andReturn(true);
		$statement->shouldReceive('fetch')->once()->andReturn(true);

		$this->pdo->shouldReceive('prepare')->once()->andReturn($statement);

		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
	}


	public function testCanReleaseOnce()
	{
		$statement = Mockery::mock(PDOStatement::class);
		$statement->shouldReceive('execute')->twice()->andReturn(true);
		$statement->shouldReceive('fetch')->twice()->andReturn(true);

		$this->pdo->shouldReceive('prepare')->twice()->andReturn($statement);

		Assert::true($this->driver->acquireLock(self::TEST_LABEL));
		Assert::true($this->driver->releaseLock(self::TEST_LABEL));
	}
}

(new PdoPgsqlDriverTest)->run();
