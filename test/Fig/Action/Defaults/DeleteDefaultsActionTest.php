<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Defaults;

use Fig\Action\BaseAction;
use Fig\Engine;
use PHPUnit\Framework\TestCase;

class DeleteDefaultsActionTest extends TestCase
{
	public function getInstance_withKey() : array
	{
		$varTimeValue = microtime( true );

		/* Name */
		$namePattern = 'action %s';
		$nameOriginalValue = sprintf( $namePattern, '{{  time  }}' );
		$nameExpectedValue = sprintf( $namePattern, $varTimeValue );

		$values['name'] = $nameExpectedValue;

		/* Domain */
		$domainPattern = 'com.example.Foo.%s';
		$domainOriginalValue = sprintf( $domainPattern, '{{ time }}' );
		$domainExpectedValue = sprintf( $domainPattern, $varTimeValue );

		$values['domain'] = $domainExpectedValue;

		/* Key */
		$keyPattern = 'DefaultsKey%s';
		$keyOriginalValue = sprintf( $keyPattern, '{{time}}' );
		$keyExpectedValue = sprintf( $keyPattern, $varTimeValue );

		$values['key'] = $keyExpectedValue;

		$action = new DeleteDefaultsAction( $nameOriginalValue, $domainOriginalValue, $keyOriginalValue );
		$action->setVariables( ['time' => $varTimeValue] );

		$data = [ 'action' => $action, 'values' => $values ];

		return $data;
	}

	public function getInstance_withoutKey() : array
	{
		$varTimeValue = microtime( true );

		/* Name */
		$namePattern = 'action %s';
		$nameOriginalValue = sprintf( $namePattern, '{{  time  }}' );
		$nameExpectedValue = sprintf( $namePattern, $varTimeValue );

		$values['name'] = $nameExpectedValue;

		/* Domain */
		$domainPattern = 'com.example.Foo.%s';
		$domainOriginalValue = sprintf( $domainPattern, '{{ time }}' );
		$domainExpectedValue = sprintf( $domainPattern, $varTimeValue );

		$values['domain'] = $domainExpectedValue;

		/* Key */
		$values['key'] = null;

		$action = new DeleteDefaultsAction( $nameOriginalValue, $domainOriginalValue, null );
		$action->setVariables( ['time' => $varTimeValue] );

		$data = [ 'action' => $action, 'values' => $values ];

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
	public function test_deploy_callsEngine_executeCommand( DeleteDefaultsAction $action, array $expectedValues )
	{
		/* Build expected command arguments */
		$expectedCommandArguments = array_values( $expectedValues );

		if( $expectedValues['key'] == null )
		{
			array_pop( $expectedCommandArguments );
		}

		array_shift( $expectedCommandArguments );				// Action name
		array_unshift( $expectedCommandArguments, 'delete' );	// Subcommand name

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
	public function test_deploy_commandError_causesError( DeleteDefaultsAction $action )
	{
		$defaultsOutputArray = ["2017-11-28 08:39:15.657 defaults[58832:3901130]", "Domain (com.example.Foo) not found.", "Defaults have not been changed."];

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
	public function test_deploy_commandSuccess_outputsOK( DeleteDefaultsAction $action, array $expectedValues )
	{
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
				'output' => [],
				'exitCode' => 0
			]);

		$action->deploy( $engineMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( BaseAction::STRING_STATUS_SUCCESS, $action->getOutput() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_invalidCommand_causesError( DeleteDefaultsAction $action )
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
	public function test_getName( DeleteDefaultsAction $action, array $expectedValues )
	{
		$this->assertEquals( $expectedValues['name'], $action->getName() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_getSubtitle( DeleteDefaultsAction $action )
	{
		$this->assertEquals( 'delete', $action->getSubtitle() );
	}
}
