<?php

/*
 * This file is part of Fig
 */
namespace Fig\Action;

use Fig\Engine;
use FigTest\Action\TestCase;

class BaseActionTest extends TestCase
{
	public function getStub() : AbstractAction
	{
		$stub = $this->getMockForAbstractClass( AbstractAction::class );
		return $stub;
	}

	/* Consumed by FigTest\Action\TestCase::test_getType */
	public function provider_ActionObject() : array
	{
		$actionName = getUniqueString( 'action ' );
		$action = new ExampleAction( $actionName );

		return [
			[$action]
		];
	}

	public function test_getName()
	{
		$expectedName = getUniqueString( 'action-' );
		$exampleAction = new ExampleAction( $expectedName );

		$actualName = $exampleAction->getName();
		$this->assertTrue( is_string( $actualName ) );
		$this->assertEquals( $expectedName, $actualName );
	}

	public function test_getName_withVariableReplacement()
	{
		$variableDate = getUniqueString( 'DATE-' );

		$actionNamePattern = getUniqueString( 'my action %s ' );
		$actionNameOriginal = sprintf( $actionNamePattern, '{{ date }}' );
		$actionNameExpected = sprintf( $actionNamePattern, $variableDate );

		$variables = [ 'date' => $variableDate ];

		$action = new ExampleAction( $actionNameOriginal );
		$action->setVariables( $variables );

		$this->assertEquals( $actionNameExpected, $action->getName() );
	}

	public function test_getProfileName_returnsNull_whenUndefined()
	{
		$action = new ExampleAction( 'name' );

		$this->assertNull( $action->getProfileName() );
	}

	public function test_getProfileName_returnsString()
	{
		$action = new ExampleAction( 'name' );

		$profileName = getUniqueString( 'profile-' );
		$action->setProfileName( $profileName );

		$this->assertEquals( $profileName, $action->getProfileName() );
	}

	public function test_getSubtitle()
	{
		$exampleAction = new ExampleAction( 'name' );

		$this->assertEquals( 'subtitle', $exampleAction->getSubtitle() );
	}

	public function provider_ignoreMethods_supportBooleanishValues() : array
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

	/**
	 * @dataProvider	provider_ignoreMethods_supportBooleanishValues
	 */
	public function test_ignoreErrors_supportsBooleanishValues( $booleanish, bool $shouldIgnoreErrors )
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreErrors( $booleanish );

		$this->assertEquals( $shouldIgnoreErrors, $stubAction->willIgnoreErrors() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreErrors_throwsException_givenNonBooleanishValue()
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreErrors( 'hello' );
	}

	/**
	 * @dataProvider	provider_ignoreMethods_supportBooleanishValues
	 */
	public function test_ignoreOutput_supportsBooleanishValues( $booleanish, $shouldIgnoreOutput )
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreOutput( $booleanish );

		$this->assertEquals( $shouldIgnoreOutput, $stubAction->willIgnoreOutput() );
	}

	/**
	 * @expectedException	DomainException
	 */
	public function test_ignoreOutput_throwsException_givenNonBooleanishValue()
	{
		$stubAction = $this->getStub();
		$stubAction->ignoreOutput( 'hello' );
	}

	public function test_isDeprecated_returnsFalseByDefault()
	{
		$exampleAction = new ExampleAction( 'name' );

		$this->assertFalse( $exampleAction->isDeprecated() );
	}
}

class ExampleAction extends AbstractAction
{
	/**
	 * @var	string
	 */
	protected $type = 'Example';

	/**
	 * @param	string	$name
	 *
	 * @return	void
	 */
	public function __construct( string $name )
	{
		$this->name = $name;
	}

	/**
	 * Executes action, setting output and error status
	 *
	 * @param	Fig\Engine	$engine
	 *
	 * @return	void
	 */
	public function deploy( Engine $engine ){}

	/**
	 * Returns action subtitle
	 *
	 * @return	string
	 */
	public function getSubtitle() : string
	{
		return 'subtitle';
	}

	/**
	 * Sets didError to simulate deployment
	 *
	 * @param	boolean	$didError
	 *
	 * @return	void
	 */
	public function ___setDidError( bool $didError )
	{
		$this->didError = $didError;
	}

	/**
	 * Sets output string to simulate deployment
	 *
	 * @param	string	$outputString
	 *
	 * @return	void
	 */
	public function ___setOutputString( string $outputString )
	{
		$this->outputString = $outputString;
	}
}
