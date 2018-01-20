<?php

/*
 * This file is part of FigTest
 */
namespace FigTest\Action;

use Fig\Action\AbstractAction;

abstract class TestCase extends \FigTest\TestCase
{
	/* Helpers */
	abstract public function createObject_fromName( string $name ) : AbstractAction;

	/* Providers */
	abstract public function provider_ActionObject() : array;

	/* Tests */
	abstract public function test_getSubtitle();

	public function test_getName()
	{
		$name = getUniqueString( 'my action ' );
		$action = $this->createObject_fromName( $name );

		$this->assertEquals( $name, $action->getName() );
	}

	public function test_getName_withVariableReplacement()
	{
		$time = time();

		$pattern = 'my action %s';
		$nameString = sprintf( $pattern, '{{ time }}' );
		$expectedName = sprintf( $pattern, $time );

		$action = $this->createObject_fromName( $nameString );
		$action->setVariables( ['time' => $time] );

		$this->assertEquals( $expectedName, $action->getName() );
	}

	/**
	 * @dataProvider	provider_ActionObject
	 */
	public function test_getType( AbstractAction $action )
	{
		$actionType = $action->getType();

		$this->assertTrue( is_string( $actionType ) );
		$this->assertTrue( strlen( $actionType ) > 0 );
	}
}
