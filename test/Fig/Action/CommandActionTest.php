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

	public function test_getCommand_supportsVariables()
	{
		$actionName = 'action' . microtime( true );

		$time = microtime( true );
		$variables = ['time' => $time];

		$pattern = 'name-%s';
		$commandString = sprintf( $pattern, '{{ time }}' );
		$expectedCommand = sprintf( $pattern, $time );

		$commandAction = new CommandAction( $actionName, $commandString );
		$commandAction->setVariables( $variables );

		$this->assertEquals( $expectedCommand, $commandAction->getCommand() );
	}

	public function test_getCommandArguments_supportsVariables()
	{
		$actionName = 'action' . microtime( true );

		$time = microtime( true );
		$variables = ['time' => $time];

		$pattern = '%s-%s';

		$commandArguments[] = sprintf( $pattern, 'arg1', '{{ time }}' );
		$commandArguments[] = sprintf( $pattern, 'arg2', '{{ time }}' );

		$expectedArguments[] = sprintf( $pattern, 'arg1', $time );
		$expectedArguments[] = sprintf( $pattern, 'arg2', $time );

		$commandAction = new CommandAction( $actionName, 'command', $commandArguments );
		$commandAction->setVariables( $variables );

		$this->assertEquals( $expectedArguments, $commandAction->getCommandArguments() );
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
