<?php

/*
 * This file is part of Fig
 */
namespace Fig\Shell;

use FigTest\TestCase;

class ShellTest extends TestCase
{
	public function provider_commandExists() : array
	{
		return [
			[ 'echo', true ],
			[ getUniqueString( 'command' ), false ],
		];
	}

	/**
	 * @dataProvider	provider_commandExists
	 */
	public function test_commandExists( string $command, bool $shouldExist )
	{
		$shell = new Shell();

		$doesExist = $shell->commandExists( $command );

		$this->assertEquals( $shouldExist, $doesExist );
	}

	public function test_executeCommand()
	{
		$shell = new Shell();

		$argument = getUniqueString( 'hello ' );
		$result = $shell->executeCommand( 'echo', [$argument] );

		$this->assertContains( $argument, $result->getOutput() );
		$this->assertEquals( 0, $result->getExitCode() );
	}

	public function test_executeCommand_exitCodes()
	{
		$shell = new Shell();

		$exitCodes = [0,1,126,127,128];
		foreach( $exitCodes as $exitCode )
		{
			$result = $shell->executeCommand( 'exit', [$exitCode] );
			$this->assertEquals( $exitCode, $result->getExitCode() );
		}
	}
}
