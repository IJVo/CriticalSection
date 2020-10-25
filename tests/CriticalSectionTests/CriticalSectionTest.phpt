<?php

declare(strict_types=1);

/**
 * @testCase
 */

namespace stekycz\CriticalSection\tests;

use Mockery;
use stekycz\CriticalSection\CriticalSection;
use stekycz\CriticalSection\Driver\IDriver;
use TestCase;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

class CriticalSectionTest extends TestCase
{

	public const TEST_LABEL = 'test';

	/** @var CriticalSection */
	private $criticalSection;

	/** @var IDriver|Mockery\MockInterface */
	private $driver;


	protected function setUp()
	{
		parent::setUp();
		$this->driver = Mockery::mock(IDriver::class);
		$this->criticalSection = new CriticalSection($this->driver);
	}


	public function testCanBeEnteredAndLeft()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(true);
		$this->driver->shouldReceive('releaseLock')->once()->andReturn(true);

		Assert::false($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::true($this->criticalSection->enter(self::TEST_LABEL));
		Assert::true($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::true($this->criticalSection->leave(self::TEST_LABEL));
		Assert::false($this->criticalSection->isEntered(self::TEST_LABEL));
	}


	public function testCannotBeEnteredTwice()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(true);
		$this->driver->shouldReceive('releaseLock')->once()->andReturn(true);

		Assert::true($this->criticalSection->enter(self::TEST_LABEL));
		Assert::true($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($this->criticalSection->enter(self::TEST_LABEL));
		Assert::true($this->criticalSection->leave(self::TEST_LABEL));
	}


	public function testCannotBeLeftWithoutEnter()
	{
		$this->driver->shouldReceive('acquireLock')->never();
		$this->driver->shouldReceive('releaseLock')->never();

		Assert::false($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($this->criticalSection->leave(self::TEST_LABEL));
	}


	public function testCannotBeLeftTwice()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(true);
		$this->driver->shouldReceive('releaseLock')->once()->andReturn(true);

		Assert::true($this->criticalSection->enter(self::TEST_LABEL));
		Assert::true($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::true($this->criticalSection->leave(self::TEST_LABEL));
		Assert::false($this->criticalSection->leave(self::TEST_LABEL));
	}


	public function testIsNotEnteredOnNotAcquiredLock()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(false);
		$this->driver->shouldReceive('releaseLock')->never();

		Assert::false($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($this->criticalSection->enter(self::TEST_LABEL));
		Assert::false($this->criticalSection->isEntered(self::TEST_LABEL));
	}


	public function testIsNotLeftOnNotReleasedLock()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(true);
		$this->driver->shouldReceive('releaseLock')->once()->andReturn(false);

		Assert::true($this->criticalSection->enter(self::TEST_LABEL));
		Assert::true($this->criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($this->criticalSection->leave(self::TEST_LABEL));
		Assert::true($this->criticalSection->isEntered(self::TEST_LABEL));
	}


	public function testMultipleCriticalSectionHandlers()
	{
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(true);
		$this->driver->shouldReceive('acquireLock')->once()->andReturn(false);
		$this->driver->shouldReceive('releaseLock')->once()->andReturn(true);

		$criticalSection = $this->criticalSection;
		$criticalSection2 = new CriticalSection($this->driver);

		Assert::false($criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($criticalSection2->isEntered(self::TEST_LABEL));
		Assert::true($criticalSection->enter(self::TEST_LABEL));
		Assert::false($criticalSection2->enter(self::TEST_LABEL));
		Assert::true($criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($criticalSection2->isEntered(self::TEST_LABEL));
		Assert::true($criticalSection->leave(self::TEST_LABEL));
		Assert::false($criticalSection2->leave(self::TEST_LABEL));
		Assert::false($criticalSection->isEntered(self::TEST_LABEL));
		Assert::false($criticalSection2->isEntered(self::TEST_LABEL));
	}
}

(new CriticalSectionTest)->run();
