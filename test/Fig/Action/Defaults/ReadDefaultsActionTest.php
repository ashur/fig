<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Defaults;

use Fig\Engine;
use PHPUnit\Framework\TestCase;

class ReadDefaultsActionTest extends TestCase
{
	/**
	 * @todo	Add variables
	 */
	public function getInstance_withKey() : array
	{
		$values['name']   = 'action ' . microtime( true );
		$values['domain'] = 'com.example.Foo.' . microtime( true );
		$values['key']    = 'DefaultsKey' . microtime( true );

		$data = [
			'action' => new ReadDefaultsAction( $values['name'], $values['domain'], $values['key'] ),
			'values' => $values
		];

		return $data;
	}

	/**
	 * @todo	Add variables
	 */
	public function getInstance_withoutKey() : array
	{
		$values['name']   = 'action ' . microtime( true );
		$values['domain'] = 'com.example.Foo.' . microtime( true );
		$values['key']    = null;

		$data = [
			'action' => new ReadDefaultsAction( $values['name'], $values['domain'], $values['key'] ),
			'values' => $values
		];

		return $data;
	}

	public function provider_actionWithValues() : array
	{
		$withKey = $this->getInstance_withKey();
        $withoutKey = $this->getInstance_withoutKey();

		return [
			[ $withKey['action'], $withKey['values'] ],
			[ $withoutKey['action'], $withoutKey['values'] ]
		];
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_callsEngine_executeCommand( ReadDefaultsAction $action, array $expectedValues )
	{
		/* Build expected command arguments */
		$expectedCommandArguments = array_values( $expectedValues );

		if( $expectedValues['key'] == null )
		{
			array_pop( $expectedCommandArguments );
		}

		array_shift( $expectedCommandArguments );				// Action name
		array_unshift( $expectedCommandArguments, 'read' );		// Subcommand name

		/* Build Engine mock */
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
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( $expectedCommandArguments )
			);

		$action->deploy( $engineMock );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_commandError_causesError( ReadDefaultsAction $action )
	{
		$defaultsOutputArray = ["Command line interface to a user's defaults.", "Syntax:"];

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();
		$engineMock
			->method( 'commandExists' )
			->willReturn( true );
		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => $defaultsOutputArray,
				'exitCode' => 1
			]);

		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );
		$this->assertEquals( implode( PHP_EOL, $defaultsOutputArray ), $action->getOutput() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_commandSuccess_outputsCommandOutput(  ReadDefaultsAction $action, array $expectedValues )
	{
		/* Build defaults command output */
		if( $expectedValues['key'] == null )
		{
			$defaultsOutputArray = ["{", "    DefaultsKey = \"Defaults Value\";", "}"];
		}
		else
		{
			$defaultsOutputArray = ["Defaults Value"];
		}

		/* Build Engine mock */
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();
		$engineMock
			->method( 'commandExists' )
			->willReturn( true );
		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => $defaultsOutputArray,
				'exitCode' => 0
			]);

		$action->deploy( $engineMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( implode( PHP_EOL, $defaultsOutputArray ), $action->getOutput() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_invalidCommand_causesError( ReadDefaultsAction $action )
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists'] )
			->getMock();
		$engineMock
			->method( 'commandExists' )
			->willReturn( false );
		$engineMock
			->expects( $this->once() )
			->method( 'commandExists' )
			->with( $this->equalTo( 'defaults' ) );

		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );

		$expectedErrorMessage = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, 'defaults' );
		$this->assertEquals( $expectedErrorMessage, $action->getOutput() );
	}

	/**
	 * Make sure BaseDefaultsAction subclasses don't omit setting `name`
	 *
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_getName( ReadDefaultsAction $action, array $expectedValues )
	{
		$this->assertEquals( $expectedValues['name'], $action->getName() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_getSubtitle( ReadDefaultsAction $action )
	{
		$this->assertEquals( 'read', $action->getSubtitle() );
	}
}
