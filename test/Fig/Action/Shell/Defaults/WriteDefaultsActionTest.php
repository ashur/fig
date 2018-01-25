<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action\AbstractAction;
use Fig\Shell;
use FigTest\Action\Shell\Defaults\DefaultsActionTestCase as TestCase;

class WriteDefaultsActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$domain = getUniqueString( 'com.example.Newton' );
		$key = getUniqueString( 'SerialNumber-' );
		$value = getUniqueString( 'value-' );

		$action = new WriteDefaultsAction( $name, $domain, $key, $value );
		return $action;
	}

	public function createObject_fromDomain( string $domain ) : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$key = getUniqueString( 'SerialNumber-' );
		$value = getUniqueString( 'value-' );

		$action = new WriteDefaultsAction( $name, $domain, $key, $value );
		return $action;
	}

	public function createObject_fromKey( string $key ) : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$domain = getUniqueString( 'com.example.Newton' );
		$value = getUniqueString( 'value-' );

		$action = new WriteDefaultsAction( $name, $domain, $key, $value );
		return $action;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$domain = getUniqueString( 'com.example.Newton' );
		$key = getUniqueString( 'SerialNumber-' );
		$value = getUniqueString( 'value-' );

		$action = new WriteDefaultsAction( $name, $domain, $key, $value );
		return $action;
	}

	public function createObject_fromValue( string $value ) : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$domain = getUniqueString( 'com.example.Newton' );
		$key = getUniqueString( 'SerialNumber-' );

		$action = new WriteDefaultsAction( $name, $domain, $key, $value );
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
			[$this->createObject_fromDomain( 'com.example.Newton' )],
			[$this->createObject_fromKey( 'SerialNumber-' )],
			[$this->createObject_fromValue( 'value-' )],
		];
	}


	/* Tests */

	public function test_deploy_commandSuccess_outputsValue()
	{
		$value = getUniqueString( 'SERIAL-' );

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

		$action = $this->createObject_fromValue( $value );
		$result = $action->deploy( $shellMock );

		$this->assertFalse( $result->didError() );
		$this->assertEquals( $value, $result->getOutput() );
	}

	public function test_hasKey_returnsBool()
	{
		$action = $this->createObject_fromDomain( 'com.example.Newton' );
		$this->assertTrue( $action->hasKey() );
	}

	public function test_getSubtitle()
	{
		$action = $this->createObject_fromName( 'action' );
		$this->assertEquals( 'write', $action->getSubtitle() );
	}

	public function test_getValue_withVariableReplacement()
	{
		$time = time();

		$pattern = 'Foo-Bar-';
		$valueString = sprintf( $pattern, '{{ time }}' );
		$expectedValue = sprintf( $pattern, $time );

		$action = $this->createObject_fromValue( $valueString );
		$action->setVars( ['time' => $time] );

		$this->assertEquals( $expectedValue, $action->getValue() );
	}
}
