<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell;

use Fig\Action;
use Fig\Shell;
use FigTest\Action\Shell\TestCase;

class CommandActionTest extends TestCase
{
	/* Providers */

	/*
	 * Consumed by:
	 * - FigTest\Action\TestCase::test_getType
	 * - FigTest\Action\Shell\TestCase::test_deploy_invalidCommand_causesError
	 */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$commandName = getUniqueString( 'command' );

		$action = new CommandAction( $actionName, $commandName, [] );

		return [
			[$action]
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
		$actionName = getUniqueString( 'action ' );
		$commandName = getUniqueString( 'command-' );

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

		$commandAction = new CommandAction( $actionName, $commandName, [] );
		$result = $commandAction->deploy( $shellMock );

		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	public function test_deploy_commandWithOutput_outputsString()
	{
		$actionName = getUniqueString( 'action ' );
		$commandName = getUniqueString( 'command-' );

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

		$commandAction = new CommandAction( $actionName, $commandName, [] );
		$result = $commandAction->deploy( $shellMock );

		$expectedOutput = implode( PHP_EOL, $output );

		$this->assertEquals( $expectedOutput, $result->getOutput() );
	}

	public function test_getCommand_withVariableReplacement()
	{
		$actionName = getUniqueString( 'action ' );

		$time = time();
		$variables = ['time' => $time];

		$pattern = 'name-%s';
		$commandString = sprintf( $pattern, '{{ time }}' );
		$expectedCommand = sprintf( $pattern, $time );

		$commandAction = new CommandAction( $actionName, $commandString );
		$commandAction->setVariables( $variables );

		$this->assertEquals( $expectedCommand, $commandAction->getCommand() );
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

	/**
	 * @dataProvider	provider_getSubtitle_withVariableReplacement
	 */
	public function test_getSubtitle_withVariableReplacement( string $command, array $variables, string $expectedSubtitle )
	{
		$commandAction = new CommandAction( 'My Command', $command );
		$commandAction->setVariables( $variables );

		$this->assertEquals( $expectedSubtitle, $commandAction->getSubtitle() );
	}
}
