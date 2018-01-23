<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Action\AbstractAction;
use FigTest\TestCase;

class AbstractDeployableActionTest extends TestCase
{
	/* Helpers */

	public function createObject_fromName( string $name ) : AbstractAction
	{
		$actionMock = $this->getMockForAbstractClass( AbstractDeployableAction::class );
		return $actionMock;
	}


	/* Providers */

	public function provider_booleanishValues() : array
	{
		return [
			[true, true],
			['true', true],
			['True', true],
			['yes', true],
			['Yes', true],

			[false, false],
			['false', false],
			['False', false],
			['no', false],
			['No', false],
		];
	}


	/* Tests */

	/**
	 * @dataProvider	provider_booleanishValues
	 */
	public function test_ignoreErrors_supportsBooleanishValues( $booleanish, bool $shouldIgnoreErrors )
	{
		$action = $this->createObject_fromName( 'my abstract action ' );
		$action->ignoreErrors( $booleanish );

		$this->assertEquals( $shouldIgnoreErrors, $action->willIgnoreErrors() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreErrors_throwsException_givenNonBooleanishValue()
	{
		$action = $this->createObject_fromName( 'my abstract action ' );
		$action->ignoreErrors( 'hello' );
	}

	/**
	 * @dataProvider	provider_booleanishValues
	 */
	public function test_ignoreOutput_supportsBooleanishValues( $booleanish, $shouldIgnoreOutput )
	{
		$action = $this->createObject_fromName( 'my abstract action ' );
		$action->ignoreOutput( $booleanish );

		$this->assertEquals( $shouldIgnoreOutput, $action->willIgnoreOutput() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreOutput_throwsException_givenNonBooleanishValue()
	{
		$action = $this->createObject_fromName( 'my abstract action ' );
		$action->ignoreOutput( 'hello' );
	}
}
