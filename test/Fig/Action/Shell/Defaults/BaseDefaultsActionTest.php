<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action\Shell\Defaults;

use Fig\Engine;
use Fig\Shell\Shell;
use FigTest\Action\TestCase;

class BaseDefaultsActionTest extends TestCase
{
	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleDefaultsAction( $actionName, 'com.example.Newton', 'SerialNumber' );

		return [
			[$action]
		];
	}

	public function test_getDomain_withVariableReplacement()
	{
		$time = time();

		$pattern = 'com.example.%s';
		$domainString = sprintf( $pattern, '{{ app }}' );
		$expectedDomain = sprintf( $pattern, $time );

		$action = new ExampleDefaultsAction( 'my defaults action', $domainString );
		$action->setVariables( ['app' => $time] );

		$this->assertEquals( $expectedDomain, $action->getDomain() );
	}

	public function test_getName()
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleDefaultsAction( $actionName, 'com.example.Newton', 'SerialNumber' );

		$this->assertEquals( $actionName, $action->getName() );
	}

	public function test_getKey_withVariableReplacement()
	{
		$time = time();

		$pattern = 'SerialNumber-';
		$keyString = sprintf( $pattern, '{{ time }}' );
		$expectedKey = sprintf( $pattern, $time );

		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton', $keyString );
		$action->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedKey, $action->getKey() );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function test_getKey_throwsException_whenKeyUndefined()
	{
		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton' );
		$action->getKey();
	}

	public function test_getSubtitle()
	{
		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton', 'SerialNumber' );

		$this->assertEquals( 'example', $action->getSubtitle() );
	}

	public function test_getValue_withVariableReplacement()
	{
		$time = time();

		$pattern = 'Foo-Bar-';
		$valueString = sprintf( $pattern, '{{ time }}' );
		$expectedValue = sprintf( $pattern, $time );

		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton', 'SerialNumber', $valueString );
		$action->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedValue, $action->getValue() );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function test_getValue_throwsException_whenValueUndefined()
	{
		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton' );
		$action->getValue();
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
		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton', $key );
		$this->assertEquals( $shouldHaveKey, $action->hasKey() );
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
		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton', 'SerialNumber', $value );
		$this->assertEquals( $shouldHaveValue, $action->hasValue() );
	}

	/**
	 * @expectedException	Fig\Exception\RuntimeException
	 * @expectedExceptionCode	Fig\Exception\RuntimeException::COMMAND_NOT_FOUND
	 */
	public function test_preDeploy_invalidCommand_throwsException()
	{
		$shellMock = $this
			->getMockBuilder( Shell::class )
			->disableOriginalConstructor()
			->setMethods( ['commandExists'] )
			->getMock();

		$shellMock
			->method( 'commandExists' )
			->willReturn( false );

		$action = new ExampleDefaultsAction( 'my defaults action', 'com.example.Newton' );
		$action->preDeploy( $shellMock );
	}
}

class ExampleDefaultsAction extends BaseDefaultsAction
{
	/**
	 * @var	string
	 */
	protected $methodName = 'example';

	/**
	 * @param	string	$name
	 *
	 * @param	string	$domain
	 *
	 * @return	void
	 */
	public function __construct( string $name, string $domain, string $key=null, string $value=null )
	{
		$this->name = $name;
		$this->domain = $domain;
		$this->key = $key;
		$this->value = $value;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Shell $shell ){}
}
