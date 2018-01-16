<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action\Action;
use Fig\Shell;
use FigTest\Action\Shell\TestCase;

class DeleteDefaultsActionTest extends TestCase
{
	/* Providers */

	/*
	 * Consumed by:
	 * - FigTest\Action\TestCase::test_getType
	 * - FigTest\Action\Shell\TestCase::test_deploy_invalidCommand_causesError
	 */
	public function provider_ActionObject() : array
	{
		$action = $this->getInstance_withKey();

		return [
			[$action['action']]
		];
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


	/* Helpers */

	public function getInstance_withKey() : array
	{
		$varTimeValue = time();

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
		$varTimeValue = time();

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


	/* Tests */

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_commandError_causesError( DeleteDefaultsAction $action )
	{
		$defaultsOutputArray = ["2017-11-28 08:39:15.657 defaults[58832:3901130]", "Domain (com.example.Foo) not found.", "Defaults have not been changed."];

		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( true );

		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( $defaultsOutputArray, 1 ) );

		$action->deploy( $shellMock );

		$expectedOutput = implode( PHP_EOL, $defaultsOutputArray );

		$this->assertTrue( $action->didError() );
		$this->assertEquals( $expectedOutput, $action->getOutput() );
	}

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_commandSuccess_outputsOK( DeleteDefaultsAction $action, array $expectedValues )
	{
		$shellMock = $this
			->getMockBuilder( Shell\Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();
		$shellMock
			->method( 'commandExists' )
			->willReturn( true );

		$shellMock
			->method( 'executeCommand' )
			->willReturn( new Shell\Result( [], 0 ) );

		$action->deploy( $shellMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( Action::STRING_STATUS_SUCCESS, $action->getOutput() );
	}

	public function test_getName()
	{
		$action = $this->getInstance_withKey();
		$this->assertEquals( $action['values']['name'], $action['action']->getName() );
	}

	public function test_getSubtitle()
	{
		$action = $this->getInstance_withoutKey();
		$this->assertEquals( 'delete', $action['action']->getSubtitle() );
	}
}
