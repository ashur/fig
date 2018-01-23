<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Action;
use Fig\Action\AbstractAction;
use Fig\Shell;
use FigTest\Action\Shell\ShellActionTestCase as TestCase;

class CommandActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$name = getUniqueString( 'my command action ' );
		$command = getUniqueString( 'command' );

		$action = new CommandAction( $name, $command, [] );
		return $action;
	}

	public function createObject_fromArguments( array $arguments ) : AbstractAction
	{
		$name = getUniqueString( 'my command action ' );
		$command = getUniqueString( 'command' );

		$action = new CommandAction( $name, $command, $arguments );
		return $action;
	}

	public function createObject_fromCommand( string $command ) : AbstractAction
	{
		$name = getUniqueString( 'my command action ' );

		$action = new CommandAction( $name, $command, [] );
		return $action;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$command = getUniqueString( 'command' );

		$action = new CommandAction( $name, $command, [] );
		return $action;
	}


	/* Providers */

	/*
	 * Consumed by:
	 * - FigTest\Action\TestCase::test_getType
	 * - FigTest\Action\Shell\TestCase::test_deploy_invalidCommand_causesError
	 */
	public function provider_ActionObject() : array
	{
		return [
			[$this->createObject_fromName( 'my command action' )]
		];
	}

	public function provider_getSubtitle_withVariableReplacement() : array
	{

		return [
			['echo', [], 'echo'],
			['{{ command }}', ['command'=>'echo'], 'echo'],
		];
	}


	/* Tests */

	public function test_deploy_commandWithoutOutput_outputsOK()
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();

		$shellMock
			->method( 'commandExists' )
			->willReturn( true );

		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( [], 0 ) );

		$action = $this->createObject();
		$result = $action->deploy( $shellMock );

		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	public function test_deploy_commandWithOutput_outputsString()
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists','executeCommand'] )
			->getMock();

		$shellMock
			->method( 'commandExists' )
			->willReturn( true );

		$output[] = getUniqueString( 'line' );
		$output[] = getUniqueString( 'line' );
		$output[] = getUniqueString( 'line' );

		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( $output, 0 ) );

		$action = $this->createObject();
		$result = $action->deploy( $shellMock );

		$expectedOutput = implode( PHP_EOL, $output );

		$this->assertEquals( $expectedOutput, $result->getOutput() );
	}

	public function test_getCommand_withVariableReplacement()
	{
		$time = time();
		$variables = ['time' => $time];

		$pattern = 'name-%s';
		$commandString = sprintf( $pattern, '{{ time }}' );
		$expectedCommand = sprintf( $pattern, $time );

		$action = $this->createObject_fromCommand( $commandString );
		$action->setVariables( $variables );

		$this->assertEquals( $expectedCommand, $action->getCommand() );
	}

	public function test_getCommandArguments_withVariableReplacement()
	{
		$actionName = getUniqueString( 'action ' );

		$time = time();
		$variables = ['time' => $time];

		$pattern = '%s-%s';

		$commandArguments[] = sprintf( $pattern, 'arg1', '{{ time }}' );
		$commandArguments[] = sprintf( $pattern, 'arg2', '{{ time }}' );

		$expectedArguments[] = sprintf( $pattern, 'arg1', $time );
		$expectedArguments[] = sprintf( $pattern, 'arg2', $time );

		$action = $this->createObject_fromArguments( $commandArguments );
		$action->setVariables( $variables );

		$this->assertEquals( $expectedArguments, $action->getCommandArguments() );
	}

	public function test_getSubtitle()
	{
		$command = getUniqueString( 'command-' );
		$action = new CommandAction( 'My Command', $command );

		$this->assertEquals( $command, $action->getSubtitle() );
	}

	/**
	 * @dataProvider	provider_getSubtitle_withVariableReplacement
	 */
	public function test_getSubtitle_withVariableReplacement( string $command, array $variables, string $expectedSubtitle )
	{
		$action = new CommandAction( 'My Command', $command );
		$action->setVariables( $variables );

		$this->assertEquals( $expectedSubtitle, $action->getSubtitle() );
	}
}
