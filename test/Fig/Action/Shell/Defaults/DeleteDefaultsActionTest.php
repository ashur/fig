<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Action;
use Fig\Action\AbstractAction;
use Fig\Shell;
use FigTest\Action\Shell\Defaults\DefaultsActionTestCase as TestCase;

class DeleteDefaultsActionTest extends TestCase
{
	/* Helpers */

	public function createObject() : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$domain = getUniqueString( 'com.example.Newton' );

		$action = new DeleteDefaultsAction( $name, $domain );
		return $action;
	}

	public function createObject_fromDomain( string $domain ) : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );

		$action = new DeleteDefaultsAction( $name, $domain );
		return $action;
	}

	public function createObject_fromKey( string $key ) : AbstractAction
	{
		$name = getUniqueString( 'my defaults action ' );
		$domain = getUniqueString( 'com.example.Newton' );

		$action = new DeleteDefaultsAction( $name, $domain, $key );
		return $action;
	}

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$domain = getUniqueString( 'com.example.Newton' );

		$action = new DeleteDefaultsAction( $name, $domain );
		return $action;
	}


	/* Providers */

	/* Consumed by tests in parent TestCase definitions */
	public function provider_ActionObject() : array
	{
		return [
			[$this->createObject_fromDomain( 'com.example.Newton' )],
			[$this->createObject_fromKey( 'SerialNumber' )],
		];
	}


	/* Tests */

	public function test_deploy_commandSuccess_outputsOK()
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

		$action = $this->createObject_fromKey( 'SerialNumber' );
		$result = $action->deploy( $shellMock );

		$this->assertFalse( $result->didError() );
		$this->assertEquals( Action\Result::STRING_STATUS_SUCCESS, $result->getOutput() );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function test_getKey_throwsException_whenKeyUndefined()
	{
		$action = $this->createObject_fromDomain( 'com.example.Newton' );
		$action->getKey();
	}

	public function test_getSubtitle()
	{
		$action = $this->createObject_fromName( 'action' );
		$this->assertEquals( 'delete', $action->getSubtitle() );
	}
}
