<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use PHPUnit\Framework\TestCase;

class CommandActionTest extends TestCase
{
	public function test_deployExecutesCommandViaEngine()
	{
		$actionName = 'action' . microtime( true );
		$commandName = 'command-' . time();
		$commandArgs = [ 'arg1', 'arg2-' . microtime( true ) ];

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( $commandName ),
				$this->equalTo( $commandArgs )
			);

		$commandAction = new CommandAction( $actionName, $commandName, $commandArgs );
		$commandAction->deploy( $engineMock );
	}

	/**
	 * @expectedException	Fig\Action\CommandNotFoundException
	 */
	public function test_invalidCommand_throwsExceptionDuringDeployment()
	{
		$actionName = 'action' . microtime( true );
		$commandName = 'command-' . time();

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists'] )
			->getMock();

		$engineMock
			->expects( $this->once() )
			->method( 'commandExists' )
			->with( $this->equalTo( $commandName ) );

		$commandAction = new CommandAction( $actionName, $commandName );
		$commandAction->deploy( $engineMock );
	}
}
