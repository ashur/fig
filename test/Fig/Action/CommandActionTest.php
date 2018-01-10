<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use FigTest\Action\TestCase;

class CommandActionTest extends TestCase
{
	public function getFigDirectoryMock() : Filesystem\Directory
	{
		return $this
			->getMockBuilder( Filesystem\Directory::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function provider_deploy_callsEngineExecuteCommand() : array
	{
		return [
			[ 'this is command output', 0, false ],
			[ 'this is error output', 1, true ],
			[ 'this is different error output', 126, true ],
		];
	}

	/**
	 * @dataProvider	provider_deploy_callsEngineExecuteCommand
	 */
	public function test_deploy_callsEngineExecuteCommand( string $outputString, int $exitCode, bool $shouldError )
	{
		$actionName = 'action' . microtime( true );
		$commandName = 'command-' . time();
		$commandArgs = [ 'arg1', 'arg2-' . microtime( true ) ];

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => [$outputString],
				'exitCode' => $exitCode
			]);

		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( $commandName ),
				$this->equalTo( $commandArgs )
			);

		$commandAction = new CommandAction( $actionName, $commandName, $commandArgs );
		$commandAction->deploy( $engineMock );

		$this->assertEquals( $shouldError, $commandAction->didError() );
		$this->assertEquals( $outputString, $commandAction->getOutput() );
	}

	public function test_deploy_commandWithoutOutput_outputsOK()
	{
		$actionName = 'action' . microtime( true );
		$commandName = 'command-' . time();

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => [],
				'exitCode' => 0
			]);

		$commandAction = new CommandAction( $actionName, $commandName, [] );
		$commandAction->deploy( $engineMock );

		$this->assertEquals( CommandAction::STRING_STATUS_SUCCESS, $commandAction->getOutput() );
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

	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$action = new CommandAction( $actionName, 'command' );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$command = getUniqueString( 'command-' );
		$action = new CommandAction( 'My Command', $command );

		$this->assertEquals( $command, $action->getSubtitle() );
	}

	public function provider_getSubtitle_withVariableReplacement() : array
	{

		return [
			['echo', [], 'echo'],
			['{{ command }}', ['command'=>'echo'], 'echo'],
		];
	}

	/**
	 * @dataProvider	provider_getSubtitle_withVariableReplacement
	 */
	public function test_getSubtitle_withVariableReplacement( string $command, array $variables, string $expectedSubtitle )
	{
		$commandAction = new CommandAction( 'My Command', $command );
		$commandAction->setVariables( $variables );

		$this->assertEquals( $expectedSubtitle, $commandAction->getSubtitle() );
	}

	public function test_getType()
	{
		$action = new CommandAction( 'name', 'command', [] );
		$this->assertEquals( 'Command', $action->getType() );
	}

	public function test_invalidCommand_causesError()
	{
		$actionName = 'action' . microtime( true );
		$commandName = 'command-' . time();

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists'] )
			->getMock();

		$engineMock
			->expects( $this->once() )
			->method( 'commandExists' )
			->with( $this->equalTo( $commandName ) );

		$commandAction = new CommandAction( $actionName, $commandName );
		$commandAction->deploy( $engineMock );

		$this->assertTrue( $commandAction->didError() );

		$expectedErrorMessage = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, $commandName );
		$this->assertEquals( $expectedErrorMessage, $commandAction->getOutput() );
	}
}
