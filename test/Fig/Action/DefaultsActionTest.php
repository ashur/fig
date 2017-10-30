<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use PHPUnit\Framework\TestCase;

class DefaultsActionTest extends TestCase
{
	public function test_getDomain_supportsVariables()
	{
		$time = microtime( true );

		$pattern = 'com.example.%s';
		$domainString = sprintf( $pattern, '{{ app }}' );
		$expectedDomain = sprintf( $pattern, $time );

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, $domainString );
		$defaultsAction->setVariables( ['app' => $time] );

		$this->assertEquals( $expectedDomain, $defaultsAction->getDomain() );
	}

	public function test_getKey_supportsVariables()
	{
		$time = microtime( true );

		$pattern = 'SerialNumber-';
		$keyString = sprintf( $pattern, '{{ time }}' );
		$expectedKey = sprintf( $pattern, $time );

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton', $keyString );
		$defaultsAction->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedKey, $defaultsAction->getKey() );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function test_getKey_throwsException_whenKeyUndefined()
	{
		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton' );
		$defaultsAction->getKey();
	}

	public function test_getValue_supportsVariables()
	{
		$time = microtime( true );

		$pattern = 'Foo-Bar-';
		$valueString = sprintf( $pattern, '{{ time }}' );
		$expectedValue = sprintf( $pattern, $time );

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton', 'SerialNumber', $valueString );
		$defaultsAction->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedValue, $defaultsAction->getValue() );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function test_getValue_throwsException_whenValueUndefined()
	{
		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton' );
		$defaultsAction->getValue();
	}

	public function provider_hasKey_returnsBool() : array
	{
		return [
			[null, false],
			['SerialNumber', true]
		];
	}
	/**
	 * @dataProvider	provider_hasKey_returnsBool
	 */
	public function test_hasKey_returnsBool( $key, $shouldHaveKey )
	{
		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton', $key );
		$this->assertEquals( $shouldHaveKey, $defaultsAction->hasKey() );
	}

	public function provider_hasValue_returnsBool() : array
	{
		return [
			[null, false],
			['Foo-Bar', true]
		];
	}
	/**
	 * @dataProvider	provider_hasValue_returnsBool
	 */
	public function test_hasValue_returnsBool( $value, $shouldHaveValue )
	{
		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::WRITE, 'com.example.Newton', 'SerialNumber', $value );
		$this->assertEquals( $shouldHaveValue, $defaultsAction->hasValue() );
	}

	/**
	 * `defaults` command may not exist on host system (i.e., non-macOS UNIX)
	 *
	 * @expectedException	Fig\Action\CommandNotFoundException
	 */
	public function test_invalidCommand_throwsExceptionDuringDeployment()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( false );

		$engineMock
			->expects( $this->once() )
			->method( 'commandExists' )
			->with( $this->equalTo( 'defaults' ) );

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, 'com.example.Newton' );
		$defaultsAction->deploy( $engineMock );
	}

	public function test_invalidDomainCausesError()
	{
		$domain = 'com.example.' . microtime( true );
		$outputArray = ['2017-01-02 03:04:56.789 defaults[12345:1234567',"Domain {$domain} does not exist"];

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => $outputArray,
				'exitCode' => 1
			]);
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( ['read', $domain] )
			);

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, $domain );
		$defaultsAction->deploy( $engineMock );

		$this->assertTrue( $defaultsAction->didError() );
		$this->assertEquals( implode( PHP_EOL, $outputArray ), $defaultsAction->getOutput() );
	}

	public function provider_invalidKeyCausesError() : array
	{
		return [
			[DefaultsAction::READ, 'read'],
			[DefaultsAction::DELETE, 'delete'],
		];
	}

	/**
	 * @dataProvider	provider_invalidKeyCausesError
	 */
	public function test_invalidKeyCausesError( int $method, string $methodString )
	{
		$domain = 'com.example.' . microtime( true );
		$key = 'Foo-Bar-' . microtime( true );
		$outputArray = ['2017-01-02 03:04:56.789 defaults[12345:1234567',"The domain/default pair of ({$domain}, {$key}) does not exist"];

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => $outputArray,
				'exitCode' => 1
			]);
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( [$methodString, $domain, $key] )
			);

		$defaultsAction = new DefaultsAction( 'my defaults action', $method, $domain, $key );
		$defaultsAction->deploy( $engineMock );

		$this->assertTrue( $defaultsAction->didError() );
		$this->assertEquals( implode( PHP_EOL, $outputArray ), $defaultsAction->getOutput() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_invalidMethod_throwsExceptionDuringDeployment()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$defaultsAction = new DefaultsAction( 'my defaults action', -1, 'com.example.Newton' );
		$defaultsAction->deploy( $engineMock );
	}

	public function test_output_commandWithOutput_outputsOutput()
	{
		$domain = 'com.example.Newton';
		$key = 'SerialNumber';
		$value = (string) microtime( true );

		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists', 'executeCommand'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$engineMock
			->method( 'executeCommand' )
			->willReturn([
				'output' => [$value],
				'exitCode' => 0
			]);
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( ['read', $domain, $key] )
			);

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::READ, $domain, $key );

		$defaultsAction->deploy( $engineMock );

		$this->assertFalse( $defaultsAction->didError() );
		$this->assertEquals( $value, $defaultsAction->getOutput() );
	}

	public function test_output_commandWithoutOutput_outputsOK()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
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
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( ['delete', 'com.example.Newton'] )
			);

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::DELETE, 'com.example.Newton' );
		$defaultsAction->deploy( $engineMock );

		$this->assertEquals( BaseAction::STRING_STATUS_SUCCESS, $defaultsAction->getOutput() );
	}

	public function test_output_writeOutputsValue()
	{
		$domain = 'com.example.Newton';
		$key = 'SerialNumber';
		$value = '{{ serial }}';

		$varSerial = (string) microtime( true );

		$engineMock = $this
			->getMockBuilder( Engine::class )
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
		$engineMock
			->expects( $this->once() )
			->method( 'executeCommand' )
			->with(
				$this->equalTo( 'defaults' ),
				$this->equalTo( ['write', $domain, $key, $varSerial] )
			);

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::WRITE, $domain, $key, $value );
		$defaultsAction->setVariables( ['serial' => $varSerial] );

		$defaultsAction->deploy( $engineMock );

		$this->assertFalse( $defaultsAction->didError() );
		$this->assertEquals( $varSerial, $defaultsAction->getOutput() );
	}

	/**
	 * @expectedException	Fig\Action\InvalidActionArgumentsException
	 */
	public function test_writeMethod_throwsExceptionWhenMissingValue()
	{
		$engineMock = $this
			->getMockBuilder( Engine::class )
			->setMethods( ['commandExists'] )
			->getMock();

		$engineMock
			->method( 'commandExists' )
			->willReturn( true );

		$defaultsAction = new DefaultsAction( 'my defaults action', DefaultsAction::WRITE, 'com.example.Newton' );
		$defaultsAction->deploy( $engineMock );
	}
}
