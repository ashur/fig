<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Shell;
use FigTest\Action\Shell\TestCase;

class WriteDefaultsActionTest extends TestCase
{
	/* Providers */

	/*
	 * Consumed by:
	 * - FigTest\Action\TestCase::test_getType
	 * - FigTest\Action\Shell\TestCase::test_deploy_invalidCommand_causesError
	 */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action-' );
		$action = new WriteDefaultsAction( $actionName, 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );

		return [
			[$action]
		];
	}


	/* Tests */

	public function test_deploy_commandError_causesError()
	{
		$domain = getUniqueString( 'com.example.' );
		$key = 'SerialNumber';
		$value = getUniqueString( 'SERIAL-' );

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

		$action = new WriteDefaultsAction( 'my defaults action', $domain, $key, $value );
		$action->deploy( $shellMock );

		$this->assertTrue( $action->didError() );
		$this->assertEquals( implode( PHP_EOL, $defaultsOutputArray ), $action->getOutput() );
	}

	public function test_deploy_commandSuccess_outputsValue()
	{
		$domain = getUniqueString( 'com.example.' );
		$key = 'SerialNumber';
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

		$action = new WriteDefaultsAction( 'my defaults action', $domain, $key, $value );
		$action->deploy( $shellMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( $value, $action->getOutput() );
	}

	/**
	 * Make sure BaseDefaultsAction subclasses don't omit setting `name`
	 */
	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$action = new WriteDefaultsAction( $actionName, 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );
		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$action = new WriteDefaultsAction( 'my defaults write action', 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );
		$this->assertEquals( 'write', $action->getSubtitle() );
	}
}
