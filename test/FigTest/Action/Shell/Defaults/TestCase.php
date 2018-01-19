<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action\Shell\Defaults;

use Fig\Action\AbstractAction;

abstract class TestCase extends \FigTest\Action\Shell\TestCase
{
	/* Helpers */
	abstract public function createObject_fromDomain( string $domain ) : AbstractAction;
	abstract public function createObject_fromKey( string $key ) : AbstractAction;

	/* Tests */
	public function test_getDomain_withVariableReplacement()
	{
		$time = time();

		$pattern = 'com.example.%s';
		$domainString = sprintf( $pattern, '{{ app }}' );
		$expectedDomain = sprintf( $pattern, $time );

		$action = $this->createObject_fromDomain( $domainString );
		$action->setVariables( ['app' => $time] );

		$this->assertEquals( $expectedDomain, $action->getDomain() );
	}

	public function test_getKey_withVariableReplacement()
	{
		$time = time();

		$pattern = 'SerialNumber-';
		$keyString = sprintf( $pattern, '{{ time }}' );
		$expectedKey = sprintf( $pattern, $time );

		$action = $this->createObject_fromKey( $keyString );
		$action->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedKey, $action->getKey() );
	}

	public function test_hasKey_returnsBool()
	{
		$actionWithoutKey = $this->createObject_fromDomain( 'com.example.Newton' );
		$actionWithKey = $this->createObject_fromKey( 'SerialNumber' );

		$this->assertFalse( $actionWithoutKey->hasKey() );
		$this->assertTrue( $actionWithKey->hasKey() );
	}
}
