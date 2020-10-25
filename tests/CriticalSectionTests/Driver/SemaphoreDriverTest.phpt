<?php

declare(strict_types=1);

namespace stekycz\CriticalSection\tests\Driver;

use stekycz\CriticalSection\Driver\SemaphoreDriver;
use TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * TEST: Driver:SemaphoreDriverTest
 *
 * @testCase
 * @phpExtension sysvsem
 */
class SemaphoreDriverTest extends TestCase
{

	public const TEST_LABEL = 'test';

	/** @var SemaphoreDriver */
	private $driver;


	protected function setUp()
	{
		parent::setUp();
		$this->driver = new SemaphoreDriver();
	}


	public function testCanAcquireOnce()
	{
		$label = __FUNCTION__;
		Assert::true($this->driver->acquireLock($label));
	}


	public function testCanReleaseOnce()
	{
		$label = __FUNCTION__;
		Assert::true($this->driver->acquireLock($label));
		Assert::true($this->driver->releaseLock($label));
	}
}

(new SemaphoreDriverTest)->run();
