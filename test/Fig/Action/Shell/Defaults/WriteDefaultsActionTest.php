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
		$result = $action->deploy( $shellMock );

		$this->assertFalse( $result->didError() );
		$this->assertEquals( $value, $result->getOutput() );
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
