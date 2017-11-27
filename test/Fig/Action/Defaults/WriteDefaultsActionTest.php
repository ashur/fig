<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Defaults;

use Fig\Engine;
use PHPUnit\Framework\TestCase;

class WriteDefaultsActionTest extends TestCase
{
	public function test_deploy_callsEngine_executeCommand()
	{
		$appName = 'Newton-' . microtime( true );

		$domainPattern = 'com.example.%s';
		$domainString = sprintf( $domainPattern, '{{ app }}' );
		$domainExpected = sprintf( $domainPattern, $appName );

		$keyPattern = '%sSerialNumber';
		$keyString = sprintf( $keyPattern, '{{ app }}' );
		$keyExpected = sprintf( $keyPattern, $appName );

		$valuePattern = '%s-SERIAL';
		$valueString = sprintf( $valuePattern, '{{ app }}' );
		$valueExpected = sprintf( $valuePattern, $appName );

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
				$this->equalTo( ['write', $domainExpected, $keyExpected, $valueExpected] )
			);

		$action = new WriteDefaultsAction( 'my defaults action', $domainString, $keyString, $valueString );
		$action->setVariables( ['app' => $appName] );

		$action->deploy( $engineMock );
	}

	public function test_deploy_commandError_causesError()
	{
		$domain = 'com.example.' . microtime( true );
		$key = 'SerialNumber';
		$value = 'SERIAL-' . microtime( true );

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

		$action = new WriteDefaultsAction( 'my defaults action', $domain, $key, $value );
		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );
		$this->assertEquals( implode( PHP_EOL, $defaultsOutputArray ), $action->getOutput() );
	}

	public function test_deploy_commandSuccess_outputsValue()
	{
		$domain = 'com.example.' . microtime( true );
		$key = 'SerialNumber';
		$value = 'SERIAL-' . microtime( true );

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

		$action = new WriteDefaultsAction( 'my defaults action', $domain, $key, $value );
		$action->deploy( $engineMock );

		$this->assertFalse( $action->didError() );
		$this->assertEquals( $value, $action->getOutput() );
	}

	public function test_deploy_invalidCommand_causesError()
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

		$action = new WriteDefaultsAction( 'my defaults action', 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );
		$action->deploy( $engineMock );

		$this->assertTrue( $action->didError() );

		$expectedErrorMessage = sprintf( Engine::STRING_ERROR_COMMANDNOTFOUND, 'defaults' );
		$this->assertEquals( $expectedErrorMessage, $action->getOutput() );
	}

	/**
	 * Make sure BaseDefaultsAction subclasses don't omit setting `name`
	 */
	public function test_getName()
	{
		$actionName = 'action ' . microtime( true );
		$action = new WriteDefaultsAction( $actionName, 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );
		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getSubtitle()
	{
		$action = new WriteDefaultsAction( 'my defaults write action', 'com.example.Newton', 'SerialNumber', 'SERIAL-NUMBER' );
		$this->assertEquals( 'write', $action->getSubtitle() );
	}
}
