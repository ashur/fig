<?php

/*
 * This file is part of Fig
 */
namespace Fig;

use Cranberry\Filesystem;
use PHPUnit\Framework\TestCase;

class EngineTest extends TestCase
{
	public function test_executeCommand_returnsArray()
	{
		$engine = new Engine();
		$result = $engine->executeCommand( 'echo', ['hello'] );

		$this->assertTrue( is_array( $result ) );
		$this->assertArrayHasKey( 'output', $result );
		$this->assertContains( 'hello', $result['output'] );

		$this->assertArrayHasKey( 'exitCode', $result );
		$this->assertEquals( 0, $result['exitCode'] );
	}
}
