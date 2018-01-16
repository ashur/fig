<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Shell;
use FigTest\Action\Shell\TestCase;

class ReadDefaultsActionTest extends TestCase
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

		$action = new ReadDefaultsAction( $nameOriginalValue, $domainOriginalValue, $keyOriginalValue );
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

		$action = new ReadDefaultsAction( $nameOriginalValue, $domainOriginalValue, null );
		$action->setVariables( ['time' => $varTimeValue] );

		$data = [ 'action' => $action, 'values' => $values ];

		return $data;
	}


	/* Tests */

	/**
	 * @dataProvider	provider_actionWithValues
	 */
	public function test_deploy_commandError_causesError( ReadDefaultsAction $action )
	{
		$defaultsOutputArray = ["Command line interface to a user's defaults.", "Syntax:"];

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
			->willReturn( new Shell\Result( $defaultsOutputArray, 0 ) );

		$action->deploy( $shellMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( implode( PHP_EOL, $defaultsOutputArray ), $action->getOutput() );
	}

	public function test_getName()
	{
		$action = $this->getInstance_withKey();
		$this->assertEquals( $action['values']['name'], $action['action']->getName() );
	}

	public function test_getSubtitle()
	{
		$action = $this->getInstance_withoutKey();
		$this->assertEquals( 'read', $action['action']->getSubtitle() );
	}
}
